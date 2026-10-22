<?php

namespace App\Services\Search;

use App\Models\BlogPost;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Media;
use App\Models\Order;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * Phase 12 — Part 4: Central search engine.
 *
 * Database-backed default engine with a driver seam so Meilisearch /
 * OpenSearch can be swapped in later without changing call sites.
 */
class SearchService
{
    /** @var array<string, array{model: class-string, fields: string[]}> */
    protected array $indexes = [
        'courses' => ['model' => Course::class, 'fields' => ['title', 'slug', 'description']],
        'lessons' => ['model' => CourseLesson::class, 'fields' => ['title']],
        'blogs' => ['model' => BlogPost::class, 'fields' => ['title', 'excerpt', 'content']],
        'pages' => ['model' => Page::class, 'fields' => ['title', 'slug', 'content']],
        'users' => ['model' => User::class, 'fields' => ['full_name', 'email']],
        'certificates' => ['model' => Certificate::class, 'fields' => ['certificate_number']],
        'orders' => ['model' => Order::class, 'fields' => ['order_number']],
        'media' => ['model' => Media::class, 'fields' => ['name', 'alt_text']],
    ];

    /**
     * Search a single index. Returns arrays of ['id', 'type', 'title', 'url'].
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $index, string $query, int $limit = 20): array
    {
        if (! isset($this->indexes[$index]) || trim($query) === '') {
            return [];
        }

        $config = $this->indexes[$index];
        $driver = config('search.driver', 'database');

        if ($driver !== 'database') {
            // Future: delegate to Meilisearch/OpenSearch driver.
            return [];
        }

        /** @var \Illuminate\Database\Eloquent\Builder $q */
        $q = $config['model']::query();
        $table = (new $config['model'])->getTable();

        foreach ($config['fields'] as $i => $field) {
            $method = $i === 0 ? 'where' : 'orWhere';
            $q->{$method}("{$table}.{$field}", 'like', "%{$query}%");
        }

        return $q->limit($limit)->get()
            ->map(fn ($row) => [
                'id' => $row->getKey(),
                'type' => $index,
                'title' => $row->title ?? $row->name ?? $row->order_number ?? $row->certificate_number ?? "#{$row->getKey()}",
                'model' => $config['model'],
            ])
            ->all();
    }

    /**
     * Global search across all indexes with per-index caching.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function globalSearch(string $query, int $limitPerIndex = 10): array
    {
        $results = [];

        foreach (array_keys($this->indexes) as $index) {
            $key = "search:{$index}:".md5($query).":{$limitPerIndex}";

            $results[$index] = Cache::remember($key, now()->addMinutes(5), fn () => $this->search($index, $query, $limitPerIndex));
        }

        return $results;
    }

    /** Invalidate cached results for an index after content changes. */
    public function flushIndex(string $index): void
    {
        Cache::forget("search:{$index}");
    }
}

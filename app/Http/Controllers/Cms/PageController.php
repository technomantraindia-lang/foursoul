<?php

namespace App\Http\Controllers\Cms;

use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): mixed
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', PageStatus::Published->value)
            ->where('visibility', 'public')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->first();

        if ($page) {
            return view('cms.page', [
                'page' => $page,
            ]);
        }

        if (file_exists(public_path('index.html'))) {
            return response()->file(public_path('index.html'));
        }

        abort(404);
    }
}

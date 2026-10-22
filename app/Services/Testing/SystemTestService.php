<?php

declare(strict_types=1);

namespace App\Services\Testing;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class SystemTestService
{
    public function run(): array
    {
        $tests = [
            ['name' => 'Application', 'run' => fn () => app()->bound('config')],
            ['name' => 'Database', 'run' => fn () => DB::connection()->getPdo() !== null],
            ['name' => 'Authentication', 'run' => fn () => config('auth.guards.admin.provider') === 'admins'],
            ['name' => 'Users', 'run' => fn () => User::query()->exists()],
            ['name' => 'Courses', 'run' => fn () => Course::query()->with('sections')->exists()],
            ['name' => 'Enrollments', 'run' => fn () => Enrollment::query()->with(['user', 'course'])->exists()],
            ['name' => 'Admin Panel', 'run' => fn () => Route::has('filament.admin.pages.admin-dashboard')],
            ['name' => 'Storage', 'run' => fn () => Storage::disk(config('filesystems.default'))],
        ];

        return array_map(function (array $test): array {
            $started = microtime(true);
            try {
                ($test['run'])();
                return ['name' => $test['name'], 'status' => 'pass', 'message' => 'Check completed successfully.', 'duration_ms' => (int) round((microtime(true) - $started) * 1000)];
            } catch (\Throwable $e) {
                report($e);
                return ['name' => $test['name'], 'status' => 'fail', 'message' => 'Check failed. See the application log for technical details.', 'duration_ms' => (int) round((microtime(true) - $started) * 1000)];
            }
        }, $tests);
    }
}

<?php

declare(strict_types=1);

namespace App\Services\Testing;

use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

final class TestDatabaseManager
{
    public const CONNECTION = 'test_center';

    public function prepare(): void
    {
        $path = config('database.connections.test_center.database');
        File::ensureDirectoryExists(dirname($path));
        if (! File::exists($path)) {
            File::put($path, '');
        }
        $this->run(function (): void {
            $exitCode = Artisan::call('migrate', ['--database' => self::CONNECTION, '--force' => true]);
            if ($exitCode !== 0) {
                throw new \RuntimeException('Isolated test database migration failed.');
            }
        });
    }

    public function run(Closure $callback): mixed
    {
        $original = DB::getDefaultConnection();
        try {
            DB::setDefaultConnection(self::CONNECTION);
            DB::purge(self::CONNECTION);
            return $callback();
        } finally {
            DB::purge(self::CONNECTION);
            DB::setDefaultConnection($original);
        }
    }
}

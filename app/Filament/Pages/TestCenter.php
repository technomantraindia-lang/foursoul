<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\Testing\SystemTestService;
use App\Services\Testing\TestDataService;
use App\Services\Testing\TestDatabaseManager;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TestCenter extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string | \UnitEnum | null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Test Center';
    protected static ?int $navigationSort = 90;
    protected static ?string $slug = 'test-center';
    protected static ?string $title = 'System Test Center';
    protected string $view = 'filament.pages.test-center';

    public array $results = [];
    public array $counts = [];
    public ?string $lastRun = null;

    public static function canAccess(): bool
    {
        $admin = Auth::guard('admin')->user();
        return (bool) ($admin?->canAccessPanel(app('filament')->getCurrentOrDefaultPanel()) && $admin->hasRole('super_admin'));
    }

    public function mount(): void
    {
        abort_unless(config('features.test_center_enabled'), 404);
        try {
            app(TestDatabaseManager::class)->prepare();
            $this->counts = app(TestDatabaseManager::class)->run(fn () => app(TestDataService::class)->counts());
        } catch (\Throwable $e) {
            report($e);
            abort(503, 'Isolated test database is unavailable. Test aborted.');
        }
    }

    public function runFullTest(): void
    {
        app(TestDatabaseManager::class)->prepare();
        app(TestDatabaseManager::class)->run(function (): void {
            app(TestDataService::class)->ensure();
            $this->results = app(SystemTestService::class)->run();
            $this->counts = app(TestDataService::class)->counts();
        });
        $this->lastRun = now()->toDateTimeString();
    }

    public function createTestData(): void
    {
        app(TestDatabaseManager::class)->prepare();
        $this->counts = app(TestDatabaseManager::class)->run(fn () => app(TestDataService::class)->ensure());
    }

    public function resetTestData(): void
    {
        app(TestDatabaseManager::class)->prepare();
        app(TestDatabaseManager::class)->run(function (): void {
            app(TestDataService::class)->reset();
            $this->counts = app(TestDataService::class)->counts();
        });
        $this->results = [];
        $this->lastRun = null;
    }
}

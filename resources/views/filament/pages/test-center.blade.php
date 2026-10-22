<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section heading="SYSTEM TEST CENTER" description="Create controlled demo data and verify key FourSouls workflows.">
            <p class="mb-4 text-sm font-medium text-warning-600">Database mode: ISOLATED TEST DATABASE. Live application records are not modified.</p>
            <div class="flex flex-wrap gap-3">
                <x-filament::button wire:click="createTestData" color="gray" icon="heroicon-m-database">Create test data</x-filament::button>
                <x-filament::button wire:click="runFullTest" icon="heroicon-m-play">Run full test</x-filament::button>
                <x-filament::button wire:click="resetTestData" wire:confirm="Reset only Test Center generated data?" color="gray" icon="heroicon-m-arrow-path">Reset test data</x-filament::button>
            </div>
        </x-filament::section>
        @if ($results)
            <x-filament::section heading="Results" description="Overall status: {{ collect($results)->contains('status', 'fail') ? 'FAIL' : 'PASS' }}. Last run: {{ $lastRun }}">
                <div class="overflow-x-auto"><table class="w-full text-left text-sm"><thead><tr class="border-b text-gray-500"><th class="px-3 py-2">Test</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Details</th><th class="px-3 py-2">Duration</th></tr></thead><tbody>
                    @foreach ($results as $result)
                        <tr class="border-b"><td class="px-3 py-2">{{ $result['name'] }}</td><td class="px-3 py-2"><x-filament::badge :color="$result['status'] === 'pass' ? 'success' : 'danger'">{{ strtoupper($result['status']) }}</x-filament::badge></td><td class="px-3 py-2">{{ $result['message'] }}</td><td class="px-3 py-2">{{ $result['duration_ms'] }} ms</td></tr>
                    @endforeach
                </tbody></table></div>
            </x-filament::section>
        @endif
        <x-filament::section heading="Test data" description="Only deterministic local demo records are counted.">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-6">@foreach ($counts as $label => $count)<div><div class="text-2xl font-semibold">{{ $count }}</div><div class="text-sm text-gray-500">{{ str($label)->headline() }}</div></div>@endforeach</div>
        </x-filament::section>
    </div>
</x-filament-panels::page>

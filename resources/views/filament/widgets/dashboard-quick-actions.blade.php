<x-filament-widgets::widget>
    <x-filament::section heading="Quick actions" description="Jump into the areas you manage most often.">
        <div class="flex flex-wrap gap-3">
            <x-filament::button tag="a" href="{{ route('filament.admin.resources.subscription-plans.index') }}" icon="heroicon-m-credit-card">Membership plans</x-filament::button>
            <x-filament::button tag="a" href="{{ route('filament.admin.resources.member-content.create') }}" color="success" icon="heroicon-m-video-camera">Add Member Content</x-filament::button>
            <x-filament::button tag="a" href="{{ route('filament.admin.resources.faqs.index') }}" color="gray" icon="heroicon-m-question-mark-circle">Manage FAQs</x-filament::button>
            <x-filament::button tag="a" href="{{ route('filament.admin.pages.analytics') }}" color="gray" icon="heroicon-m-chart-bar">Open analytics</x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

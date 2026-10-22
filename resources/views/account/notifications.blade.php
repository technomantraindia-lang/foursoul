@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('account.notifications.read-all') }}">
                @csrf
                <button type="submit"
                        class="text-sm font-medium text-blue-600 hover:text-blue-800">
                    Mark all as read ({{ $unreadCount }})
                </button>
            </form>
        @endif
    </div>

    @include('components.flash-message')

    @if ($notifications->isEmpty())
        <p class="text-gray-500">You have no notifications yet.</p>
    @else
        <ul class="divide-y divide-gray-200 bg-white rounded-lg shadow">
            @foreach ($notifications as $notification)
                <li class="flex items-start gap-3 p-4 {{ $notification->read_at ? '' : 'bg-blue-50' }}">
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900">{{ $notification->title }}</p>
                        @if ($notification->subject)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $notification->subject }}</p>
                        @endif
                        <div class="prose prose-sm text-gray-700 mt-1">{!! $notification->body !!}</div>
                        <p class="text-xs text-gray-400 mt-2">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </div>

                    @if (! $notification->read_at)
                        <form method="POST"
                              action="{{ route('account.notifications.read', $notification) }}">
                            @csrf
                            <button type="submit"
                                    class="text-xs text-blue-600 hover:text-blue-800 whitespace-nowrap">
                                Mark read
                            </button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
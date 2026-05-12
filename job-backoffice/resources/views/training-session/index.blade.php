<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('training sessions') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />
        <div class="flex justify-end items-center space-x-4 mb-4">
            @if (request()->input('archived') == 'true')
                <a href="{{ route('training-session.index') }}" class="bg-green-500 text-white px-4 py-2 rounded-md">Active Sessions</a>
            @else
                <a href="{{ route('training-session.index', ['archived' => 'true']) }}" class="bg-black text-white px-4 py-2 rounded-md">Archived Sessions</a>
            @endif
            <a href="{{ route('training-session.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md">+ Add Session</a>
        </div>
        <table class="min-w-full divide-y border divide-gray-200 rounded-lg bg-white">
            <thead><tr><th class="px-6 py-3 text-left">Title</th><th class="px-6 py-3 text-left">School</th><th class="px-6 py-3 text-left">Date</th><th class="px-6 py-3 text-left">Status</th><th class="px-6 py-3 text-left">Actions</th></tr></thead>
            <tbody>
                @forelse($trainingSessions as $trainingSession)
                    <tr class="border-b">
                        <td class="px-6 py-4"><a class="text-blue-500 underline" href="{{ route('training-session.show', $trainingSession->id) }}">{{ $trainingSession->title }}</a></td>
                        <td class="px-6 py-4">{{ $trainingSession->school->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $trainingSession->trainingDate }}</td>
                        <td class="px-6 py-4">{{ $trainingSession->status }}</td>
                        <td class="px-6 py-4">
                            @if (request()->input('archived') == 'true')
                                <form action="{{ route('training-session.restore', $trainingSession->id) }}" method="POST">@csrf @method('PUT')<button type="submit" class="text-green-600">Restore</button></form>
                            @else
                                <a href="{{ route('training-session.edit', $trainingSession->id) }}" class="text-blue-500 mr-4">Edit</a>
                                <form action="{{ route('training-session.destroy', $trainingSession->id) }}" method="POST" class="inline-block">@csrf @method('DELETE')<button type="submit" class="text-red-600">Archive</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No training sessions found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $trainingSessions->links() }}
    </div>
</x-app-layout>

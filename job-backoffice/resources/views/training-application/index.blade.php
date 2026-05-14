<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('training-applications') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />
        <div class="flex justify-end items-center space-x-4 mb-4">
            @if (request()->input('archived') == 'true')
                <a href="{{ route('training-application.index') }}" class="bg-green-500 text-white px-4 py-2 rounded-md">Active applications</a>
            @else
                <a href="{{ route('training-application.index', ['archived' => 'true']) }}" class="bg-black text-white px-4 py-2 rounded-md">Archived applications</a>
            @endif
        </div>
        <table class="min-w-full divide-y border divide-gray-200 rounded-lg bg-white">
            <thead><tr><th class="px-6 py-3 text-left">Applicant</th><th class="px-6 py-3 text-left">Session</th><th class="px-6 py-3 text-left">Status</th><th class="px-6 py-3 text-left">Actions</th></tr></thead>
            <tbody>
                @forelse($trainingApplications as $trainingApplication)
                    <tr class="border-b">
                        <td class="px-6 py-4"><a class="text-blue-500 underline" href="{{ route('training-application.show', $trainingApplication->id) }}">{{ $trainingApplication->user->name ?? 'N/A' }}</a></td>
                        <td class="px-6 py-4">{{ $trainingApplication->trainingSession->title ?? 'N/A' }}</td>
                        <td class="px-6 py-4">{{ $trainingApplication->status }}</td>
                        <td class="px-6 py-4">
                            @if (request()->input('archived') == 'true')
                                <form action="{{ route('training-application.restore', $trainingApplication->id) }}" method="POST">@csrf @method('PUT')<button type="submit" class="text-green-600">Restore</button></form>
                            @else
                                <a href="{{ route('training-application.edit', $trainingApplication->id) }}" class="text-blue-500 mr-4">Edit</a>
                                <form action="{{ route('training-application.destroy', $trainingApplication->id) }}" method="POST" class="inline-block">@csrf @method('DELETE')<button type="submit" class="text-red-600">Archive</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No training applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $trainingApplications->links() }}
    </div>
</x-app-layout>

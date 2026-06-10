<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('training-applications') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}
        </h2>
    </x-slot>

    <div class="overflow-x-auto p-6">
        <x-toast-notification />

        <div class="flex justify-end items-center space-x-4 mb-4">
            @if (request()->input('archived') == 'true')
                <!-- Active -->
                <a href="{{ route('training-application.index') }}"
                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Active applications
                </a>
            @else
                <!-- Archived -->
                <a href="{{ route('training-application.index', ['archived' => 'true']) }}"
                    class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black">
                    Archived applications
                </a>
            @endif
        </div>

        <!-- Table -->
        <table class="min-w-full divide-y border divide-gray-200 rounded-lg shadow-mt-4 bg-white">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applicant Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Session (Formation)</th>
                    @if (auth()->user()->role == 'admin')
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">School</th>
                    @endif
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">AI Score</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($trainingApplications as $trainingApplication)
                    <tr class="border-b">
                        <td class="px-6 py-4 text-gray-800">
                            @if(request()->input('archived') == 'true')
                                <span class="text-gray-500">{{ $trainingApplication->user->name ?? 'N/A' }}</span>
                            @else
                                <a class="text-blue-500 hover:text-blue-700 underline"
                                    href="{{ route('training-application.show', $trainingApplication->id) }}">{{ $trainingApplication->user->name ?? 'N/A' }}</a>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-gray-800">
                            {{ $trainingApplication->trainingSession?->title ?? __('Session supprimée') }}
                            @if ($trainingApplication->trainingSession?->trashed())
                                <span class="text-xs text-gray-500">({{ __('archivée') }})</span>
                            @endif
                            @if ($trainingApplication->is_waitlist)
                                <span class="text-xs text-amber-600">⏳ {{ __('liste d\'attente') }}</span>
                            @endif
                        </td>

                        @if (auth()->user()->role == 'admin')
                            <td class="px-6 py-4 text-gray-800">{{ $trainingApplication->trainingSession?->school?->name ?? 'N/A' }}</td>
                        @endif

                        <td class="px-6 py-4 text-gray-800">
                            @if($trainingApplication->aiGeneratedScore > 0)
                                <span class="font-semibold">{{ $trainingApplication->aiGeneratedScore }}/100</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-gray-800">
                            @if($trainingApplication->status === 'accepted')
                                <span class="text-green-600 font-semibold">{{ $trainingApplication->status }}</span>
                            @elseif($trainingApplication->status === 'rejected')
                                <span class="text-red-600 font-semibold">{{ $trainingApplication->status }}</span>
                            @else
                                <span class="text-gray-600">{{ $trainingApplication->status }}</span>
                            @endif
                        </td>

                        <td>
                            <div class="flex space-x-4">
                                @if (request()->input('archived') == 'true')
                                    <!-- restore button -->
                                    <form action="{{ route('training-application.restore', $trainingApplication->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-green-600 hover:text-green-700">♻️Restore</button>
                                    </form>
                                @else
                                    <!-- edit button -->
                                    <a href="{{ route('training-application.edit', $trainingApplication->id) }}" class="text-blue-500 hover:text-blue-700">✍️Edit</a>
                                    <!-- archive button -->
                                    <form action="{{ route('training-application.destroy', $trainingApplication->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">🗃️Archive</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No training-applications found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div>
            {{ $trainingApplications->links() }}
        </div>
    </div>
</x-app-layout>

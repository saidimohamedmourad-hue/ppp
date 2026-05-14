<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('My Training Applications') }}
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="w-full bg-indigo-500 text-white p-4 rounded-md mb-2">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="w-full bg-red-500 text-white p-4 rounded-md mb-2">
            {{ session('error') }}
        </div>
    @endif

    <div>
        <div class="py-12">
            <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto space-y-4">
                @forelse ($trainingApplications as $trainingApplication)
                    <div class="bg-gray-900 rounded-lg p-4 space-y-3">
                        <div>
                            <h3 class="text-white text-lg font-bold">
                                {{ $trainingApplication->trainingSession->title }}
                            </h3>
                            <p class="text-gray-400">
                                {{ optional($trainingApplication->trainingSession->school)->name }}
                            </p>
                            <p class="text-sm text-gray-400">
                                {{ $trainingApplication->trainingSession->location }}
                            </p>
                            @if ($trainingApplication->trainingSession->trainingDate)
                                <p class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($trainingApplication->trainingSession->trainingDate)->format('M d, Y') }}
                                </p>
                            @endif
                        </div>

                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-400">
                                {{ $trainingApplication->created_at->format('d/m/Y H:i') }}
                            </p>
                            <p class="px-3 py-1 bg-indigo-500 text-white rounded-lg">
                                {{ optional($trainingApplication->trainingSession->trainingCategory)->name }}
                            </p>
                        </div>

                        @if ($trainingApplication->resume)
                            <div class="flex items-center gap-2">
                                <span class="text-gray-300">
                                    Applied with {{ $trainingApplication->resume->filename }}
                                </span>
                                <a href="{{ Storage::disk('cloud')->url($trainingApplication->resume->fileUri) }}"
                                    target="_blank" class="text-blue-500 hover:text-blue-700 underline">
                                    View Resume
                                </a>
                            </div>
                        @endif

                        <div class="flex flex-col gap-2 mt-4">
                            <div class="flex items-center gap-2">
                                @php
                                    $statusColor = match ($trainingApplication->status) {
                                        'pending' => 'bg-yellow-500',
                                        'accepted' => 'bg-green-500',
                                        'rejected' => 'bg-red-500',
                                        default => 'bg-gray-500',
                                    };
                                @endphp
                                <p class="text-sm {{ $statusColor }} text-white w-fit rounded-md p-2">
                                    Status: {{ $trainingApplication->status }}
                                </p>
                                <p class="text-sm bg-indigo-500 text-white p-2 rounded-md w-fit">
                                    Score: {{ $trainingApplication->aiGeneratedScore }}
                                </p>
                            </div>
                            <h4 class="text-md font-bold text-white">AI Feedback:</h4>
                            <p class="text-sm text-gray-300">
                                {{ $trainingApplication->aiGeneratedFeedback }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="bg-gray-800 rounded-lg p-4 text-center">
                        <p class="text-gray-400">No training applications found</p>
                    </div>
                @endforelse
            </div>
            <div class="max-w-7xl mx-auto mt-4">
                {{ $trainingApplications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

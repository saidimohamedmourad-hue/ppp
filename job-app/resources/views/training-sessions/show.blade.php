<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __($trainingSession->title) }} - Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            <a href="{{ route('training-sessions.index') }}" class="text-blue-400 hover:underline mb-6 inline-block">
                &larr; Back to Formations
            </a>
            <div class="border-b border-white/10 pb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $trainingSession->title }}</h1>
                        <p class="text-md text-gray-400">{{ optional($trainingSession->school)->name }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <p class="text-sm text-white">Location: {{ $trainingSession->location }}</p>
                            @if (!is_null($trainingSession->salary))
                                <p class="text-sm text-white">
                                    Stipend: {{ '$'.number_format($trainingSession->salary) }}
                                </p>
                            @endif
                            <p class="bg-indigo-500 text-white p-2 rounded-lg">
                                {{ optional($trainingSession->trainingCategory)->name }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('training-sessions.apply', $trainingSession->id) }}"
                            class="justify-center bg-gradient-to-r from-indigo-500 to-rose-500 text-white rounded-lg px-4 py-2">
                            Apply now
                        </a>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mt-6">
                <div class="col-span-2">
                    <h2 class="text-lg font-bold text-white">Training Description</h2>
                    <p class="text-gray-400">{{ $trainingSession->description }}</p>
                </div>
                <div class="col-span-1">
                    <h2 class="text-lg font-bold text-white">Training Overview</h2>
                    <div class="bg-gray-900 rounded-lg p-6 space-y-4">
                        <p class="text-gray-400">
                            <span class="font-bold text-white">Published Date:</span>
                            {{ $trainingSession->created_at->format('M d, Y') }}
                        </p>
                        <p class="text-gray-400">
                            <span class="font-bold text-white">School:</span>
                            {{ optional($trainingSession->school)->name }}
                        </p>
                        <p class="text-gray-400">
                            <span class="font-bold text-white">Location:</span>
                            {{ $trainingSession->location }}
                        </p>
                        @if ($trainingSession->trainingDate)
                            <p class="text-gray-400">
                                <span class="font-bold text-white">Training Date:</span>
                                {{ \Carbon\Carbon::parse($trainingSession->trainingDate)->format('M d, Y') }}
                            </p>
                        @endif
                        @if ($trainingSession->endDate)
                            <p class="text-gray-400">
                                <span class="font-bold text-white">End Date:</span>
                                {{ \Carbon\Carbon::parse($trainingSession->endDate)->format('M d, Y') }}
                            </p>
                        @endif
                        @if ($trainingSession->startTime)
                            <p class="text-gray-400">
                                <span class="font-bold text-white">Start Time:</span>
                                {{ $trainingSession->startTime }}
                            </p>
                        @endif
                        @if ($trainingSession->endTime)
                            <p class="text-gray-400">
                                <span class="font-bold text-white">End Time:</span>
                                {{ $trainingSession->endTime }}
                            </p>
                        @endif
                        <p class="text-gray-400">
                            <span class="font-bold text-white">Participants:</span>
                            {{ $trainingSession->currentParticipants }}/{{ $trainingSession->maxParticipants }}
                        </p>
                        <p class="text-gray-400">
                            <span class="font-bold text-white">Category:</span>
                            {{ optional($trainingSession->trainingCategory)->name }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

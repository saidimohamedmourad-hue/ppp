<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $trainingApplication->user->name ?? 'Training application' }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <div class="mb-4"><a href="{{ route('training-application.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md">&larr; Back</a></div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p><strong>Applicant:</strong> {{ $trainingApplication->user->name ?? 'N/A' }}</p>
            <p><strong>Session:</strong> {{ $trainingApplication->trainingSession->title ?? 'N/A' }}</p>
            <p><strong>Status:</strong> {{ $trainingApplication->status }}</p>
            <p><strong>AI Score:</strong> {{ $trainingApplication->aiGeneratedScore }}</p>
            <p><strong>Feedback:</strong> {{ $trainingApplication->aiGeneratedFeedback }}</p>
            <div class="mt-4"><a href="{{ route('training-application.edit', ['training_application' => $trainingApplication->id, 'redirectTolist' => 'false']) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md">Edit</a></div>
        </div>
    </div>
</x-app-layout>

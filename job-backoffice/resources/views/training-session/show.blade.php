<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $trainingSession->title }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <div class="mb-4"><a href="{{ route('training-session.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md">&larr; Back to training sessions</a></div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p><strong>School:</strong> {{ $trainingSession->school->name ?? 'N/A' }}</p>
            <p><strong>Category:</strong> {{ $trainingSession->trainingCategory->name ?? 'N/A' }}</p>
            <p><strong>Date:</strong> {{ $trainingSession->trainingDate }}</p>
            <p><strong>Status:</strong> {{ $trainingSession->status }}</p>
            <p><strong>Description:</strong> {{ $trainingSession->description }}</p>
            <div class="mt-4"><a href="{{ route('training-session.edit', ['training_session' => $trainingSession->id, 'redirectTolist' => 'false']) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md">Edit Session</a></div>
        </div>
    </div>
</x-app-layout>

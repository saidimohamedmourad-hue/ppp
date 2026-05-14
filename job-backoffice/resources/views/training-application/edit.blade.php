<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Training Application status') }}</h2></x-slot>
    <div class="overflow-x-auto p-6"><div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <form action="{{ route('training-application.update', ['training_application' => $trainingApplication->id, 'redirectTolist' => request('redirectTolist')]) }}" method="POST">@csrf @method('PUT')
            <p class="mb-2"><strong>Applicant:</strong> {{ $trainingApplication->user->name ?? 'N/A' }}</p>
            <p class="mb-2"><strong>Training Session:</strong> {{ $trainingApplication->trainingSession->title ?? 'N/A' }}</p>
            <p class="mb-2"><strong>AI score:</strong> {{ $trainingApplication->aiGeneratedScore }}</p>
            <p class="mb-4"><strong>AI feedback:</strong> {{ $trainingApplication->aiGeneratedFeedback }}</p>
            <select name="status" class="w-full mb-4 rounded-md">
                @foreach(['pending','accepted','rejected'] as $status)
                    <option value="{{ $status }}" {{ old('status', $trainingApplication->status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
            <div class="flex justify-end space-x-4"><a href="{{ route('training-application.index') }}">Cancel</a><button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Update status</button></div>
        </form>
    </div></div>
</x-app-layout>

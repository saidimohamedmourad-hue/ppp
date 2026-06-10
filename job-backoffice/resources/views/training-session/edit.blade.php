<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit training session') }}</h2></x-slot>
    <div class="overflow-x-auto p-6"><div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <form action="{{ route('training-session.update', ['training_session' => $trainingSession->id, 'redirectTolist' => request('redirectTolist')]) }}" method="POST">@csrf @method('PUT')
            <input class="w-full mb-3 rounded-md" type="text" name="title" value="{{ old('title', $trainingSession->title) }}">
            <textarea class="w-full mb-3 rounded-md" name="description" rows="4">{{ old('description', $trainingSession->description) }}</textarea>
            <input class="w-full mb-3 rounded-md" type="text" name="location" value="{{ old('location', $trainingSession->location) }}">
            <input class="w-full mb-3 rounded-md" type="date" name="trainingDate" value="{{ old('trainingDate', $trainingSession->trainingDate) }}">
            <input class="w-full mb-3 rounded-md" type="date" name="endDate" value="{{ old('endDate', $trainingSession->endDate) }}">
            <input class="w-full mb-3 rounded-md" type="time" name="startTime" value="{{ old('startTime', $trainingSession->startTime) }}">
            <input class="w-full mb-3 rounded-md" type="time" name="endTime" value="{{ old('endTime', $trainingSession->endTime) }}">
            <input class="w-full mb-3 rounded-md" type="number" name="maxParticipants" value="{{ old('maxParticipants', $trainingSession->maxParticipants) }}">
            <input class="w-full mb-3 rounded-md" type="number" name="currentParticipants" value="{{ old('currentParticipants', $trainingSession->currentParticipants) }}">
            <select name="status" class="w-full mb-3 rounded-md">@foreach(['open','closed','cancelled'] as $status)<option value="{{ $status }}" {{ old('status', $trainingSession->status) === $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select>
            <select name="type" class="w-full mb-3 rounded-md">@foreach(['presentiel' => 'Présentiel', 'en_ligne' => 'En ligne', 'accelerer' => 'Accéléré', 'longue_duree' => 'Longue durée'] as $value => $label)<option value="{{ $value }}" {{ old('type', $trainingSession->type) === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
            <label class="block text-sm text-gray-600">Niveau d'études minimum requis *</label>
            <select name="min_education_level" class="w-full mb-3 rounded-md"><option value="">-- Sélectionnez --</option>@foreach(config('education.levels') as $level)<option value="{{ $level }}" {{ old('min_education_level', $trainingSession->min_education_level) === $level ? 'selected' : '' }}>{{ $level }}</option>@endforeach</select>
            <textarea name="cancellation_reason" class="w-full mb-3 rounded-md" rows="2" placeholder="Raison d'annulation (requis si statut = annulé)">{{ old('cancellation_reason', $trainingSession->cancellation_reason) }}</textarea>
            <select name="schoolId" class="w-full mb-3 rounded-md">@foreach($schools as $school)<option value="{{ $school->id }}" {{ old('schoolId', $trainingSession->schoolId) === $school->id ? 'selected' : '' }}>{{ $school->name }}</option>@endforeach</select>
            <select name="trainingCategoryId" class="w-full mb-3 rounded-md">@foreach($categories as $category)<option value="{{ $category->id }}" {{ old('trainingCategoryId', $trainingSession->trainingCategoryId) === $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select>
            <div class="flex justify-end space-x-4"><a href="{{ route('training-session.index') }}">Cancel</a><button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Update Session</button></div>
        </form>
    </div></div>
</x-app-layout>

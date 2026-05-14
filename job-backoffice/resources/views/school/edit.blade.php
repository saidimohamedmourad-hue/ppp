<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit school') }}</h2></x-slot>
    <div class="overflow-x-auto p-6"><div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <form action="{{ route('school.update', ['school' => $school->id, 'redirectTolist' => request('redirectTolist')]) }}" method="POST">@csrf @method('PUT')
            <input class="w-full mb-3 rounded-md" type="text" name="name" value="{{ old('name', $school->name) }}">
            <input class="w-full mb-3 rounded-md" type="text" name="address" value="{{ old('address', $school->address) }}">
            <select class="w-full mb-3 rounded-md" name="industry">@foreach($industries as $industry)<option value="{{ $industry }}" {{ old('industry', $school->industry) === $industry ? 'selected' : '' }}>{{ $industry }}</option>@endforeach</select>
            <input class="w-full mb-3 rounded-md" type="text" name="website" value="{{ old('website', $school->website) }}">
            <textarea class="w-full mb-3 rounded-md" name="description" rows="4">{{ old('description', $school->description) }}</textarea>
            <input class="w-full mb-3 rounded-md" type="text" name="owner_name" value="{{ old('owner_name', $school->owner->name ?? '') }}">
            <input class="w-full mb-3 rounded-md" type="password" name="owner_password" placeholder="New owner password (optional)">
            <div class="flex justify-end space-x-4"><a href="{{ route('school.index') }}">Cancel</a><button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Update school</button></div>
        </form>
    </div></div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add training session') }}</h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <form action="{{ route('training-session.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="title" class="block text-gray-700 font-semibold mb-2">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}"
                        class="{{ $errors->has('title') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('title')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-gray-700 font-semibold mb-2">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="{{ $errors->has('description') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="location" class="block text-gray-700 font-semibold mb-2">Location</label>
                    <input type="text" name="location" id="location" value="{{ old('location') }}"
                        class="{{ $errors->has('location') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('location')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="trainingDate" class="block text-gray-700 font-semibold mb-2">Start date</label>
                        <input type="date" name="trainingDate" id="trainingDate" value="{{ old('trainingDate') }}"
                            class="{{ $errors->has('trainingDate') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('trainingDate')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="endDate" class="block text-gray-700 font-semibold mb-2">End date <span class="text-gray-500 font-normal">(optional)</span></label>
                        <input type="date" name="endDate" id="endDate" value="{{ old('endDate') }}"
                            class="{{ $errors->has('endDate') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('endDate')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="startTime" class="block text-gray-700 font-semibold mb-2">Start time <span class="text-gray-500 font-normal">(optional)</span></label>
                        <input type="time" name="startTime" id="startTime" value="{{ old('startTime') }}"
                            class="{{ $errors->has('startTime') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('startTime')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="endTime" class="block text-gray-700 font-semibold mb-2">End time <span class="text-gray-500 font-normal">(optional)</span></label>
                        <input type="time" name="endTime" id="endTime" value="{{ old('endTime') }}"
                            class="{{ $errors->has('endTime') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('endTime')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="maxParticipants" class="block text-gray-700 font-semibold mb-2">Max participants</label>
                    <input type="number" name="maxParticipants" id="maxParticipants" min="1" value="{{ old('maxParticipants', 1) }}"
                        class="{{ $errors->has('maxParticipants') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('maxParticipants')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="salary" class="block text-gray-700 font-semibold mb-2">Salary / stipend <span class="text-gray-500 font-normal">(optional)</span></label>
                    <input type="text" name="salary" id="salary" value="{{ old('salary') }}"
                        class="{{ $errors->has('salary') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('salary')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="status" class="block text-gray-700 font-semibold mb-2">Status</label>
                    <select name="status" id="status"
                        class="{{ $errors->has('status') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach (['open', 'closed', 'cancelled'] as $statusOption)
                            <option value="{{ $statusOption }}" @selected(old('status', 'open') === $statusOption)>{{ $statusOption }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="type" class="block text-gray-700 font-semibold mb-2">Type</label>
                    <select name="type" id="type"
                        class="{{ $errors->has('type') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach (['presentiel' => 'Présentiel', 'en_ligne' => 'En ligne', 'accelerer' => 'Accéléré', 'longue_duree' => 'Longue durée'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'presentiel') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="min_education_level" class="block text-gray-700 font-semibold mb-2">Niveau d'études minimum requis <span class="text-red-500">*</span></label>
                    <select name="min_education_level" id="min_education_level"
                        class="{{ $errors->has('min_education_level') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Sélectionnez --</option>
                        @foreach (config('education.levels') as $level)
                            <option value="{{ $level }}" @selected(old('min_education_level') === $level)>{{ $level }}</option>
                        @endforeach
                    </select>
                    @error('min_education_level')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="cancellation_reason" class="block text-gray-700 font-semibold mb-2">Raison d'annulation <span class="text-gray-500 font-normal">(requis si statut = annulé)</span></label>
                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3"
                        class="{{ $errors->has('cancellation_reason') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('cancellation_reason') }}</textarea>
                    @error('cancellation_reason')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="schoolId" class="block text-gray-700 font-semibold mb-2">School</label>
                    <select name="schoolId" id="schoolId"
                        class="{{ $errors->has('schoolId') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @forelse ($schools as $school)
                            <option value="{{ $school->id }}" @selected(old('schoolId', $schools->first()->id) == $school->id)>{{ $school->name }}</option>
                        @empty
                            <option value="" disabled selected>No schools available — create a school first.</option>
                        @endforelse
                    </select>
                    @error('schoolId')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="trainingCategoryId" class="block text-gray-700 font-semibold mb-2">Training category</label>
                    <select name="trainingCategoryId" id="trainingCategoryId"
                        class="{{ $errors->has('trainingCategoryId') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @forelse ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('trainingCategoryId', $categories->first()->id) == $category->id)>{{ $category->name }}</option>
                        @empty
                            <option value="" disabled selected>No categories available.</option>
                        @endforelse
                    </select>
                    @error('trainingCategoryId')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('training-session.index') }}" class="text-gray-700 underline py-2">Cancel</a>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md" @disabled($schools->isEmpty() || $categories->isEmpty())>Add Session</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

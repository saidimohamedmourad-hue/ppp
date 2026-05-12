<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add school') }}</h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <form action="{{ route('school.store') }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-semibold mb-2">School name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}"
                        class="{{ $errors->has('name') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="address" class="block text-gray-700 font-semibold mb-2">Address</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                        class="{{ $errors->has('address') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('address')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="industry" class="block text-gray-700 font-semibold mb-2">Industry</label>
                    <select name="industry" id="industry"
                        class="{{ $errors->has('industry') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach ($industries as $industry)
                            <option value="{{ $industry }}" @selected(old('industry', $industries[0] ?? '') === $industry)>{{ $industry }}</option>
                        @endforeach
                    </select>
                    @error('industry')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="website" class="block text-gray-700 font-semibold mb-2">Website <span class="text-gray-500 font-normal">(optional, include https://)</span></label>
                    <input type="text" name="website" id="website" value="{{ old('website') }}"
                        class="{{ $errors->has('website') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('website')
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

                <div class="mb-4 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                    <h3 class="text-gray-800 font-semibold mb-3">School owner account</h3>
                    <div class="mb-4">
                        <label for="owner_name" class="block text-gray-700 font-semibold mb-2">Owner name</label>
                        <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name') }}"
                            class="{{ $errors->has('owner_name') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('owner_name')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="owner_email" class="block text-gray-700 font-semibold mb-2">Owner email</label>
                        <input type="email" name="owner_email" id="owner_email" value="{{ old('owner_email') }}"
                            class="{{ $errors->has('owner_email') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('owner_email')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="owner_password" class="block text-gray-700 font-semibold mb-2">Owner password <span class="text-gray-500 font-normal">(min. 8 characters)</span></label>
                        <input type="password" name="owner_password" id="owner_password"
                            class="{{ $errors->has('owner_password') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('owner_password')
                            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('school.index') }}" class="text-gray-700 underline py-2">Cancel</a>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Add school</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

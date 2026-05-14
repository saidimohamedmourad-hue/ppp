<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add training category') }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <form action="{{ route('training-category.store') }}" method="POST" class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
            @csrf
            <div class="mb-4"><label for="name" class="block mb-2">Category Name</label><input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full rounded-md" required>@error('name')<p class="text-red-500">{{ $message }}</p>@enderror</div>
            <div class="mb-4"><label for="description" class="block mb-2">Description</label><textarea name="description" id="description" rows="4" class="w-full rounded-md">{{ old('description') }}</textarea>@error('description')<p class="text-red-500">{{ $message }}</p>@enderror</div>
            <div class="flex justify-end space-x-4"><a href="{{ route('training-category.index') }}">Cancel</a><button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md">Add Category</button></div>
        </form>
    </div>
</x-app-layout>

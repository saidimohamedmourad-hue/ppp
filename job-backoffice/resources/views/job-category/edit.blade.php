<x-app-layout>
 <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('EDIT job categories') }}
        </h2>
    </x-slot>

<div class="overflow-x-auto p-6">
    
    <form action="{{ route('job-category.update', $category->id) }}" method="POST"
     class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md">
        @csrf
        @method('PUT')
        <div class="mb-4">
            <label for="name" class="block text-gray-700 font-semibold mb-2">Category Name:</label>
            <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}"
             class="{{ $errors->has('name') ? 'outline-red-500  outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            @error('name')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>
         <div class="flex justify-end space-x-4">
        <a href="{{ route('job-category.index') }}"
         class="text-gray-500 hover:text-gray-700">Cancel</a>
         
       
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Update Category</button>
        </div>
    </form>

</div>

    </x-app-layout>
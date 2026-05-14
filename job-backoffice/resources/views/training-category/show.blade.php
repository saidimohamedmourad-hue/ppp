<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $category->name }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <div class="mb-4"><a href="{{ route('training-category.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md">&larr; Back</a></div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p><strong>Name:</strong> {{ $category->name }}</p>
            <p><strong>Description:</strong> {{ $category->description ?? 'N/A' }}</p>
        </div>
    </div>
</x-app-layout>

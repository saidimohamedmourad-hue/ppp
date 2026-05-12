<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('training categories') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />
        <div class="flex justify-end items-center space-x-4 mb-4">
            @if (request()->input('archived') == 'true')
                <a href="{{ route('training-category.index') }}" class="bg-green-500 text-white px-4 py-2 rounded-md">Active Categories</a>
            @else
                <a href="{{ route('training-category.index', ['archived' => 'true']) }}" class="bg-black text-white px-4 py-2 rounded-md">Archived Categories</a>
            @endif
            <a href="{{ route('training-category.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md">+ Add New Category</a>
        </div>
        <table class="min-w-full divide-y border divide-gray-200 rounded-lg bg-white">
            <thead><tr><th class="px-6 py-3 text-left">Name</th><th class="px-6 py-3 text-left">Actions</th></tr></thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b">
                        <td class="px-6 py-4"><a class="text-blue-500 underline" href="{{ route('training-category.show', $category->id) }}">{{ $category->name }}</a></td>
                        <td class="px-6 py-4">
                            @if (request()->input('archived') == 'true')
                                <form action="{{ route('training-category.restore', $category->id) }}" method="POST">@csrf @method('PUT')<button type="submit" class="text-green-600">Restore</button></form>
                            @else
                                <a href="{{ route('training-category.edit', $category->id) }}" class="text-blue-500 mr-4">Edit</a>
                                <form action="{{ route('training-category.destroy', $category->id) }}" method="POST" class="inline-block">@csrf @method('DELETE')<button type="submit" class="text-red-600">Archive</button></form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-6 py-4 text-center text-gray-500">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $categories->links() }}
    </div>
</x-app-layout>

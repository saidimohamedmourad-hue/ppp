<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('schools') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}
        </h2>
    </x-slot>

    <div class="overflow-x-auto p-6">
        <x-toast-notification />

        <div class="flex justify-end items-center space-x-4 mb-4">
            @if (request()->input('archived') == 'true')
                <!-- Active -->
                <a href="{{ route('school.index') }}"
                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Active schools
                </a>
            @else
                <!-- Archived -->
                <a href="{{ route('school.index', ['archived' => 'true']) }}"
                    class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black">
                    Archived schools
                </a>
            @endif

            <!-- Add New school Button -->
            <a href="{{ route('school.create') }}"
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                + Add New school
            </a>
        </div>

        <!-- Table schools -->
        <table class="min-w-full divide-y border divide-gray-200 rounded-lg shadow-mt-4 bg-white">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Address</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Industry</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Website</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Téléphone</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-800">actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($schools as $school)
                    <tr class="border-b">
                        <td class="px-6 py-4 text-gray-800">
                            @if(request()->input('archived') == 'true')
                                <span class="text-gray-500">{{ $school->name }}</span>
                            @else
                                <a class="text-blue-500 hover:text-blue-700 underline" href="{{ route('school.show', $school->id) }}">{{ $school->name }}</a>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-800">{{ $school->address }}</td>
                        <td class="px-6 py-4 text-gray-800">{{ $school->industry }}</td>
                        <td class="px-6 py-4 text-gray-800">{{ $school->website }}</td>
                        <td class="px-6 py-4 text-gray-800">
                            @if($school->phone)
                                <a class="text-blue-500 hover:text-blue-700 underline" href="tel:{{ $school->phone }}">{{ $school->phone }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex space-x-4">
                                @if (request()->input('archived') == 'true')
                                    <!-- restore button -->
                                    <form action="{{ route('school.restore', $school->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-green-600 hover:text-green-700">♻️Restore</button>
                                    </form>
                                @else
                                    <!-- edit button -->
                                    <a href="{{ route('school.edit', $school->id) }}" class="text-blue-500 hover:text-blue-700">✍️Edit</a>
                                    <!-- delete button -->
                                    <form action="{{ route('school.destroy', $school->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700">🗃️Archive</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No school found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div>
            {{ $schools->links() }}
        </div>
    </div>
</x-app-layout>

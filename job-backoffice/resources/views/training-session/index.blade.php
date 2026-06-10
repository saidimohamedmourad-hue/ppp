<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('training sessions') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}
        </h2>
    </x-slot>

    <div class="overflow-x-auto p-6">
        <x-toast-notification />

        <div class="flex justify-end items-center space-x-4 mb-4">
            @if (request()->input('archived') == 'true')
                <!-- Active -->
                <a href="{{ route('training-session.index') }}"
                    class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Active Sessions
                </a>
            @else
                <!-- Archived -->
                <a href="{{ route('training-session.index', ['archived' => 'true']) }}"
                    class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black">
                    Archived Sessions
                </a>
            @endif

            <!-- Add New session Button -->
            <a href="{{ route('training-session.create') }}"
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                + Add Session
            </a>
        </div>

        <!-- Table -->
        <table class="min-w-full divide-y border divide-gray-200 rounded-lg shadow-mt-4 bg-white">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Title</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">School</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Location</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($trainingSessions as $trainingSession)
                    <tr class="border-b">
                        <td class="px-6 py-4 text-gray-800">
                            @if(request()->input('archived') == 'true')
                                <span class="text-gray-500">{{ $trainingSession->title }}</span>
                            @else
                                <a class="text-blue-500 hover:text-blue-700 underline" href="{{ route('training-session.show', $trainingSession->id) }}">{{ $trainingSession->title }}</a>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-800">{{ $trainingSession->school->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-800">{{ $trainingSession->type ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-800">{{ $trainingSession->location ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-gray-800">{{ $trainingSession->trainingDate }}</td>
                        <td class="px-6 py-4 text-gray-800">
                            @if($trainingSession->status === 'open')
                                <span class="text-green-600 font-semibold">{{ $trainingSession->status }}</span>
                            @elseif($trainingSession->status === 'cancelled')
                                <span class="text-red-600 font-semibold">{{ $trainingSession->status }}</span>
                            @else
                                <span class="text-gray-600">{{ $trainingSession->status }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex space-x-4">
                                @if (request()->input('archived') == 'true')
                                    <!-- restore button -->
                                    <form action="{{ route('training-session.restore', $trainingSession->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-green-600 hover:text-green-700">♻️Restore</button>
                                    </form>
                                @else
                                    <!-- edit button -->
                                    <a href="{{ route('training-session.edit', $trainingSession->id) }}" class="text-blue-500 hover:text-blue-700">✍️Edit</a>
                                    <!-- archive button -->
                                    <form action="{{ route('training-session.destroy', $trainingSession->id) }}" method="POST" class="inline-block">
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
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No training sessions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div>
            {{ $trainingSessions->links() }}
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $school->name }}
        </h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />

        <!-- back button (admin only) -->
        @if (auth()->user()->role == 'admin')
            <div class="mb-4">
                <a href="{{ route('school.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    &larr; Back to Schools
                </a>
            </div>
        @endif

        <!-- wrapper -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <!-- School Details -->
            <h3 class="text-lg font-semibold mb-4">School Informations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-700"><strong>Owner Name:</strong> {{ $school->owner->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Email:</strong> {{ $school->owner->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Address:</strong> {{ $school->address }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Industry:</strong> {{ $school->industry }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Website:</strong>
                        @if($school->website)
                            <a class="text-blue-500 hover:text-blue-700 underline" href="{{ $school->website }}" target="_blank">{{ $school->website }}</a>
                        @else
                            <span class="text-gray-400">N/A</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Téléphone:</strong>
                        @if($school->phone)
                            <a class="text-blue-500 hover:text-blue-700 underline" href="tel:{{ $school->phone }}">{{ $school->phone }}</a>
                        @else
                            <span class="text-gray-400">non renseigné</span>
                        @endif
                    </p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-gray-700"><strong>Description:</strong> {{ $school->description ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- edit and delete buttons -->
            <div class="flex justify-end space-x-4 m-6">
                @if (auth()->user()->role == 'admin')
                    <a href="{{ route('school.edit', ['school' => $school->id, 'redirectTolist' => 'false']) }}"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        Edit School
                    </a>
                @else
                    <a href="{{ route('my-school.edit') }}"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                        Edit
                    </a>
                @endif

                @if (auth()->user()->role == 'admin')
                    <form action="{{ route('school.destroy', $school->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Delete School
                        </button>
                    </form>
                @endif
            </div>

            <!-- tabs navigation (admin only) -->
            @if (auth()->user()->role == 'admin')
                <div class="mb-6">
                    <ul class="flex space-x-4">
                        <li>
                            <a href="{{ route('school.show', ['school' => $school->id, 'tab' => 'sessions']) }}"
                                class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'sessions' || request('tab') == '' ? 'border-b2 border-blue-700' : '' }}">
                                Training Sessions
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('school.show', ['school' => $school->id, 'tab' => 'applications']) }}"
                                class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'applications' ? 'border-b2 border-blue-700' : '' }}">
                                Applications
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- tab content -->
                <div>
                    <!-- sessions table -->
                    <div id="sessions" class="{{ request('tab') == 'sessions' || request('tab') == '' ? 'block' : 'hidden' }}">
                        <table class="min-w-full bg-gray-50 rounded-lg shadow">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Title</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Location</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($school->trainingSessions as $session)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $session->title }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $session->type }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $session->location }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $session->status }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <a href="{{ route('training-session.show', $session->id) }}" class="text-blue-500 hover:text-blue-700">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-2 px-4 text-center">No training sessions</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- applications table -->
                    <div id="applications" class="{{ request('tab') == 'applications' ? 'block' : 'hidden' }}">
                        <table class="min-w-full bg-gray-50 rounded-lg shadow">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applicant Name</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Téléphone</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Session</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($school->trainingApplications as $application)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $application->user->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @if($application->user?->phone)
                                                <a class="text-blue-500 hover:text-blue-700 underline" href="tel:{{ $application->user->phone }}">{{ $application->user->phone }}</a>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $application->trainingSession?->title ?? __('Session supprimée') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $application->status }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <a href="{{ route('training-application.show', $application->id) }}" class="text-blue-500 hover:text-blue-700">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-2 px-4 text-center">No applications</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

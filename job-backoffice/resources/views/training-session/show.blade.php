<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $trainingSession->title }}
        </h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />

        <!-- back button -->
        <div class="mb-4">
            <a href="{{ route('training-session.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                &larr; Back to training sessions
            </a>
        </div>

        <!-- wrapper -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <!-- Session Details -->
            <h3 class="text-lg font-semibold mb-4">Training Session Informations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-700"><strong>Title:</strong> {{ $trainingSession->title }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>School:</strong> {{ $trainingSession->school->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Category:</strong> {{ $trainingSession->trainingCategory->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Type:</strong> {{ $trainingSession->type ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Niveau d'études minimum:</strong> {{ $trainingSession->min_education_level ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Location:</strong> {{ $trainingSession->location ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Status:</strong>
                        @if($trainingSession->status === 'open')
                            <span class="text-green-600 font-semibold">{{ $trainingSession->status }}</span>
                        @elseif($trainingSession->status === 'cancelled')
                            <span class="text-red-600 font-semibold">{{ $trainingSession->status }}</span>
                        @else
                            <span class="text-gray-600">{{ $trainingSession->status }}</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Start Date:</strong> {{ $trainingSession->trainingDate ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>End Date:</strong> {{ $trainingSession->endDate ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Hours:</strong> {{ $trainingSession->startTime ?? '—' }} → {{ $trainingSession->endTime ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Participants:</strong> {{ $trainingSession->currentParticipants ?? 0 }} / {{ $trainingSession->maxParticipants ?? '∞' }}
                        @if($trainingSession->is_full)
                            <span class="text-xs text-red-600">(complète)</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Price:</strong> {{ $trainingSession->salary ? $trainingSession->salary.' DA' : 'Gratuit' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Views:</strong> {{ $trainingSession->viewCount ?? 0 }}</p>
                </div>
                @if($trainingSession->status === 'cancelled' && $trainingSession->cancellation_reason)
                    <div class="md:col-span-2">
                        <p class="text-gray-700"><strong>Raison d'annulation:</strong> <span class="text-red-600">{{ $trainingSession->cancellation_reason }}</span></p>
                    </div>
                @endif
                <div class="md:col-span-2">
                    <p class="text-gray-700"><strong>Description:</strong> {{ $trainingSession->description }}</p>
                </div>
            </div>

            <!-- edit and delete buttons -->
            <div class="flex justify-end space-x-4 m-6">
                <a href="{{ route('training-session.edit', ['training_session' => $trainingSession->id, 'redirectTolist' => 'false']) }}"
                    class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Edit Session
                </a>
                @if($trainingSession->trashed())
                    <form action="{{ route('training-session.restore', $trainingSession->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                            Restore Session
                        </button>
                    </form>
                @else
                    <form action="{{ route('training-session.destroy', $trainingSession->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                            Archive Session
                        </button>
                    </form>
                @endif
            </div>

            <!-- applicants tab -->
            <div class="mb-6">
                <ul class="flex space-x-4">
                    <li>
                        <span class="text-gray-800 font-medium border-b-2 border-blue-700 pb-2">Applications ({{ $trainingSession->trainingApplications->count() }})</span>
                    </li>
                </ul>
            </div>
            <div>
                <table class="min-w-full bg-gray-50 rounded-lg shadow">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applicant Name</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">AI Score</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($trainingSession->trainingApplications as $application)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $application->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $application->aiGeneratedScore > 0 ? $application->aiGeneratedScore.'/100' : '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $application->status }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="{{ route('training-application.show', $application->id) }}" class="text-blue-500 hover:text-blue-700">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-2 px-4 text-center">No applications</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

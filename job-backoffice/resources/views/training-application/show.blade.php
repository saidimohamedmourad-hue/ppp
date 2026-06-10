<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $trainingApplication->user->name ?? 'Candidat' }} — {{ $trainingApplication->trainingSession?->title ?? __('Session supprimée') }}
        </h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />

        <!-- back button -->
        <div class="mb-4">
            <a href="{{ route('training-application.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                &larr; Back to training applications
            </a>
        </div>

        <!-- wrapper -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <!-- Application Details -->
            <h3 class="text-lg font-semibold mb-4">Training Application Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-700"><strong>Applicant:</strong> {{ $trainingApplication->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Email:</strong> {{ $trainingApplication->user->email ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Session:</strong> {{ $trainingApplication->trainingSession?->title ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>School:</strong> {{ $trainingApplication->trainingSession?->school?->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Niveau d'études:</strong> {{ $trainingApplication->education_level ?? 'Non renseigné' }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Status:</strong>
                        @if($trainingApplication->status === 'accepted')
                            <span class="text-green-600 font-semibold">{{ $trainingApplication->status }}</span>
                        @elseif($trainingApplication->status === 'rejected')
                            <span class="text-red-600 font-semibold">{{ $trainingApplication->status }}</span>
                        @else
                            <span class="text-gray-600">{{ $trainingApplication->status }}</span>
                        @endif
                        @if($trainingApplication->is_waitlist)
                            <span class="text-xs text-amber-600">⏳ liste d'attente</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Resume:</strong>
                        @if($trainingApplication->resume)
                            <a class="text-blue-500 hover:text-blue-700 underline" href="{{ $trainingApplication->resume->fileUri }}" target="_blank">{{ $trainingApplication->resume->filename ?? $trainingApplication->resume->fileUri }}</a>
                        @else
                            <span class="text-gray-400">aucun CV</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- edit and delete buttons -->
            <div class="flex justify-end space-x-4 m-6">
                <a href="{{ route('training-application.edit', ['training_application' => $trainingApplication->id, 'redirectTolist' => 'false']) }}"
                    class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Edit Application
                </a>
                <form action="{{ route('training-application.destroy', $trainingApplication->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Archive Application
                    </button>
                </form>
            </div>

            <!-- tabs navigation -->
            <div class="mb-6">
                <ul class="flex space-x-4">
                    <li>
                        <a href="{{ route('training-application.show', ['training_application' => $trainingApplication->id, 'tab' => 'resume']) }}"
                            class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'resume' || request('tab') == '' ? 'border-b2 border-blue-900' : '' }}">
                            Resume
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('training-application.show', ['training_application' => $trainingApplication->id, 'tab' => 'AIFeedback']) }}"
                            class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'AIFeedback' ? 'border-b2 border-blue-900' : '' }}">
                            AI Feedback
                        </a>
                    </li>
                </ul>
            </div>

            <!-- tab content -->
            <div>
                <!-- resume tab -->
                <div id="resume" class="{{ request('tab') == 'resume' || request('tab') == '' ? 'block' : 'hidden' }}">
                    <table class="min-w-full bg-gray-50 rounded-lg shadow">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Summary</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Skills</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Education</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Experience</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $trainingApplication->resume->summary ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $trainingApplication->resume->skills ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $trainingApplication->resume->education ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $trainingApplication->resume->experience ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- AI Feedback tab -->
                <div id="AIFeedback" class="{{ request('tab') == 'AIFeedback' ? 'block' : 'hidden' }}">
                    <table class="min-w-full bg-gray-50 rounded-lg shadow">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">AI Score</th>
                                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $trainingApplication->aiGeneratedScore }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $trainingApplication->aiGeneratedFeedback }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

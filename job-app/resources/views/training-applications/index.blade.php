<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Mes formations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 space-y-4">

            @if(session('success'))
                <div class="bg-green-600 text-white p-4 rounded-lg mb-2">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-600 text-white p-4 rounded-lg mb-2">{{ session('error') }}</div>
            @endif

            @forelse($trainingApplications as $app)
            @php
                $statusColor = match($app->status) {
                    'pending'  => 'bg-yellow-500 text-white',
                    'reviewed' => 'bg-blue-500 text-white',
                    'accepted' => 'bg-green-500 text-white',
                    'rejected' => 'bg-red-500 text-white',
                    default    => 'bg-gray-500 text-white',
                };
                $statusLabel = match($app->status) {
                    'pending'  => 'En attente',
                    'reviewed' => 'Examiné',
                    'accepted' => 'Accepté',
                    'rejected' => 'Refusé',
                    default    => $app->status,
                };
            @endphp

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-5 space-y-3">

                {{-- Header --}}
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <h3 class="text-white text-lg font-bold leading-tight">
                            {{ optional($app->trainingSession)->title ?? 'Formation supprimée' }}
                        </h3>
                        <p class="text-gray-400 text-sm mt-0.5">
                            {{ optional($app->trainingSession?->school)->name ?? '—' }}
                        </p>
                    </div>
                    <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Meta --}}
                @if($app->trainingSession)
                <div class="flex flex-wrap gap-3 text-sm text-gray-400">
                    <span>📍 {{ $app->trainingSession->location }}</span>
                    @if($app->trainingSession->trainingDate)
                    <span>📅 {{ \Carbon\Carbon::parse($app->trainingSession->trainingDate)->format('d/m/Y') }}</span>
                    @endif
                    @if(optional($app->trainingSession->trainingCategory)->name)
                    <span class="px-2 py-0.5 bg-indigo-500 text-white rounded-full text-xs">
                        {{ $app->trainingSession->trainingCategory->name }}
                    </span>
                    @endif
                    <span>🕐 Candidaté le {{ $app->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @else
                <p class="text-sm text-gray-500 italic">🕐 Candidaté le {{ $app->created_at->format('d/m/Y H:i') }}</p>
                @endif

                {{-- Resume --}}
                @if($app->resume)
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-400">CV :</span>
                    <span class="text-white font-medium">{{ $app->resume->filename }}</span>
                    <a href="{{ Storage::url($app->resume->fileUri) }}"
                       target="_blank"
                       class="text-blue-400 hover:text-blue-300 underline ml-1">
                        Voir le CV
                    </a>
                </div>
                @endif

                {{-- AI Feedback --}}
                <div class="bg-gray-800 rounded-lg p-3 space-y-2">
                    <p class="text-sm font-semibold text-indigo-400">
                        🤖 Score IA : {{ $app->aiGeneratedScore ?? 0 }}/100
                    </p>
                    <p class="text-sm font-semibold text-indigo-400">
                        💬 Feedback IA :
                    </p>
                    <p class="text-sm text-gray-300">
                        {{ $app->aiGeneratedFeedback ?: 'Pas encore de feedback IA pour cette candidature.' }}
                    </p>
                </div>

            </div>
            @empty
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-10 text-center">
                <p class="text-gray-400 text-lg">Aucune candidature à une formation pour le moment.</p>
                <a href="{{ route('training-sessions.index') }}" class="mt-4 inline-block text-indigo-400 hover:underline">
                    Voir les formations disponibles →
                </a>
            </div>
            @endforelse

            @if($trainingApplications->hasPages())
            <div class="mt-4">{{ $trainingApplications->links() }}</div>
            @endif

        </div>
    </div>
</x-app-layout>

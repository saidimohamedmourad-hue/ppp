<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Mes candidatures') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 space-y-4">

            @if(session('success'))
                <div class="bg-green-600 text-white p-4 rounded-lg mb-2">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($jobApplications as $jobApplication)
            @php
                $statusColor = match($jobApplication->status) {
                    'pending'     => 'bg-yellow-500 text-white',
                    'reviewed'    => 'bg-blue-500 text-white',
                    'shortlisted' => 'bg-indigo-500 text-white',
                    'accepted'    => 'bg-green-500 text-white',
                    'rejected'    => 'bg-red-500 text-white',
                    default       => 'bg-gray-500 text-white',
                };
                $statusLabel = match($jobApplication->status) {
                    'pending'     => 'En attente',
                    'reviewed'    => 'Examiné',
                    'shortlisted' => 'Présélectionné',
                    'accepted'    => 'Accepté',
                    'rejected'    => 'Refusé',
                    default       => $jobApplication->status,
                };
            @endphp

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-5 space-y-3">

                {{-- Header --}}
                <div class="flex justify-between items-start gap-3">
                    <div>
                        <h3 class="text-white text-lg font-bold leading-tight">
                            {{ optional($jobApplication->jobVacancy)->title ?? 'Offre supprimée' }}
                        </h3>
                        <p class="text-gray-400 text-sm mt-0.5">
                            {{ optional($jobApplication->jobVacancy?->company)->name ?? '—' }}
                        </p>
                    </div>
                    <span class="shrink-0 px-3 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">
                        {{ $statusLabel }}
                    </span>
                </div>

                {{-- Meta --}}
                @if($jobApplication->jobVacancy)
                <div class="flex flex-wrap gap-3 text-sm text-gray-400">
                    <span class="flex items-center gap-1">
                        📍 {{ $jobApplication->jobVacancy->location }}
                    </span>
                    <span class="flex items-center gap-1">
                        💼 {{ $jobApplication->jobVacancy->type }}
                    </span>
                    @if($jobApplication->jobVacancy->salary)
                    <span class="flex items-center gap-1">
                        💰 {{ number_format($jobApplication->jobVacancy->salary, 0, ',', ' ') }} DA/an
                    </span>
                    @endif
                    <span class="flex items-center gap-1">
                        📅 {{ $jobApplication->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                @else
                <p class="text-sm text-gray-500 italic">📅 Candidaté le {{ $jobApplication->created_at->format('d/m/Y H:i') }}</p>
                @endif

                {{-- Resume --}}
                @if($jobApplication->resume)
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-gray-400">CV :</span>
                    <span class="text-white font-medium">{{ $jobApplication->resume->filename }}</span>
                    <a href="{{ Storage::url($jobApplication->resume->fileUri) }}"
                       target="_blank"
                       class="text-blue-400 hover:text-blue-300 underline ml-1">
                        Voir le CV
                    </a>
                </div>
                @endif

                {{-- AI Feedback --}}
                <div class="bg-gray-800 rounded-lg p-3 space-y-2">
                    <p class="text-sm font-semibold text-indigo-400">
                        🤖 Score IA : {{ $jobApplication->aiGeneratedScore ?? 0 }}/100
                    </p>
                    <p class="text-sm font-semibold text-indigo-400">
                        💬 Feedback IA :
                    </p>
                    <p class="text-sm text-gray-300">
                        {{ $jobApplication->aiGeneratedFeedback ?: 'Pas encore de feedback IA pour cette candidature.' }}
                    </p>
                </div>

            </div>
            @empty
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-10 text-center">
                <p class="text-gray-400 text-lg">Aucune candidature pour le moment.</p>
                <a href="{{ route('dashboard') }}" class="mt-4 inline-block text-indigo-400 hover:underline">
                    Voir les offres disponibles →
                </a>
            </div>
            @endforelse

            {{-- Pagination --}}
            @if($jobApplications->hasPages())
            <div class="mt-4">
                {{ $jobApplications->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>

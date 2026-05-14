<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 px-6 flex flex-col gap-6">
        @if ($showJobTab)
            <div x-data="{ tab: 'jobs' }" class="flex flex-col gap-6">
                <div class="inline-flex rounded-lg border border-gray-200 bg-gray-50 p-1 shadow-sm self-start" role="tablist">
                    <button type="button"
                        @click="tab = 'jobs'"
                        :class="tab === 'jobs' ? 'bg-white text-indigo-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="rounded-md px-4 py-2 text-sm font-medium transition">
                        {{ __('Jobs') }}
                    </button>
                    <button type="button"
                        @click="tab = 'training'"
                        :class="tab === 'training' ? 'bg-white text-teal-700 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
                        class="rounded-md px-4 py-2 text-sm font-medium transition">
                        {{ __('Training') }}
                    </button>
                </div>

                <div x-show="tab === 'jobs'" x-cloak class="flex flex-col gap-4">
                    @include('dashboard.partials.job-analytics', ['jobAnalytics' => $jobAnalytics])
                </div>

                <div x-show="tab === 'training'" x-cloak class="flex flex-col gap-4">
                    @include('dashboard.partials.training-analytics', ['trainingAnalytics' => $trainingAnalytics])
                </div>
            </div>
        @else
            @if (! $hasSchool)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-900">
                    {{ __('No school is linked to your account yet. Once an administrator assigns your school, training statistics will appear here.') }}
                </div>
            @else
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Training — your school') }}</h3>
                <div class="flex flex-col gap-4">
                    @include('dashboard.partials.training-analytics', ['trainingAnalytics' => $trainingAnalytics])
                </div>
            @endif
        @endif
    </div>
</x-app-layout>

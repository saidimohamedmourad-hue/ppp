<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Formations') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            <h3 class="text-white text-2xl font-bold mb-6">
                {{ 'Welcome Back, ' }}{{ Auth::user()->name }}!
            </h3>

            <div class="flex items-center justify-between">
                <form action="{{ route('training-sessions.index') }}" method="get" class="flex items-center justify-center w-1/2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="w-full p-2 rounded-l-lg bg-gray-800 text-white"
                        placeholder="Search for a training session, school or category">
                    <button type="submit" class="bg-indigo-500 text-white p-2 rounded-r-lg border border-indigo-500">
                        Search
                    </button>
                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    @if (request('search'))
                        <a href="{{ route('training-sessions.index', ['category' => request('category')]) }}"
                            class="text-white p-1 rounded-lg">Clear</a>
                    @endif
                </form>
            </div>

            <div class="space-y-4 mt-6">
                @forelse ($trainingSessions as $session)
                    <div class="border-b border-white/10 pb-4 flex justify-between items-center">
                        <div>
                            <a href="{{ route('training-sessions.show', $session->id) }}"
                                class="text-lg font-semibold text-blue-400 hover:underline">
                                {{ $session->title }}
                            </a>
                            <p class="text-sm text-white">
                                {{ optional($session->school)->name }} - {{ $session->location }}
                            </p>
                            <p class="text-sm text-gray-400">
                                {{ optional($session->trainingCategory)->name }}
                                @if ($session->trainingDate)
                                    - {{ \Carbon\Carbon::parse($session->trainingDate)->format('M d, Y') }}
                                @endif
                            </p>
                            @if (!is_null($session->salary))
                                <p class="text-sm text-white">{{ '$'.number_format($session->salary) }}</p>
                            @endif
                        </div>
                        <span class="bg-blue-500 text-white p-4 rounded-lg">
                            {{ $session->currentParticipants }}/{{ $session->maxParticipants }}
                        </span>
                    </div>
                @empty
                    <p class="text-white text-2xl font-bold">No training sessions found</p>
                @endforelse
            </div>
            <div class="mt-6">
                {{ $trainingSessions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

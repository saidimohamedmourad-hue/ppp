<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
       <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
        <h3 class="text-white text-2xl font-bold mb-6">
            {{ 'Welcom Back,' }}{{ Auth::user()->name }}!
        </h3>
<!--search $ filter-->
    <div class="flex items-center justify-between">
        <!--search Bar-->
        <form action="{{ route('dashboard') }}" method='get'class="flex items-center justify-center w-1/4">
            <input type="text" name="search" value="{{ request('search') }}" class="w-full p-2 rounded-l-lg bg-gray-800 text-white" placeholder="search for a job">
            <button type="submit" class="bg-indigo-500 text-white p-2 rounded-r-lg border border-indigo-500">Search</button>
            @if (request('filter'))
            <input type="hidden" name="filter" value="{{ request('filter') }}">
            
            @endif
               @if (request('search') )
             
             
             <a href="{{ route('dashboard', [ 'filter' => request('filter')]) }}"
             class=" text-withe p-1 rounded-lg">Clear </a>
               @endif   
        </form>
        <!--Filters-->
        <div class="flex space-x-2">
            <a href="{{ route('dashboard',['filter' => 'Full-time', 'search' => request('search')]) }}" 
            class="bg-indigo-500 text-withe p-2 rounded-lg">Full-Time</a>
            <a href="{{ route('dashboard',['filter' => 'Remote', 'search' => request('search')]) }}"
             class="bg-indigo-500 text-withe p-2 rounded-lg">Remote</a>
            <a href="{{ route('dashboard',['filter' => 'Hybrid', 'search' => request('search')]) }}"
             class="bg-indigo-500 text-withe p-2 rounded-lg">Hybride</a>
            <a href="{{ route('dashboard', ['filter' => 'Contract', 'search' => request('search')]) }}"
             class="bg-indigo-500 text-withe p-2 rounded-lg">Contract</a>

             @if (request('filter') )
             
             
             <a href="{{ route('dashboard', [ 'search' => request('search')]) }}"
             class="text-withe p-2 rounded-lg">Clear </a>
               @endif   
        </div>
    </div>

        <!-- Job List -->
         <div class="space-y-4 mt-6">
            @forelse ($jobs as $job )
            
           
            <!--Job Items-->
            <div class="border-b border-white/10 pb-4 flex justify-between ltems-center">
            <div>
                <a href="{{ route('job-vacancy.show', $job) }}" class="text-lg font-semibold text-blue-400 hover:underline">{{ $job->title }}</a>
                <p class="text-sm text-white">{{ $job->company->name }}- {{ $job->location }}<p>
                <p class="text-sm text-white">{{'$'. number_format($job->salary) }}/Year</p>
            
           </div>
        <span class="bg-blue-500 text-white p-4 rounded-lg">{{ $job->type }}</span>
       </div>
       @empty
       <p class="text-white text-2xl font-bold">No jobs found</p>
        @endforelse
       </div>
       <div class="mt-6">
       {{ $jobs->links() }}
       </div>
       </div>

    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('My Applications') }} 
        </h2>

    </x-slot>
    <!--validation session -->
    @if(session('success'))
    <div class="w-full bg-indigo-500 text-white p-4 rounded-mb mb-2">
        {{ session('success') }}
    </div>
    @endif
    <div> <div class="py-12">
    <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto space-y-4">
        @forelse($jobApplications as $jobApplication)
        <div class="bg-gray-900 rounded-lg ">
            <h3 class="text-white text-lg font-bold">{{ $jobApplication->jobVacancy->title }}</h3>
            <p class="text-gray-400">{{ $jobApplication->jobVacancy->company->name }}<
            <p class="text-sm">{{ $jobApplication->jobVacancy->location }}</p>
            <p class="text-xs">{{ $jobApplication->jobVacancy->salary }}</p>
        </div>
        <div class="flex justify-between items-center">
            <p class="text-sm">{{ $jobApplication->created_at->format('d/m/Y H:i') }}</p>
            <p class="px-3 py-1 bg-indigo-500 text-white rounded-lg">{{ $jobApplication->jobVacancy->type }}</p>
          
        </div>
        <div class="flex  items-center gap-2">
          <span>Applied with {{ $jobApplication->resume->filename }}</span>
          <a href="{{ Storage::disk('cloud')->url($jobApplication->resume->fileUri) }}" target="_blank" class="text-blue-500 hover:text-blue-700 underline">View Resume</a>
        </div>

        <div class="flex flex-start flex-col gap-2 mt-4">
            <div class="flex items-center gap-2">
                @php
                $statusColor = match($jobApplication->status) {
                    'pending' => 'bg-yellow-500',
                    'accepted' => 'bg-green-500',
                    'rejected' => 'bg-red-500',
                };
                @endphp
        <p class="text-sm {{$statusColor}} w-fit rounded-md p-2">status:{{ $jobApplication->status }}</p>
        <p class="text-sm bg-indigo-500 text-white p-2 rounded-md w-fit"> Score: 
        {{ $jobApplication->aiGeneratedScore }}</p>
            </div>
            
            <h4 class="text-md font-bold">AI Feedback:</h4>
           <p class="text-sm"> {{ $jobApplication->aiGeneratedFeedback }}</p>
        </div>
        @empty
        <div class="bg-gray-800 rounded-lg p-4 text-center">
            <p class="text-gray-400">No applications found</p>
        </div>
        @endforelse
    </div>
    <div>
        {{ $jobApplications->links() }}
    </div>
    </div>
    </div>
</x-app-layout>
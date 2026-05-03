<x-app-layout>
        <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __($jobVacancy->title) }} - Destails 
        </h2>
    </x-slot>
    <div class="py-12">
     <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
    <a href="{{ route('dashboard') }}" class="text-blue-400 hover:underline mb-6 inline-block" >
        &larr; Back to Formations
    </a>
    <div class="border-b border-white/10 pb-6">
        <div class="flex items-center justify-between">
            <div>
        <h1 class="text-3xl font-bold text-white">{{ $jobVacancy->title }}</h1>
        <P class="text-md text-gray-400">{{ $jobVacancy->company->name }}</P>
        <div class="flex items-center gap-2">
            <p class="text-sm text-white">Location: {{ $jobVacancy->location }}</p>
            <p class="text-sm text-white">Price: {{ '$'. number_format($jobVacancy->salary) }}/Year</p>
               <p class="bg-indigo-500 text-withe p-2 rounded-lg">Type: {{ $jobVacancy->type }}</p>
        </div>
        </div>
        <div>
            <a href="{{ route('job-vacancy.apply', $jobVacancy->id) }}" class="justify-center bg-gradient-to-r from-indigo-500 to-rose-500 text-white rounded-lg px-4 py-2">
                Apply now</a>
            </div>
    </div>
     </div>
     <div class="grid grid-cols-3  gap-4 mt-6">
  <div class=" col-span-2">
    <h2 class="text-lg font-bold text-white">Formation Description</h2>
    <p class="text-gray-400 ">{{ $jobVacancy->description }}</p>
  </div>
  <div class=" col-sapan-1">
    <h2 class="text-lg font-bold text-white">Formation Overview</h2>
    <div class="bg-gray-900 rounded-lg p-6 space-y-4">
         <p class="text-gray-400"><span class="font-bold text-white">Published Date:</span> {{ $jobVacancy->created_at->format('M d, Y') }}</p>
         <p class="text-gray-400"><span class="font-bold text-white">Company:</span> {{ $jobVacancy->company->name }}</p>
        <p class="text-gray-400"><span class="font-bold text-white">Location:</span> {{ $jobVacancy->location }}</p>
        <p class="text-gray-400"><span class="font-bold text-white">Price:</span> {{ '$'. number_format($jobVacancy->salary) }}/Year</p>
        <p class="text-gray-400"><span class="font-bold text-white">Type:</span> {{ $jobVacancy->type }}</p>
        <p class="text-gray-400"><span class="font-bold text-white">Category:</span> {{ $jobVacancy->jobCategory->name }}</p>

    </div>
  </div>
  
</div>
     </div>
</div>
</x-app-layout>
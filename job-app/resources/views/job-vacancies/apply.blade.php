<x-app-layout>
        <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __($jobVacancy->title) }} - aplly
        </h2>
    </x-slot>
    <div class="py-12">
     <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
    <a href="{{ route('dashboard') }}" class="text-blue-400 hover:underline mb-6 inline-block" >
        &larr; Back to Jobs 
    </a>
    <div class="border-b border-white/10 pb-6">
        <div class="flex items-center justify-between">
            <div>
        <h1 class="text-3xl font-bold text-white">{{ $jobVacancy->title }}</h1>
        <P class="text-md text-gray-400">{{ $jobVacancy->company->name }}</P>
        <div class="flex items-center gap-2">
            <p class="text-sm text-white">Location: {{ $jobVacancy->location }}</p>
            <p class="text-sm text-white">Salary: {{ '$'. number_format($jobVacancy->salary) }}/Year</p>
               <p class="bg-indigo-500 text-withe p-2 rounded-lg">Type: {{ $jobVacancy->type }}</p>
        </div>
        </div>
      
    </div>
     </div>
     <form action="{{ route('job-vacancy.processApllication',$jobVacancy->id) }}" method="POST" class = "space-y-6">
        @csrf
     <!--Resume selection-->
     <div>
        <h3 class="text-xl font-semibold text-white mb-4">Choosre your resume</h3>
     <div class="mb-6">
        <x-input-label for = "resume" value = "select from your existing resume:" />
        <!-- list of resumes -->
         </div>
        </div>

        <!-- Upload New Resumes -->
         <div x-data="{ fileName: '' , hasError: {{ $errors->has('resume_file') ? 'true' : 'false' }} }">
            <x-input-label for="resume" value="Or Upload a new resume:" />
            <div class="flex items-center">
                <div class="flex-1">
                <label for="new_resume_file" class="block text-white cursor-pointer">
                    <div class="border-2 border-dashed border-gray-600 rounded-lg p-4 hover:border-blue-500-transition"
                    :class="{'border-blue-500': fileName, 'border-red-600' : hasError }">
                    <input @change = 'fileName = $event.target.files[0].name' type="file" name="resume_file" id="new_resume_file" class="hidden" accept=".pdf" />
                    <div class="text-center">
                        <template x-if="!fileName">
                            <p class="text-gray-400">Click to upload PDP (MAX 5MB)</p>
                        </template>

                        <template x-if="fileName">
                            <div>
                            <p x-text="fileName" class="mt-2 text-blue-400" ></p>
                            <p class="text-gray-400  text-sm mt-1">Clcik to change file</p>
                       </div>
                        </template>
                    </div>
</div>
                    </div>
                </label>
            </div>
         </div>
         <div> 
            <!--submit Button-->
            <x-primary-button class="w-full">
                Apply Now
            </x-primary-button>
         </div>
     </form>

     </div>
</div>
</x-app-layout>
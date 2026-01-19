<x-app-layout>
 <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Job Application status') }}
        </h2>
    </x-slot>

<div class="overflow-x-auto p-6">
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
<form action="{{ route('job-application.update', ['job_application' => $jobApplication->id, 'redirectTolist' => request('redirectTolist')]) }}" method="POST" >
        
         @csrf
         @method('PUT')


        <!--JobApplication Details-->
        
        <div class="mb-4 p-6 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold mb-4">Job Application Details</h3>
            <span> Enter Job Application status</span>

           <div>
                    <p class="text-gray-700"><strong>Applicant:</strong> {{ $jobApplication->user->name }}</p>
            </div>
                
                <div>
                    <p class="text-gray-700"><strong>Job Vacancy:</strong> {{ $jobApplication->jobVacancy->type }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Company:</strong> {{ $jobApplication->jobVacancy->company->name}}</p>
                </div>
                <div>
                          <p class="text-gray-700"><strong>AIFeedback:</strong> {{ $jobApplication->aiGeneratedFeedback}}</p>
                </div>
                 <div>
                          <p class="text-gray-700"><strong>AIscore:</strong> {{ $jobApplication->aiGeneratedScore}}</p>
                </div>

            <div class="mb-4">
                <label for="status" class="block text-gray-700 font-semibold mb-2">Type:</label>
                <select name="status" id="status" value="{{ old('type',$jobApplication->status)}}"
                class="{{ $errors->has('type') ? 'outline-red-500  outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                
                    <option value="pending">Pending</option>
                   <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                    
                
                
                </select>
                @error('type')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>     
                @enderror
            </div>
           
              


                     <div class="flex justify-end space-x-4">
        <a href="{{ route('job-application.index') }}"
         class="text-gray-500 hover:text-gray-700">Cancel</a>
         
       
            <button type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Edit Job application status</button>
        </div>
    </form>
    
</div>
</div>
</x-app-layout>

  
                
            <!-- Job Description
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-semibold mb-2">Description:</label>
                <textarea name="description" id="description" rows="5"
                    class="{{ $errors->has('description') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter job description">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div> -->



            <!-- Experience Level -->
            <!-- <div class="mb-4">
                <label for="experience_level" class="block text-gray-700 font-semibold mb-2">Experience Level:</label>
                <select name="experience_level" id="experience_level"
                    class="{{ $errors->has('experience_level') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select experience level</option>
                    <option value="Entry" {{ old('experience_level') == 'Entry' ? 'selected' : '' }}>Entry Level</option>
                    <option value="Intermediate" {{ old('experience_level') == 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="Senior" {{ old('experience_level') == 'Senior' ? 'selected' : '' }}>Senior</option>
                    <option value="Expert" {{ old('experience_level') == 'Expert' ? 'selected' : '' }}>Expert</option>
                </select>
                @error('experience_level')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div> -->

            <!-- Application Deadline 
            <div class="mb-4">
                <label for="deadline" class="block text-gray-700 font-semibold mb-2">Application Deadline:</label>
                <input type="date" name="deadline" id="deadline" value="{{ old('deadline') }}"
                    class="{{ $errors->has('deadline') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('deadline')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

             Skills Required 
            <div class="mb-4">
                <label for="skills" class="block text-gray-700 font-semibold mb-2">Required Skills:</label>
                <input type="text" name="skills" id="skills" value="{{ old('skills') }}"
                    class="{{ $errors->has('skills') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., Laravel, PHP, MySQL">
                @error('skills')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div> --> 




                

            
  
        


     

    
<x-app-layout>
 <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Job Vacancy') }}
        </h2>
    </x-slot>

<div class="overflow-x-auto p-6">
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <form action="{{ route('job-vacancy.store') }}" method="POST" >
        
         @csrf


        <!--JobVacancy Details-->
        <div class="mb-4 p-6 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold mb-4">Job Vacancy Details</h3>
            <span> Enter Job vacancy details</span>

               <div class="mb-4">
            <label for="title" class="block text-gray-700 font-semibold mb-2">Titile:</label>
            <input type="text" name="title" id="title" value="{{ old('title') }}"
             class="{{ $errors->has('title') ? 'outline-red-500  outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" >
            @error('title')
                <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
            @enderror
        </div>
            <div class="mb-4">
                <label for="location" class="block text-gray-700 font-semibold mb-2">Location:</label>
                <input type="text" name="location" id="location" value="{{ old('location') }}"
                class="{{ $errors->has('location') ? 'outline-red-500  outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" >
                @error('title')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
                </div>

                <div class="mb-4">
                <label for="salary" class="block text-gray-700 font-semibold mb-2">Expected salary (USD):</label>
                <input type="number" name="salary" id="salary" value="{{ old('salary') }}"
                class="{{ $errors->has('salry') ? 'outline-red-500  outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" >
                @error('salary')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
                </div>

            <div class="mb-4">
                <label for="type" class="block text-gray-700 font-semibold mb-2">Type:</label>
                <select name="type" id="type" value="{{ old('type') }}"
                class="{{ $errors->has('type') ? 'outline-red-500  outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                
                    <option value="Full-time">Full-time</option>
                   <option value="Contract">Contract</option>
                    <option value="Remote">Remote</option>
                    <option value="Hybride">Hybride</option>
                
                
                </select>
                @error('type')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>     
                @enderror
            </div>
           
              
                  

                <!-- company select dropdown -->

                <div class="mb-4">
                    <label for="companyId" class="block text-gray-700 font-semibold mb-2">Company:</label>
                    <select name="companyId" id="companyId"
                        class=" w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        
                        @foreach($companies as $company)
                            <option {{ old('companyId') == $company->id ? 'selected':'' }} value="{{ $company->id }}">
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('companyId')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                   <!-- Job Category select dropdown -->

                <div class="mb-4">
                    <label for="jobCategoryId" class="block text-gray-700 font-semibold mb-2">Job Category</label>
                    <select name="jobCategoryId" id="jobCategoryId"
                        class=" w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        
                        @foreach($categories as $category)
                            <option  {{ old('categoryId') == $company->id ? 'selected':'' }} value="{{ $category->id }}" >
                                {{ $category->name }}      </option>
                        @endforeach
                    </select>
                    @error('categoryId')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                
                 <div class="mb-4">
                <label for="location" class="block text-gray-700 font-semibold mb-2">Description:</label>
                <textarea rows="4" name="description" id="description"
                class=" w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" >
            {{ old('description') }}
                </textarea>
                     @error('description')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                
                </div>
                


                     <div class="flex justify-end space-x-4">
        <a href="{{ route('job-vacancy.index') }}"
         class="text-gray-500 hover:text-gray-700">Cancel</a>
         
       
            <button type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Add JobVacancy</button>
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




                

            
  
        


     

    
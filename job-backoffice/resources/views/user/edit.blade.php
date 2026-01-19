<x-app-layout>
 <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit password user') }}
        </h2>
    </x-slot>

<div class="overflow-x-auto p-6">
    <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
<form action="{{ route('user.update', ['user' => $user->id, 'redirectTolist' => request('redirectTolist')]) }}" method="POST" >
        
         @csrf
         @method('PUT')


        <!--JobApplication Details-->
        
        <div class="mb-4 p-6 bg-gray-100 border border-gray-300 rounded-lg shadow-sm">
            <h3 class="text-lg font-semibold mb-4">user Details</h3>
            

           <div>
                    <p class="text-gray-700"><strong>Name:</strong> {{ $user->name }}</p>
            </div>
                
                <div>
                    <p class="text-gray-700"><strong>Email:</strong> {{ $user->email }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Role:</strong> {{ $user->role}}</p>
                </div>
              

                    <!--owner password-->

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">
                        can change Password:</label>
                    <div class="relative" x-data="{ showPassword: false }"> 
                
            <x-text-input id="password" class="block mt-1 w-full"    name="password"
                            autocomplete="current-password" x-bind:type="showPassword ? 'text' : 'password'"/>
      <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-2 flex items-center text-gray-500">

        <!--eye close icon svg -->
      <svg x-show="!showPassword" class="w-5 h-5" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
      xmlns="http://www.w3.org/2000/svg">
        <path d="M2.99902 3L20.999 21M9.8433 9.91364C9.32066 10.4536 8.99902 11.1892 8.99902 12C8.99902 13.6569 10.3422 15 11.999 15C12.8215 15 13.5667 14.669 14.1086 14.133M6.49902 6.64715C4.59972 7.90034 3.15305 9.78394 2.45703 12C3.73128 16.0571 7.52159 19 11.9992 19C13.9881 19 15.8414 18.4194 17.3988 17.4184M10.999 5.04939C11.328 5.01673 11.6602 5 11.999 5C16.4766 5 20.2669 7.94288 21.5412 12C20.7690 14.1043 19.5194 15.8473 17.9988 16.949M14.2166 14.2171L20.999 21" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>            
        </svg>
            <!-- Eye open icon  SVG -->

        <svg x-show="showPassword" class="w-5 h-5" width="800px" height="800px" viewBox="0 0 24 24" fill="none"
         xmlns="http://www.w3.org/2000/svg">
<path d="M15.0007 12C15.0007 13.6569 13.6576 15 12.0007 15C10.3439 15 9.00073 13.6569 9.00073 12C9.00073 10.3431 10.3439 9 12.0007 9C13.6576 9 15.0007 10.3431 15.0007 12Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M12.0012 5C7.52354 5 3.73326 7.94288 2.45898 12C3.73324 16.0571 7.52354 19 12.0012 19C16.4788 19 20.2691 16.0571 21.5434 12C20.2691 7.94291 16.4788 5 12.0012 5Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
</button>
</div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
</div>

              


                     <div class="flex justify-end space-x-4">
        <a href="{{ route('user.index') }}"
         class="text-gray-500 hover:text-gray-700">Cancel</a>
         
       
            <button type="submit" 
            class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">Edit user password</button>
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




                

            
  
        


     

    
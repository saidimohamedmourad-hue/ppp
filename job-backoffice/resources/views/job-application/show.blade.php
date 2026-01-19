<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $jobApplication->user->name }} Applied to {{ $jobApplication->jobVacancy->title }}
        </h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />
<!-- back button-->
        <div class="mb-4">
            <a href="{{ route('job-application.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                &larr; Back to jobApplication
            </a>
        </div>
      <!--wraper-->

        <div class="bg-white shadow-md rounded-lg p-6">
              <!-- jobVacancies Details -->
            <h3 class="text-lg font-semibold mb-4 ">jobApplication Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!--  jobVacancy details -->
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
                    <p class="text-gray-700"><strong>Status:</strong >  {{ $jobApplication->status }} </p>
                  </div>
                  
                  <div><strong>Rusume:</strong>
                       <a class="text-blue-500 hover:text-blue-700 underline" 
                    href="{{$jobApplication->resume->fileUri }}" target="_blank">{{ $jobApplication->resume->fileUri }} </a>
                  </div>
            </div>
            <!-- archived button-->
           

         <!--edit and archived button-->
            <div class="flex justify-end space-x-4 m-6">
                <a href="{{ route('job-application.edit', ['job_application' => $jobApplication->id, 'redirectTolist' => 'false']) }}"
                 class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Edit jobApplication
                </a>

        <form action="{{ route('job-application.destroy', $jobApplication->id) }}" 
        method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Delete jobApplication
                    </button>
                </form>

</div>
      
 <!-- tabs navigation-->
         <div class="mb-6">
            <ul class="flex space-x-4 ">
                <li>
                <a href="{{ route('job-application.show', ['job_application' => $jobApplication->id, 'tab' => 'resume']) }}" 
                class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'resume' || request('tab') == '' ? 'border-b2 border-blue-900': '' }}"> 
                    Resume
                </a>
                </li>
                <li>
                <a href="{{ route('job-application.show', ['job_application' => $jobApplication->id, 'tab' => 'AIFeedback']) }}" 
                class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'AIFeedback' ? 'border-b2 border-blue-900' : '' }}">
                    AI Feedback
                </a></li>
                </ul>
                </div>
                <!-- tab content-->
                <div>
                
                     <!--resume tab -->
                     <div id="resume" class="{{ request('tab') == 'resume' || request('tab') == '' ? 'block' : 'hidden' }}  ">
                        
                        <table class="min-w-full bg-gray-50 rounded-lg shadow">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Summary</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Skils</th>
                                      <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Education</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Experiemce</th>
                                  
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jobApplication->resume->summary }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jobApplication->resume->skills }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jobApplication->resume->education }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jobApplication->resume->experience }}  </td>
                                </tr>
                                
                                
                            </tbody>

                        </table>
                    </div>

                    <!--IAGFeedback tab-->
                    
                           <div id="AIFeedback" class="{{ request('tab') == 'AIFeedback' ? 'block' : 'hidden' }}  ">                       
                        <table class="min-w-full bg-gray-50 rounded-lg shadow">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">AI Score</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Feedback</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jobApplication->aiGeneratedScore }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jobApplication->aiGeneratedFeedback }}</td>
                                 
            
                                    </td>
                                </tr>
                                
                                
                            </tbody>
                            
                            
                        </table>
                        </div>
                    </div> 
                    
        </div>
    </div>

</x-app-layout>
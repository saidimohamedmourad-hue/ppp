<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('job-applications') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}
        </h2>
    </x-slot>

   <div class="overflow-x-auto p-6">
   <x-toast-notification />

   <div class="flex justify-end items-center space-x-4 mb-4">
    @if (request()->input('archived') == 'true')
    
   <!-- Active-->
        <a href="{{ route('job-application.index') }}" 
        class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
             Active job_vacancies
        </a>

    @else
    <!-- Archived-->
    <a href="{{ route('job-application.index', ['archived' => 'true']) }}"
     class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black">
        Archived job application
    </a>

      @endif

    
</div>
   
   
    
    <!-- Table  -->
    <table class="min-w-full  divide-y border divide-gray-200 rounded-lg shadow-mt-4 bg-white">
        <thead>
            <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applicant Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Position(Job --JobVacancy)</th>
              @if (auth()->user()->role == 'admin')
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Company</th>
                @endif
                  <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">status</th>
                   <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">actions</th>
              
              
            </tr>
            </thead>
            
        <tbody>
            @forelse($jobApplications as $jobApplication)
            <tr class="border-b">
               <td class="px-6 py-4 text-gray-800">
                    @if(request()->input('archived') == 'true')
                    <span class="text-gray-500">{{ $jobApplication->user->name}}</span>
                    @else
                    <a class="text-blue-500 hover:text-blue-700 underline" 
                    href="{{ route('job-application.show', $jobApplication->id) }}">{{ $jobApplication->user->name }}</a>
                    @endif
                </td>
                
                <td class="px-6 py-4 text-gray-800">{{ $jobApplication->jobVacancy->title }}</td>
                 @if (auth()->user()->role == 'admin')
                 <td class="px-6 py-4 text-gray-800">{{ $jobApplication->jobVacancy->company->name ?? 'N/A'}}</td>
                 @endif
              
                       <!-- <td class="px-6 py-4 text-gray-800">{{ $jobApplication->status }}</td> -->
                               <td class="px-6 py-4 text-gray-800">
                        @if($jobApplication->status === 'accepted')
                            <span class="text-green-600 font-semibold">{{ $jobApplication->status }}</span>
                        @elseif($jobApplication->status === 'rejected')
                            <span class="text-red-600 font-semibold">{{ $jobApplication->status }}</span>
                        @else
                            <span class="text-gray-600">{{ $jobApplication->status }}</span>
                        @endif
                    </td>
                                    
                                            
                <td >
                    <div class="flex space-x-4">
                        @if (request()->input('archived') == 'true')
                         <!-- restore button -->
                        <form action="{{ route('job-application.restore', $jobApplication->id) }}"
                         method="POST" class="inline-block">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="text-green-600 hover:text-green-700">♻️Restore</button>
                        </form>
                        
               
                        @else
                                 <!-- edit button -->
                    <a href="{{ route('job-application.edit', $jobApplication->id) }}"
                     class="text-blue-500 hover:text-blue-700 ">✍️Edit</a>
                        <!-- delete button -->

                    <form action="{{ route('job-application.destroy', $jobApplication->id) }}"
                     method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-700">🗃️Archive</button>
                    </form>
                       
                        @endif
                    </div>
                    </td>
                    </tr>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="px-6 py-4 text-center text-gray-500">No job-applications found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div>
        {{ $jobApplications->links() }}
    </div>
   </div>
</x-app-layout>

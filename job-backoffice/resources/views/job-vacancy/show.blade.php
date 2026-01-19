<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $jobVacancy->title }}
        </h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />
<!-- back button-->
        <div class="mb-4">
            <a href="{{ route('job-vacancy.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                &larr; Back to jobVacancies
            </a>
        </div>
      <!--wraper-->

        <div class="bg-white shadow-md rounded-lg p-6">
              <!-- jobVacancies Details -->
            <h3 class="text-lg font-semibold mb-4 ">JobVcancy Informations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!--  jobVacancy details -->
            <div>
                    <p class="text-gray-700"><strong>Company:</strong> {{ $jobVacancy->company->name }}</p>
            </div>
                
                <div>
                    <p class="text-gray-700"><strong>Location:</strong> {{ $jobVacancy->location }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Type:</strong> {{ $jobVacancy->type}}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Salary:</strong> ${{ number_format($jobVacancy->salary, 2) }}</p>
                  </div>
                  <div>
                    <p class="text-gray-700"><strong>Description:</strong> {{ $jobVacancy->description }}</p>
                  </div>
            </div>
            <!--edit and archived button-->
            <div class="flex justify-end space-x-4 m-6">
                <a href="{{ route('job-vacancy.edit', ['job_vacancy' => $jobVacancy->id, 'redirectTolist' => 'false']) }}"
                 class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Edit Company
                </a>

        <form action="{{ route('job-vacancy.destroy', $jobVacancy->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Delete jobVacancy
                    </button>
                </form>

</div>
        <!-- tabs navigation-->
         <div class="mb-6">
            <ul class="flex space-x-4 ">
               
                <li>
                <a href="{{ route('job-vacancy.show', ['job_vacancy' => $jobVacancy->id, 'tab' => 'applications']) }}" 
                class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'applications' || request('tab') == '' ? 'border-b2 border-blue-700' : '' }}">
                    Application
                </a></li>
                </ul>
                </div>
                <!-- tab content-->
                <div>
                
                     <!-- applications table-->
                     <div id="applications" class="{{ request('tab') == 'applications' || request('tab') == '' ? 'block' : 'hidden' }}  ">
                        
                        <table class="min-w-full bg-gray-50 rounded-lg shadow">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applicant Name</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Job Title</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($jobVacancy->jobApplications as $application)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $application->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $application->jobVacancy->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $application->status }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <a href="{{ route('job-application.show', $application->id) }}" class="text-blue-500 hover:text-blue-700">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-2 px-4 text-center">No applications </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
        </div>
    </div>

</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $company->name }}
        </h2>
    </x-slot>
    <div class="overflow-x-auto p-6">
        <x-toast-notification />
<!-- back button-->
 
            @if (auth()->user()->role == 'admin') 
        <div class="mb-4">
            <a href="{{ route('company.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                &larr; Back to Companies
            </a>
        </div>
        @endif
      <!--wraper-->

        <div class="bg-white shadow-md rounded-lg p-6">
              <!-- Company Details -->
            <h3 class="text-lg font-semibold mb-4 ">Company Informations</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- owner name-->
            <div>
                    <p class="text-gray-700"><strong>Owner Name:</strong> {{ $company->owner->name }}</p>
            </div>
                 <div>
                    <p class="text-gray-700"><strong>Email:</strong> {{ $company->owner->email }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Address:</strong> {{ $company->address }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Industry:</strong> {{ $company->industry }}</p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Website:</strong> <a class="text-blue-500 hover:text-blue-700 underline"
                     href="{{ $company->website }}" target="_blank">{{ $company->website }}</a></p>
                </div>
                <div>
                    <p class="text-gray-700"><strong>Téléphone:</strong>
                        @if($company->phone)
                            <a class="text-blue-500 hover:text-blue-700 underline" href="tel:{{ $company->phone }}">{{ $company->phone }}</a>
                        @else
                            <span class="text-gray-400">non renseigné</span>
                        @endif
                    </p>
                </div>
            </div>
            <!-- Analytics par offre (vues, candidatures, acceptées) -->
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-3">Analytics par offre</h3>
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-blue-700">{{ $analyticsTotals['views'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500 uppercase">Vues totales</div>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-gray-800">{{ $analyticsTotals['apps'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500 uppercase">Candidatures</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <div class="text-2xl font-bold text-green-700">{{ $analyticsTotals['accepted'] ?? 0 }}</div>
                        <div class="text-xs text-gray-500 uppercase">Acceptées</div>
                    </div>
                </div>
                <table class="min-w-full bg-gray-50 rounded-lg shadow text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-gray-900">Offre</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-900">Vues</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-900">Candidatures</th>
                            <th class="px-4 py-2 text-right font-semibold text-gray-900">Acceptées</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($jobAnalytics ?? collect()) as $job)
                        <tr class="border-t">
                            <td class="px-4 py-2 text-gray-900">{{ $job->title }}</td>
                            <td class="px-4 py-2 text-right text-gray-600">{{ $job->viewCount ?? 0 }}</td>
                            <td class="px-4 py-2 text-right text-gray-900 font-semibold">{{ $job->totalCount ?? 0 }}</td>
                            <td class="px-4 py-2 text-right text-green-700 font-semibold">{{ $job->acceptedCount ?? 0 }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-3 text-center text-gray-500">Aucune offre publiée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!--edit and archived button-->


            <div class="flex justify-end space-x-4 m-6">
                 @if (auth()->user()->role == 'admin') 
                <a href="{{ route('company.edit', ['company' => $company->id, 'redirectTolist' => 'false']) }}"
                 class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Edit Company
                </a>
                @else
                  <a href="{{ route('my-company.edit',) }}"
                 class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                    Edit 
                </a>
                @endif

        
            @if (auth()->user()->role == 'admin')    
            <form action="{{ route('company.destroy', $company->id) }}" method="POST" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Delete Company
                    </button>
                </form>
            @endif
</div>
        <!-- tabs navigation-->
         
            @if (auth()->user()->role == 'admin') 
         <div class="mb-6">
            <ul class="flex space-x-4 ">
                <li>
                <a href="{{ route('company.show', ['company' => $company->id, 'tab' => 'jobs']) }}" 
                class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'jobs' || request('tab') == '' ? 'border-b2 border-blue-700': '' }}"> 
                    Jobs
                </a>
                </li>
                <li>
                <a href="{{ route('company.show', ['company' => $company->id, 'tab' => 'applications']) }}" 
                class="text-gray-600 hover:text-gray-800 font-medium border-b-2 border-transparent hover:border-gray-300 pb-2 {{ request('tab') == 'applications' ? 'border-b2 border-blue-700' : '' }}">
                    Application
                </a></li>
                </ul>
                </div>
                <!-- tab content-->
                <div>
                    <!-- jobs table-->
                     <div id="jobs" class="{{ request('tab') == 'jobs' || request('tab') == '' ? 'block' : 'hidden' }}  ">
                        
                        <table class="min-w-full bg-gray-50 rounded-lg shadow">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Job Title</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Location</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($company->jobVacancies as $job)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $job->title }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $job->type }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $job->location }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <a href="{{ route('job-vacancy.show', $job->id) }}" class="text-blue-500 hover:text-blue-700">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-2 px-4 text-center">No jobs </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                     </div>
                     <!-- applications table-->
                     <div id="applications" class="{{ request('tab') == 'applications' ? 'block' : 'hidden' }}  ">
                        
                        <table class="min-w-full bg-gray-50 rounded-lg shadow">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Applicant Name</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Téléphone</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Job Title</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($company->jobapplications as $application)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $application->user->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        @if($application->user?->phone)
                                            <a class="text-blue-500 hover:text-blue-700 underline" href="tel:{{ $application->user->phone }}">{{ $application->user->phone }}</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $application->jobVacancy?->title ?? __('Offre supprimée') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $application->status }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <a href="{{ route('job-application.show', $application->id) }}" class="text-blue-500 hover:text-blue-700">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
        </div>
    </div>

</x-app-layout>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 px-6 flex flex-col gap-4">
        
        <!--overview cards -->
        
            <div class="grid grid-cols-3 gap-4">
                <!-- Card 1 -->
                <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-lg font-medium text-gray-900">Active Users</div>
                        <div class="mt-2 text-3xl font-bold text-indigo-600">{{ $analytics['activeUsers'] }}</div>
                           <div class="text-sm  text-gray-500">Last 30 days </div>
                    </div>
                </div>

               
                <!-- Card 2 -->
                <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="text-lg font-medium text-gray-900">Total Jobs</div>
                        <div class=" text-3xl font-bold text-indigo-600">{{ $analytics['totalJobs'] }}</div>
                           <div class="text-sm  text-gray-500">All time</div>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="p-6 bg-white overflow-hidden shadow-sm rounded-lg">
                     <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900">Total application</h3>
                        <p class=" text-3xl font-bold text-indigo-600">{{ $analytics['totalApplications'] }}</p>
                           <p class="text-sm  text-gray-500">All time </p>
                    </div>
                </div>
</div>
                
                   <!-- Most applied Jobs -->
                <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <h3 class="text-lg font-medium text-gray-900">Most applied Jobs</h3>
                     <div>
                        <table class="w-full divide-y divide-gay-200">
                            <thead>
                                <tr class="text-left">
                                <th class="py-2 uppercase text-gray-500">Job Title</th>
                                @if (auth()->user()->role == 'admin')
                                
                              
                                <th class="py-2 uppercase text-gray-500">company</th>
                                  @endif
                                <th class="py-2 uppercase text-gray-500">Total application</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                               
                             @foreach ($analytics['mostAppliedJobs'] as $mostAppliedJob )
                             <tr>
                                <td class="py-4">{{ $mostAppliedJob->title }}</td>
                                   @if (auth()->user()->role == 'admin')
                                <td class="py-4">{{ $mostAppliedJob->company->name }}</td>
                                @endif
                                <td class="py-4">{{ $mostAppliedJob->totalCount }}</td>
                             </tr>
                             
                             @endforeach
                            </tbody>
                        </table>
                     </div>
                </div>
                
            
               <!--conversion Rates-->
                    <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <h3 class="text-lg font-medium text-gray-900">Conversion Rates</h3>
                     <div>
                        <table class="w-full divide-y divide-gay-200">
                            <thead>
                                <tr class="text-left">
                                <th class="py-2 uppercase text-gray-500">Job Title</th>
                                <th class="py-2 uppercase text-gray-500">Views</th>
                                <th class="py-2 uppercase text-gray-500">Applications</th>
                                <th class="py-2 uppercase text-gray-500">Conversion Rates</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                               @foreach ($analytics['conversionRates'] as $conversionRate )
                               <tr>
                                <td class="py-4">{{ $conversionRate->title }}</td>
                                <td class="py-4">{{ $conversionRate->viewCount }}</td>
                                <td class="py-4">{{ $conversionRate->totalCount }}</td>
                                <td class="py-4">{{ $conversionRate->conversionRate }}%</td>
                               </tr>
                               
                               @endforeach
                            </tbody>
                        </table>
                     </div>
                </div>
                
            </div>
            
    </div>
</x-app-layout>

@props(['trainingAnalytics'])

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="text-lg font-medium text-gray-900">Active applicants</div>
            <div class="mt-2 text-3xl font-bold text-teal-600">{{ $trainingAnalytics['activeApplicants'] }}</div>
            <div class="text-sm text-gray-500">Job seekers (login last 30 days) with training applications in scope</div>
        </div>
    </div>
    <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="text-lg font-medium text-gray-900">Training sessions</div>
            <div class="mt-2 text-3xl font-bold text-teal-600">{{ $trainingAnalytics['totalSessions'] }}</div>
            <div class="text-sm text-gray-500">Active sessions in scope</div>
        </div>
    </div>
    <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <h3 class="text-lg font-medium text-gray-900">Training applications</h3>
            <p class="text-3xl font-bold text-teal-600">{{ $trainingAnalytics['totalApplications'] }}</p>
            <p class="text-sm text-gray-500">All time in scope</p>
        </div>
    </div>
    @if (($trainingAnalytics['totalSchools'] ?? null) !== null)
        <div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg sm:col-span-1">
            <div class="p-6 text-gray-900">
                <h3 class="text-lg font-medium text-gray-900">Schools</h3>
                <p class="text-3xl font-bold text-teal-600">{{ $trainingAnalytics['totalSchools'] }}</p>
                <p class="text-sm text-gray-500">Registered schools</p>
            </div>
        </div>
    @endif
</div>

<div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <h3 class="text-lg font-medium text-gray-900 mb-2">Most applied training sessions</h3>
    <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left">
                    <th class="py-2 uppercase text-gray-500 text-sm">Session</th>
                    @if (!empty($trainingAnalytics['includeSchoolColumn']))
                        <th class="py-2 uppercase text-gray-500 text-sm">School</th>
                    @endif
                    <th class="py-2 uppercase text-gray-500 text-sm">Applications</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($trainingAnalytics['mostAppliedSessions'] as $session)
                    <tr>
                        <td class="py-4">{{ $session->title }}</td>
                        @if (!empty($trainingAnalytics['includeSchoolColumn']))
                            <td class="py-4">{{ $session->school->name ?? '—' }}</td>
                        @endif
                        <td class="py-4">{{ $session->totalCount }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ !empty($trainingAnalytics['includeSchoolColumn']) ? 3 : 2 }}" class="py-4 text-gray-500">No sessions in scope.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="p-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <h3 class="text-lg font-medium text-gray-900 mb-2">Session conversion (views to applications)</h3>
    <div class="overflow-x-auto">
        <table class="w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left">
                    <th class="py-2 uppercase text-gray-500 text-sm">Session</th>
                    <th class="py-2 uppercase text-gray-500 text-sm">Views</th>
                    <th class="py-2 uppercase text-gray-500 text-sm">Applications</th>
                    <th class="py-2 uppercase text-gray-500 text-sm">Rate</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($trainingAnalytics['conversionRates'] as $session)
                    <tr>
                        <td class="py-4">{{ $session->title }}</td>
                        <td class="py-4">{{ $session->viewCount }}</td>
                        <td class="py-4">{{ $session->totalCount }}</td>
                        <td class="py-4">{{ $session->conversionRate }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-gray-500">No conversion data in scope.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

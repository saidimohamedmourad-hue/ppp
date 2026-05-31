<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $school->name }}</h2></x-slot>
    <div class="overflow-x-auto p-6">
        <div class="mb-4"><a href="{{ route('school.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md">&larr; Back</a></div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p><strong>Name:</strong> {{ $school->name }}</p>
            <p><strong>Industry:</strong> {{ $school->industry }}</p>
            <p><strong>Address:</strong> {{ $school->address }}</p>
            <p><strong>Website:</strong> {{ $school->website ?? 'N/A' }}</p>
            <p><strong>Téléphone:</strong>
                @if($school->phone)
                    <a class="text-blue-500 underline" href="tel:{{ $school->phone }}">{{ $school->phone }}</a>
                @else
                    <span class="text-gray-400">non renseigné</span>
                @endif
            </p>
            <p><strong>Description:</strong> {{ $school->description ?? 'N/A' }}</p>
            <p><strong>Owner:</strong> {{ $school->owner->name ?? 'N/A' }}</p>
        </div>
    </div>
</x-app-layout>

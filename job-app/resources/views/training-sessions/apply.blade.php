<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __($trainingSession->title) }} - Apply
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="bg-black shadow-lg rounded-lg p-6 max-w-7xl mx-auto">
            <a href="{{ route('training-sessions.index') }}" class="text-blue-400 hover:underline mb-6 inline-block">
                &larr; Back to Formations
            </a>
            <div class="border-b border-white/10 pb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white">{{ $trainingSession->title }}</h1>
                        <p class="text-md text-gray-400">{{ optional($trainingSession->school)->name }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <p class="text-sm text-white">Location: {{ $trainingSession->location }}</p>
                            @if ($trainingSession->trainingDate)
                                <p class="text-sm text-white">
                                    Date: {{ \Carbon\Carbon::parse($trainingSession->trainingDate)->format('M d, Y') }}
                                </p>
                            @endif
                            <p class="bg-indigo-500 text-white p-2 rounded-lg">
                                {{ optional($trainingSession->trainingCategory)->name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('training-sessions.processApplication', $trainingSession->id) }}"
                method="POST" class="space-y-6" enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Whoops!</strong>
                        <span class="block sm:inline">There were some problems with your input.</span>
                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <h3 class="text-xl font-semibold text-white mb-4">Choose your resume</h3>
                    <div class="mb-6">
                        <x-input-label for="resume" value="Select from your existing resume:" />
                        <div class="space-y-4">
                            @forelse ($resumes as $resume)
                                <div class="flex items-center gap-2">
                                    <input type="radio" name="resume_option" id="{{ $resume->id }}"
                                        value="{{ $resume->id }}"
                                        @error('resume_option') class="border-red-500" @else class="border-gray-600" @enderror />
                                    <x-input-label for="existing_{{ $resume->id }}" class="text-white cursor-pointer">
                                        {{ $resume->filename }}
                                        <span class="block text-sm text-gray-400">
                                            Uploaded on {{ $resume->created_at->format('M j, Y') }}
                                        </span>
                                    </x-input-label>
                                </div>
                            @empty
                                <p class="text-gray-400">
                                    You have no resumes uploaded. Please upload a new resume to apply.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div x-data="{ fileName: '', hasError: {{ $errors->has('resume_file') ? 'true' : 'false' }} }">
                        <div class="flex items-center gap-2">
                            <input x-ref="newResumeRadio" type="radio" name="resume_option" id="new_resume"
                                value="new_resume"
                                @error('resume_option') class="border-red-500" @else class="border-gray-600" @enderror />
                            <x-input-label class="text-white cursor-pointer" for="new_resume"
                                value="Upload a new resume:" />
                        </div>
                        <div class="flex items-center">
                            <div class="flex-1">
                                <label for="new_resume_file" class="block text-white cursor-pointer">
                                    <div class="border-2 border-dashed border-gray-600 rounded-lg p-4 hover:border-blue-500 transition"
                                        :class="{ 'border-blue-500': fileName, 'border-red-600': hasError }">
                                        <input
                                            @change="fileName = $event.target.files[0].name; $refs.newResumeRadio.checked = true"
                                            type="file" name="resume_file" id="new_resume_file" class="hidden"
                                            accept=".pdf" />
                                        <div class="text-center">
                                            <template x-if="!fileName">
                                                <p class="text-gray-400">Click to upload PDF (MAX 2MB)</p>
                                            </template>
                                            <template x-if="fileName">
                                                <div>
                                                    <p x-text="fileName" class="mt-2 text-blue-400"></p>
                                                    <p class="text-gray-400 text-sm mt-1">Click to change file</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <x-primary-button class="w-full">
                            Apply Now
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

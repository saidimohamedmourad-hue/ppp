<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajouter une entreprise') }}
        </h2>
    </x-slot>

    <div class="overflow-x-auto p-6">
        <div class="max-w-2xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <form action="{{ route('company.store') }}" method="POST">
                @csrf

                {{-- Company Details --}}
                <div class="mb-6 p-6 bg-gray-50 border border-gray-200 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Informations de l'entreprise</h3>

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-semibold mb-2">Nom de l'entreprise *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                            class="{{ $errors->has('name') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-semibold mb-2">Adresse *</label>
                        <input type="text" name="address" id="address" value="{{ old('address') }}"
                            class="{{ $errors->has('address') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="industry" class="block text-gray-700 font-semibold mb-2">Secteur d'activité *</label>
                        <select name="industry" id="industry"
                            class="{{ $errors->has('industry') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($industries as $industry)
                                <option value="{{ $industry }}" @selected(old('industry') === $industry)>{{ $industry }}</option>
                            @endforeach
                        </select>
                        @error('industry')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="website" class="block text-gray-700 font-semibold mb-2">Site web <span class="text-gray-500 font-normal">(optionnel, avec https://)</span></label>
                        <input type="url" name="website" id="website" value="{{ old('website') }}"
                            class="{{ $errors->has('website') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('website')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="phone" class="block text-gray-700 font-semibold mb-2">Téléphone de contact <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="+213 555 123 456" required
                            class="{{ $errors->has('phone') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-gray-500 text-xs mt-1">Ce numéro sera affiché aux candidats sur chaque offre.</p>
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Owner Section with Alpine.js toggle --}}
                <div class="mb-6 p-6 bg-gray-50 border border-gray-200 rounded-lg"
                    x-data="{ mode: '{{ old('owner_mode', 'new') }}' }">

                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Propriétaire du compte</h3>

                    {{-- Toggle --}}
                    <div class="flex gap-6 mb-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="owner_mode" value="new" x-model="mode"
                                class="w-4 h-4 text-blue-600">
                            <span class="text-gray-700 font-medium">Créer un nouveau compte</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="owner_mode" value="existing" x-model="mode"
                                class="w-4 h-4 text-blue-600">
                            <span class="text-gray-700 font-medium">
                                Lier à un compte existant
                                @if($unlinkedOwners->isEmpty())
                                    <span class="text-xs text-gray-400">(aucun disponible)</span>
                                @else
                                    <span class="text-xs text-gray-500">({{ $unlinkedOwners->count() }} disponible(s))</span>
                                @endif
                            </span>
                        </label>
                    </div>

                    @error('owner_mode')
                        <p class="text-red-500 text-sm mb-3">{{ $message }}</p>
                    @enderror

                    {{-- Existing user dropdown --}}
                    <div x-show="mode === 'existing'" x-cloak>
                        <label for="owner_id" class="block text-gray-700 font-semibold mb-2">
                            Utilisateur company-owner sans entreprise *
                        </label>
                        @if($unlinkedOwners->isEmpty())
                            <p class="text-orange-500 text-sm p-3 bg-orange-50 border border-orange-200 rounded-md">
                                Aucun utilisateur avec le rôle <strong>company-owner</strong> sans entreprise associée n'est disponible.
                                Utilisez le mode "Créer un nouveau compte" ou invitez d'abord l'utilisateur à s'inscrire.
                            </p>
                        @else
                            <select name="owner_id" id="owner_id"
                                class="{{ $errors->has('owner_id') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Sélectionnez un utilisateur --</option>
                                @foreach($unlinkedOwners as $owner)
                                    <option value="{{ $owner->id }}" @selected(old('owner_id') === $owner->id)>
                                        {{ $owner->name }} — {{ $owner->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('owner_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>

                    {{-- New owner fields --}}
                    <div x-show="mode === 'new'" x-cloak>
                        <div class="mb-4">
                            <label for="owner_name" class="block text-gray-700 font-semibold mb-2">Nom *</label>
                            <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name') }}"
                                class="{{ $errors->has('owner_name') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('owner_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="owner_email" class="block text-gray-700 font-semibold mb-2">Email *</label>
                            <input type="email" name="owner_email" id="owner_email" value="{{ old('owner_email') }}"
                                class="{{ $errors->has('owner_email') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('owner_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="owner_password" class="block text-gray-700 font-semibold mb-2">Mot de passe * <span class="text-gray-500 font-normal">(min. 8 caractères)</span></label>
                            <div class="relative" x-data="{ show: false }">
                                <input id="owner_password" name="owner_password"
                                    :type="show ? 'text' : 'password'"
                                    class="{{ $errors->has('owner_password') ? 'outline-red-500 outline outline-1' : '' }} w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10">
                                <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-2 flex items-center text-gray-500 text-sm px-1">
                                    <span x-text="show ? 'Cacher' : 'Voir'"></span>
                                </button>
                            </div>
                            @error('owner_password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('company.index') }}" class="text-gray-500 hover:text-gray-700 py-2">Annuler</a>
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Créer l'entreprise
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

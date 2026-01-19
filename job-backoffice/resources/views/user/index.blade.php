<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('users') }} {{ request()->input('archived') == 'true' ? '(archived)' : '' }}
        </h2>
    </x-slot>

   <div class="overflow-x-auto p-6">
   <x-toast-notification />

   <div class="flex justify-end items-center space-x-4 mb-4">
    @if (request()->input('archived') == 'true')
    
   <!-- Active-->
        <a href="{{ route('user.index') }}" 
        class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
             Active users
        </a>

    @else
    <!-- Archived-->
    <a href="{{ route('user.index', ['archived' => 'true']) }}"
     class="bg-black text-white px-4 py-2 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-black">
        Archived user
    </a>

      @endif

    
</div>
   
   
    
    <!-- Table  -->
    <table class="min-w-full  divide-y border divide-gray-200 rounded-lg shadow-mt-4 bg-white">
        <thead>
            <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900"> Name</th>
              <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Role</th>
           
              
              
            </tr>
            </thead>
            
        <tbody>
            @forelse($users as $user)
            <tr class="border-b">
               <td class="px-6 py-4 text-gray-800">
                
                    <span class="text-gray-500">{{ $user->name}}</span>
                    
                   
                </td>
                
                <td class="px-6 py-4 text-gray-800">{{ $user->email }}</td>
                <td class="px-6 py-4 text-gray-800">{{ $user->role }}</td>
                
                
              
                     
                                    
                                            
                <td >
                    <div class="flex space-x-4">
                        @if (request()->input('archived') == 'true')
                         <!-- restore button -->
                        <form action="{{ route('user.restore', $user->id) }}"
                         method="POST" class="inline-block">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="text-green-600 hover:text-green-700">♻️Restore</button>
                        </form>
                        
               
                        @else
                        <!-- if admin dont allow delete-->
                          @if($user->role !== 'admin')

                                 <!-- edit button -->
                    <a href="{{ route('user.edit', $user->id) }}"
                     class="text-blue-500 hover:text-blue-700 ">✍️Edit</a>
                        <!-- delete button -->

                    <form action="{{ route('user.destroy', $user->id) }}"
                     method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-700">🗃️Archive</button>
                    </form>
                   @endif
                        @endif
                    </div>
                    </td>
                    </tr>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="px-6 py-4 text-center text-gray-500">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div>
        {{ $users->links() }}
    </div>
   </div>
</x-app-layout>

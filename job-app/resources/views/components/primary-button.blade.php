<button {{ $attributes->merge(['type' => 'submit', 'class' => 'justify-center bg-gradient-to-r from-indigo-500 to-rose-500 text-white rounded-lg px-4 py-2']) }}>
    {{ $slot }}
</button>

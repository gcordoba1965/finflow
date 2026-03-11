@props(['active' => false])
<a {{ $attributes }}
   class="{{ $active
       ? 'bg-red-500/10 text-red-400 border-l-2 border-red-500'
       : 'text-gray-500 hover:bg-gray-800 hover:text-gray-300' }}
      flex items-center gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold transition-colors mb-0.5">
    {{ $slot }}
</a>

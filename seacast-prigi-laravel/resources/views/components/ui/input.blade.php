@props([
    'label' => null,
    'error' => null,
    'help' => null,
    'id' => null,
    'type' => 'text',
])

@php
$id = $id ?? 'input-' . md5(uniqid());
@endphp

<div class="space-y-1.5 w-full">
    @if($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
            {{ $label }}
        </label>
    @endif

    <input 
        id="{{ $id }}" 
        type="{{ $type }}" 
        {{ $attributes->merge(['class' => 'w-full px-3.5 py-2 text-xs rounded-xl border bg-white dark:bg-[#071B2B] text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 transition-colors ' . ($error ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/30' : 'border-slate-300 dark:border-[#244456] focus:border-cyan-500')]) }}
    />

    @if($error)
        <p class="text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $error }}</p>
    @elseif($help)
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $help }}</p>
    @endif
</div>


@props([
    'variant' => 'default',
    'dot' => false,
    'pulse' => false,
])

@php
$variantStyles = [
    'default' => 'bg-[#E7F0F5] dark:bg-[#12364A] text-[#102A3A] dark:text-[#E7F0F5] border-[#DCE6EC] dark:border-[#244456]',
    'cyan' => 'bg-cyan-50 dark:bg-cyan-950/40 text-cyan-800 dark:text-cyan-300 border-cyan-200 dark:border-cyan-500/30',
    'emerald' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border-emerald-200 dark:border-emerald-500/30',
    'amber' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border-amber-200 dark:border-amber-500/30',
    'rose' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-300 border-rose-200 dark:border-rose-500/30',
    'indigo' => 'bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/30',
    'slate' => 'bg-slate-100 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700',
    'z1' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-800 dark:text-sky-300 border-sky-300 dark:border-sky-500/40',
    'z2' => 'bg-blue-50 dark:bg-blue-950/50 text-blue-800 dark:text-blue-300 border-blue-300 dark:border-blue-500/40',
    'z3' => 'bg-cyan-50 dark:bg-cyan-950/50 text-cyan-800 dark:text-cyan-300 border-cyan-300 dark:border-cyan-500/40',
    'z4' => 'bg-purple-50 dark:bg-purple-950/50 text-purple-800 dark:text-purple-300 border-purple-300 dark:border-purple-500/40',
    'z5' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-800 dark:text-rose-300 border-rose-300 dark:border-rose-500/40',
    'gen' => 'bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700',
];

$dotColors = [
    'default' => 'bg-slate-400 dark:bg-slate-300',
    'cyan' => 'bg-cyan-500 dark:bg-cyan-400',
    'emerald' => 'bg-emerald-500 dark:bg-emerald-400',
    'amber' => 'bg-amber-500 dark:bg-amber-400',
    'rose' => 'bg-rose-500 dark:bg-rose-400',
    'indigo' => 'bg-indigo-500 dark:bg-indigo-400',
    'slate' => 'bg-slate-500 dark:bg-slate-400',
    'z1' => 'bg-[#38BDF8]',
    'z2' => 'bg-[#2563EB]',
    'z3' => 'bg-[#06B6D4]',
    'z4' => 'bg-[#A855F7]',
    'z5' => 'bg-[#F43F5E]',
    'gen' => 'bg-[#64748B]',
];

$style = $variantStyles[$variant] ?? $variantStyles['default'];
$dotColor = $dotColors[$variant] ?? $dotColors['default'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full border text-xs font-semibold tracking-wide transition-all ' . $style]) }}>
    @if($dot)
        <span class="relative flex h-2 w-2">
            @if($pulse)
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $dotColor }}"></span>
            @endif
            <span class="relative inline-flex rounded-full h-2 w-2 {{ $dotColor }}"></span>
        </span>
    @endif
    {{ $slot }}
</span>


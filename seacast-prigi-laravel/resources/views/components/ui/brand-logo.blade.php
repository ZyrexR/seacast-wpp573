@props([
    'variant' => 'full',       // 'full', 'horizontal', 'symbol', 'mark', 'hero'
    'theme' => 'auto',         // 'auto', 'light', 'dark'
    'size' => 'md',            // 'xs', 'sm', 'md', 'lg', 'xl', '2xl', 'hero'
    'withLink' => false,
    'withText' => true,        // Displays SEACAST + Full Tagline beside the logo
    'href' => null,
    'badge' => null,
    'withBadge' => false,
    'subtext' => null,
    'tagline' => true,
    'maxWidth' => null,
])

@php
    $linkUrl = $href ?? route('home');

    // Sizing scales for the complete, uncut master logo
    $logoSizes = [
        'xs' => 'h-7 sm:h-8',
        'sm' => 'h-9 sm:h-10 md:h-11',
        'md' => 'h-11 sm:h-12 md:h-[48px]',
        'lg' => 'h-14 sm:h-16 md:h-20',
        'xl' => 'h-20 sm:h-24 md:h-28',
        '2xl' => 'h-28 sm:h-32 md:h-36',
        'hero' => 'h-28 sm:h-36 md:h-44 w-auto max-w-full',
    ];

    // For icon/emblem only (favicons, tiny avatars)
    $symbolSizes = [
        'xs' => 'h-6 w-auto',
        'sm' => 'h-8 w-auto',
        'md' => 'h-10 w-auto',
        'lg' => 'h-14 w-auto',
        'xl' => 'h-20 w-auto',
        '2xl' => 'h-28 w-auto',
    ];

    $pixelHeights = [
        'xs' => '32px',
        'sm' => '42px',
        'md' => '48px',
        'lg' => '62px',
        'xl' => '80px',
        '2xl' => '112px',
        'hero' => '140px',
    ];
    $pixelHeight = $pixelHeights[$size] ?? '48px';

    $logoClass = $logoSizes[$size] ?? $logoSizes['md'];
    $symClass = $symbolSizes[$size] ?? $symbolSizes['md'];

    if ($maxWidth) {
        $logoClass .= ' ' . $maxWidth;
    }
@endphp

@if($withLink)
    <a href="{{ $linkUrl }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 sm:gap-3.5 group focus:outline-none transition-transform hover:opacity-95']) }}>
@else
    <div {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 sm:gap-3.5 select-none']) }}>
@endif

    {{-- =========================================================================
         1. SYMBOL ONLY (Only if explicitly requested for tiny badges / icons)
         ========================================================================= --}}
    @if($variant === 'symbol' || $variant === 'mark')
        <div class="relative shrink-0 {{ $symClass }} flex items-center justify-center">
            @if($theme === 'light')
                <img 
                    src="{{ asset('assets/branding/seacast-symbol-light.png') }}" 
                    alt="SEACAST" 
                    class="h-full w-auto object-contain filter drop-shadow-sm group-hover:scale-105 transition-transform duration-200" 
                    loading="eager" 
                />
            @elseif($theme === 'dark')
                <img 
                    src="{{ asset('assets/branding/seacast-symbol-dark.png') }}" 
                    alt="SEACAST" 
                    class="h-full w-auto object-contain filter drop-shadow-sm group-hover:scale-105 transition-transform duration-200" 
                    loading="eager" 
                />
            @else
                <img 
                    src="{{ asset('assets/branding/seacast-symbol-light.png') }}" 
                    alt="SEACAST" 
                    class="h-full w-auto object-contain dark:hidden filter drop-shadow-sm group-hover:scale-105 transition-transform duration-200" 
                    loading="eager" 
                />
                <img 
                    src="{{ asset('assets/branding/seacast-symbol-dark.png') }}" 
                    alt="SEACAST" 
                    class="h-full w-auto object-contain hidden dark:block filter drop-shadow-sm group-hover:scale-105 transition-transform duration-200" 
                    loading="eager" 
                />
            @endif
        </div>

    {{-- =========================================================================
         2. MASTER FULL LOGO + BRAND IDENTITY TEXT BLOCK
         SEACAST (Primary bold brand) + Scientific Ocean Intelligence & Fisheries Decision Support
         ========================================================================= --}}
    @else
        {{-- Authentic uncut logo image --}}
        <div class="relative shrink-0 flex items-center">
            @if($theme === 'light')
                <img 
                    src="{{ asset('assets/branding/seacast-logo-light.png') }}" 
                    alt="SEACAST" 
                    class="{{ $logoClass }} w-auto object-contain filter drop-shadow-sm group-hover:opacity-95 transition-opacity"
                    style="height: {{ $pixelHeight }}; width: auto; max-height: 100%;"
                    loading="eager"
                />
            @elseif($theme === 'dark')
                <img 
                    src="{{ asset('assets/branding/seacast-logo-dark.png') }}" 
                    alt="SEACAST" 
                    class="{{ $logoClass }} w-auto object-contain filter drop-shadow-md group-hover:opacity-95 transition-opacity"
                    style="height: {{ $pixelHeight }}; width: auto; max-height: 100%;"
                    loading="eager"
                />
            @else
                <img 
                    src="{{ asset('assets/branding/seacast-logo-light.png') }}" 
                    alt="SEACAST" 
                    class="{{ $logoClass }} w-auto object-contain dark:hidden filter drop-shadow-sm group-hover:opacity-95 transition-opacity"
                    style="height: {{ $pixelHeight }}; width: auto; max-height: 100%;"
                    loading="eager"
                />
                <img 
                    src="{{ asset('assets/branding/seacast-logo-dark.png') }}" 
                    alt="SEACAST" 
                    class="{{ $logoClass }} w-auto object-contain hidden dark:block filter drop-shadow-md group-hover:opacity-95 transition-opacity"
                    style="height: {{ $pixelHeight }}; width: auto; max-height: 100%;"
                    loading="eager"
                />
            @endif
        </div>

        {{-- Official brand identity text block beside logo --}}
        @if($withText)
            <div class="flex flex-col justify-center leading-none">
                <span class="font-black tracking-tight text-lg sm:text-xl lg:text-2xl text-slate-900 dark:text-white uppercase font-sans">
                    SEACAST
                </span>
                <span class="hidden sm:inline-block text-[11px] lg:text-xs font-medium text-slate-500 dark:text-slate-400 tracking-tight mt-1 max-w-[280px] lg:max-w-none line-clamp-1">
                    {{ $subtext ?? 'Scientific Ocean Intelligence & Fisheries Decision Support' }}
                </span>
                <span class="sm:hidden text-[10px] font-medium text-slate-500 dark:text-slate-400 tracking-tight mt-0.5">
                    Ocean Intelligence & Fisheries Support
                </span>
            </div>
        @endif

        {{-- Optional companion badge if requested --}}
        @if($withBadge || $badge)
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-100 dark:bg-cyan-950/80 text-cyan-800 dark:text-cyan-300 border border-cyan-300 dark:border-cyan-800 shrink-0">
                {{ $badge }}
            </span>
        @endif
    @endif

@if($withLink)
    </a>
@else
    </div>
@endif

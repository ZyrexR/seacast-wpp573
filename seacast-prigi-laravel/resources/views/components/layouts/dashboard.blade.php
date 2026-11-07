@props([
    'title' => 'SEACAST — Ruang Kendali Operasional Kelautan',
    'fullBleed' => false
])
<!DOCTYPE html>
<html lang="id" data-theme="dashboard" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SEACAST — Ruang Kendali Operasional Kelautan' }}</title>

    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/branding/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/branding/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/branding/apple-touch-icon.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|jetbrains-mono:400,600,700&display=swap" rel="stylesheet" />

    <!-- Leaflet Engine & Map Styling -->
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <!-- Compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-[#071B2B] text-[#E7F0F5] min-h-screen flex antialiased font-sans">
    <!-- Dashboard Sidebar -->
    <x-dashboard.sidebar />

    <!-- Main Operational Workspace -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
        <!-- Topbar -->
        <x-dashboard.topbar :title="$title ?? 'Operational Deck'" />

        <!-- Subheader / Notification Flash -->
        @if(session('success'))
            <div class="px-4 sm:px-6 lg:px-8 mt-4">
                <x-ui.alert variant="success" :dismissible="true">
                    {{ session('success') }}
                </x-ui.alert>
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 sm:px-6 lg:px-8 mt-4">
                <x-ui.alert variant="danger" :dismissible="true">
                    {{ session('error') }}
                </x-ui.alert>
            </div>
        @endif

        <!-- Main Body -->
        <main class="flex-1 {{ $fullBleed ? 'p-0 overflow-hidden flex flex-col' : 'p-4 sm:p-6 lg:p-8 space-y-6' }}">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>

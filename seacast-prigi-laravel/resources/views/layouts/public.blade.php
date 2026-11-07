<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SEACAST — Scientific Ocean Intelligence & Fisheries Decision Support' }}</title>
    <meta name="description" content="Platform Intelijen Oseanografi dan Prediksi Kesesuaian Daerah Penangkapan Ikan Pelagis (Tongkol, Cakalang, Madidihang) berbasiskan 8.129 sel kanonikal PostGIS WPPNRI 573.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900|jetbrains-mono:400,600,700&display=swap" rel="stylesheet" />

    <!-- Leaflet Engine & Map Styling -->
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}" />
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>

    <!-- Compiled Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#F6F9FB] dark:bg-[#071B2B] text-slate-900 dark:text-slate-100 min-h-screen flex flex-col antialiased">
    <!-- Public Navbar Component -->
    <x-public.navbar />

    <!-- Global Flash Notification -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <x-ui.alert variant="success" :dismissible="true">
                {{ session('success') }}
            </x-ui.alert>
        </div>
    @endif
    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <x-ui.alert variant="danger" :dismissible="true">
                {{ session('error') }}
            </x-ui.alert>
        </div>
    @endif

    <!-- Main Content Container -->
    <main class="flex-1 w-full">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Public Footer Component -->
    <x-public.footer />

    @livewireScripts
</body>
</html>


{{-- Port of frontend/app/layout.tsx + frontend/app/providers.tsx --}}
<!DOCTYPE html>
<html lang="en" data-scroll-behavior="smooth" class="{{ $htmlClass ?? '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes, viewport-fit=cover">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#1D9E75">
    <meta name="description" content="A safe, confidential platform for Gordon College students to file complaints, track case status, and communicate directly with the DEIU.">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SpeakUp GC">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SpeakUp GC' }}</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/favicon-64x64.png" sizes="64x64" type="image/png">
    <link rel="icon" href="/favicon-180x180.png" sizes="180x180" type="image/png">
    <link rel="apple-touch-icon" href="/icon-192x192.png" sizes="192x192" type="image/png">

    {{-- next-themes equivalent: apply stored theme before paint (storageKey "speakup-theme", default light) --}}
    <script>
(function(){try{var t=localStorage.getItem('speakup-theme');var d=document.documentElement;if(t==='dark'){d.classList.add('dark');d.style.colorScheme='dark';}else{d.classList.remove('dark');d.style.colorScheme='light';}}catch(e){}})();
    </script>
    {{-- Mute debug console noise on deployed builds before app bundles load (verbatim from layout.tsx) --}}
    <script>
(function(){
  try {
    var h = location.hostname;
    var isDev = h === 'localhost' || h === '127.0.0.1' || h.endsWith('.local');
    if (!isDev) {
      var n = function(){};
      console.log = n;
      console.debug = n;
      console.info = n;
      console.warn = n;
    }
  } catch (e) {}
})();
    </script>

    <style>[x-cloak]{display:none !important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-background text-foreground">
    {{ $slot }}

    {{-- Session flash -> toast (same look as the shadcn toaster) --}}
    @php
        $flashToasts = [];
        if (session('toast')) { $flashToasts[] = session('toast'); }
        if (session('status')) { $flashToasts[] = ['title' => session('status')]; }
        if ($errors->any() && ! session('toast')) {
            $flashToasts[] = ['title' => 'Error', 'description' => $errors->first(), 'variant' => 'destructive'];
        }
    @endphp
    @if (count($flashToasts))
        <script type="application/json" id="speakup-flash">@json($flashToasts)</script>
    @endif
    @stack('scripts')
</body>
</html>

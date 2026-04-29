<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <style>
            html {
                background-color: oklch(1 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'HomeDuty') }}</title>

        <meta name="description" content="HomeDuty is a quiet, fair chore scheduler for the people you actually live with. Create a private home group, share duties, and let rotations handle the rest.">
        <meta name="keywords" content="chore scheduler, household chores, roommate app, family duties, cleaning rotation, cooking rotation, shared chores, home management">
        <meta name="author" content="Mohamed Idris Musa">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="HomeDuty">
        <meta property="og:title" content="HomeDuty — Shared chores, fairly handled">
        <meta property="og:description" content="A quiet, fair chore scheduler for the people you actually live with.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('logo.png') }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="HomeDuty — Shared chores, fairly handled">
        <meta name="twitter:description" content="A quiet, fair chore scheduler for the people you actually live with.">
        <meta name="twitter:image" content="{{ asset('logo.png') }}">

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <script type="application/ld+json">@verbatim
        {
            "@context": "https://schema.org",
            "@type": "WebApplication",
            "name": "HomeDuty",
            "description": "A quiet, fair chore scheduler for housemates and families.",
            "applicationCategory": "LifestyleApplication",
            "operatingSystem": "Web"
        }
        @endverbatim</script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

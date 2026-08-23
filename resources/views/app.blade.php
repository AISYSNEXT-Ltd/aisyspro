<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Solutions métier, CRM, sites web connectés et hébergement pour les professionnels et PME.">
    <meta name="robots" content="{{ request()->is('admin*', 'connexion-admin') ? 'noindex,nofollow' : 'index,follow' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="AISYSPRO">
    <meta property="og:title" content="AISYSPRO — CRM, site web et hébergement métier">
    <meta property="og:description" content="Solutions métier, CRM, sites web connectés et hébergement pour les professionnels et PME.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <title>AISYSPRO — CRM, site web et hébergement métier</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>

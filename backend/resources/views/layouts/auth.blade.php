<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex, nofollow" />
  <title>{{ $title ?? 'Admin' }} · Founders Table</title>
  <link rel="icon" href="/assets/img/favicon.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" />
  <link rel="stylesheet" href="/admin-assets/admin.css?v=2" />
</head>
<body class="auth-body">
  <main class="auth-shell">
    {{ $slot }}
  </main>
</body>
</html>

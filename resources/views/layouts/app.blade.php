<!DOCTYPE html>
<html lang="en" class="antialiased bg-white min-h-screen">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta http-equiv="x-ua-compatible" content="ie=edge">
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="manifest" href="/manifest.json">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#00b4b6">
		<title>xTenant - @yield('page_title')</title>
		<!-- Scripts -->
		<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.3.5/dist/alpine.min.js" defer></script>
		@stack('scripts')

		<!-- Fonts -->
		<link rel="dns-prefetch" href="//fonts.gstatic.com">
		<link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

		<link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
	</head>
	<body  x-data="{ bShowMenu: false }" class="flex flex-col leading-normal min-h-screen text-gray-900">
		@yield('header')
		<div class="flex-1 flex">
			@yield('nav')
			<section :class="{ 'hidden': bShowMenu }" class="flex-1 m-2 mt-12 p-2 md:block">
			@yield('content')
			</section>
		</div>
		<div class="md:flex">
			@if (!request()->is('/'))
			<div class="md:w-64"></div>
			@endif
			<footer class="flex-1 p-1 text-center text-gray-700 text-xs">&copy;{{ date('Y') }} xTenant</footer>
		</div>
	</body>
</html>
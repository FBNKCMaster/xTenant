@extends('xtenant::layouts.app')

@section('page_title', 'About')

@section('header')
  @include('xtenant::layouts.header')
@endsection

@section('nav')
  @include('xtenant::layouts.nav')
@endsection

@section('content')
  <div class="container mx-auto">
    <div class="bg-gray-100 border m-1 p-4 rounded text-gray-700">
      <h2 class="text-xl">ABOUT THIS PROJECT</h2>
      <h2 class="mt-2 text-lg">Philosophy</h2>
      <p class="bg-gray-200 p-2 text-sm">
        In summary: "<b>Plug & Play</b>".
        <br>
        We have always loved the way apple handles it when it comes to its accessories. Don't we?
        <br>
        Who doesn't like it when "it just works" with no more steps to do or annoying configuration changes?
        <br>
        This is the aim of this package, just "<b>require & setup</b>". With no more steps, no additional configuration, and no mandatory code changing, you get your app multi-tenancy ready, and everything is handled for you to run multiple web apps with one single Laravel installation.
      </p>
      <h2 class="mt-2 text-lg">Behind the scenes</h2>
      <p class="bg-gray-200 p-2 text-sm">
        Here are the tools used in this project:
        <br>
        - Backend: <a class="font-semibold" href="https://laravel.com">Laravel</a> framework (of course)
        <br>
        - Frontend: <a class="font-semibold" href="https://tailwindcss.com">Tailwindcss</a> + <a class="font-semibold" href="https://github.com/alpinejs/alpine">Alpinejs</a> (SuperAdmin web UI)
        <br>
        - Testing: <a class="font-semibold" href="https://github.com/orchestral/testbench">Orchestra Bench</a> (to phpunit test laravel packages)
      </p>
      <h2 class="mt-2 text-lg">Credits</h2>
      <p class="bg-gray-200 p-2 text-sm">
        Big thanks to the great community of [ Laravel + Vue.js/Alpine.js + Tailwind CSS ] especially:
        <br>
        - <a class="font-semibold" href="https://twitter.com/taylorotwell">Taylor Otwell</a> and the team: for Laravel, the great framework
        <br>
        - <a class="font-semibold" href="https://twitter.com/tomschlick">Tom Schlick</a>: being the first to talk and bringing first insights and ideas about multi-tenancy with Laravel
        <br>
        - <a class="font-semibold" href="https://twitter.com/themsaid">Mohamed Said</a>: for his tutorials on youtube and write-ups about this complex subject
        <br>
        - <a class="font-semibold" href="https://twitter.com/adamwathan">Adam Wathan</a>and the team: for the awesome Tailwind CSS
        <br>
        - <a class="font-semibold" href="https://twitter.com/calebporzio">Caleb Porzio</a>: for Alpine.js (really sweet alternative to Vue.js)
      </p>
      <h2 class="mt-2 text-lg">Contribution</h2>
      <p class="bg-gray-200 p-2 text-sm">
        xTenant is an open source project and anyone can contribute to make it better.
        <br>
        So if you like the philosophy and the idea, feel free to fork, test, PR, rise issues, suggest ideas and sponsor as well :)
        <br>
        Here is the repository url: <a class="font-semibold" href="https://github.com/FBNKCMaster/xTenant">https://github.com/FBNKCMaster/xTenant</a>
      </p>
      <h2 class="mt-2 text-lg">Whoami</h2>
      <p class="bg-gray-200 p-2 text-sm">
        Time to introduce my self. My name is <b>Farid BEN KACEM</b> from Morocco. I'm passionate about web developement.
        <br>
        I'm working on other side projects at the same time, so feel free to follow me on Twitter <a class="font-semibold" href="https://twitter.com/FBNKCMaster">@FBNKCMaster</a> or just contact me for any feedback or suggestions to improve this project :)
        <br>
        Thank you!
      </p>
    </div>
  </div>
@endsection
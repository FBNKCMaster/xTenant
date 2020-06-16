@extends('xtenant::layouts.app')

@section('page_title', 'Console Commands')

@section('header')
  @include('xtenant::layouts.header')
@endsection

@section('nav')
  @include('xtenant::layouts.nav')
@endsection

@section('content')
  <div class="container mx-auto">
    <div class="bg-gray-100 border m-1 p-4 rounded text-gray-700">
      <h2 class="text-xl">CONSOLE COMMANDS</h2>
      <p class="p-2">
        To take more advantage on xTenant, you can use the console command to run artisan commands for advanced operations.
        <br>
        Here is the list of available commands:
        <br>
        (all commands start with 'xtenant:')
      </p>
      <h2 class="mt-2 text-lg">Setup package (run it once to get started)</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:setup
      </p>
      <h2 class="mt-2 text-lg">Create a tenant</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:new
      </p>
      <h2 class="mt-2 text-lg">Edit a tenant</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:edit [tenant]
      </p>
      <h2 class="mt-2 text-lg">Destroy a tenant</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:destroy [tenant]
      </p>
      <h2 class="mt-2 text-lg">Run migrations for a tenant</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:migrate [tenant]
      </p>
      <h2 class="mt-2 text-lg">Run seeds for a tenant</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:seed [tenant]
      </p>
      <h2 class="mt-2 text-lg">Create directory & symbolic link for a tenant</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:directory [tenant]
      </p>
      <h2 class="mt-2 text-lg">Backup tenant's database</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:backupdb [tenant]
      </p>
      <h2 class="mt-2 text-lg">Backup tenant's directory</h2>
      <p class="bg-gray-200 p-2 text-sm">
        $ php artisan xtenant:backupdir [tenant]
      </p>
    </div>
  </div>
@endsection
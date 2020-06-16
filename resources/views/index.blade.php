@extends('xtenant::layouts.app')

@section('page_title', 'Welcome')

@section('content')
  <div class="container flex flex-1 h-full items-center justify-center mx-auto">
    <div class="text-center">
      <div class="flex items-center">
        <div class="bg-center bg-contain bg-no-repeat h-40 w-64" style="background-image:url('https://raw.githubusercontent.com/FBNKCMaster/xTenant/master/xTenant_Logo.png')"></div>
        <div class="mx-4 text-3xl text-gray-800">WELCOME</div>
      </div>
      @guest('superadmin')
				<a class="mx-auto text-blue-600" href="/howto">Installation & Getting Started</a>
			@else
        <a class="mx-auto text-blue-600" href="/dashboard">DASHBOARD</a>
      @endguest
    </div>
  </div>
@endsection
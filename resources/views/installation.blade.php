@extends('xtenant::layouts.app')

@section('page_title', 'Installation')

@section('header')
  @include('xtenant::layouts.header')
@endsection

@section('content')
  <div class="container mx-auto">
    <div class="flex items-center justify-center">
      <div class="p-4">
        <div class="m-1 relative">
          <div class="bg-center bg-contain bg-no-repeat h-40" style="background-image:url('https://raw.githubusercontent.com/FBNKCMaster/xTenant/master/xTenant_Logo.png')"></div>
          <div class="absolute bottom-0 font-semibold inset-x-0 p-2 m-1 text-blue-400 text-center text-sm">INSTALLATION & GETTING STARTED</div>
        </div>

        <div class="borde m-1 p-1">
          <div class="flex items-center rounded-full">
            <div class="bg-red-600 flex font-bold h-10 items-center justify-center rounded-full shadow-md text-white text-sm w-10 z-10">
              1
            </div>
            <div class="bg-blue-100 flex flex-1 h-10 items-center justify-center px-4 pl-10 -ml-10 rounded-full shadow text-blue-800">
              Clone & Install the "demo-app"
            </div>
          </div>
          <div class="bg-gray-100 border border-t-0 p-1 ml-10 mr-4 text-xs">
            This is a sample app made for demonstration purpose
            <br>
            You can use it to test any other laravel package
            <br>
            Find it here: <b>https://github.com/FBNKCMaster/demo-app</b>
            <br>
            After install make sure you can access it via: [<b>http://demo-app.test</b>]
          </div>
          <div class="bg-gray-900 font-semibold p-1 ml-10 mr-4 rounded-b text-xs">
            <span class="mr-px text-white">$</span><span class="ml-px text-green-300">git clone https://github.com/FBNKCMaster/demo-app.git</span>
          </div>
        </div>

        <div class="borde m-1 p-1">
          <div class="flex items-center rounded-full">
            <div class="bg-red-600 flex font-bold h-10 items-center justify-center rounded-full shadow-md text-white text-sm w-10 z-10">
              2
            </div>
            <div class="bg-blue-100 flex flex-1 h-10 items-center justify-center px-4 pl-10 -ml-10 rounded-full shadow text-blue-800">
              Install xTenant package
            </div>
          </div>
          <div class="bg-gray-100 border border-t-0 p-1 ml-10 mr-4 text-xs">
            Run this command:
          </div>
          <div class="bg-gray-900 font-semibold p-1 ml-10 mr-4 rounded-b text-xs">
            <span class="mr-px text-white">$</span><span class="ml-px text-green-300">composer require fbnkcmaster/xtenant</span>
          </div>
        </div>

        <div class="borde m-1 p-1">
          <div class="flex items-center rounded-full">
            <div class="bg-red-600 flex font-bold h-10 items-center justify-center rounded-full shadow-md text-white text-sm w-10 z-10">
              3
            </div>
            <div class="bg-blue-100 flex flex-1 h-10 items-center justify-center px-4 pl-10 -ml-10 rounded-full shadow text-blue-800">
              Setup
            </div>
          </div>
          <div class="bg-gray-100 border border-t-0 p-1 ml-10 mr-4 text-xs">
            Run this command and follow instructions:
          </div>
          <div class="bg-gray-900 font-semibold p-1 ml-10 mr-4 rounded-b text-xs">
            <span class="mr-px text-white">$</span><span class="ml-px text-green-300">php artisan xtenant:setup</span>
          </div>
        </div>

        <div class="borde m-1 p-1">
          <div class="flex items-center rounded-full">
            <div class="bg-red-600 flex font-bold h-10 items-center justify-center rounded-full shadow-md text-white text-sm w-10 z-10">
              4
            </div>
            <div class="bg-blue-100 flex flex-1 h-10 items-center justify-center px-4 pl-10 -ml-10 rounded-full shadow text-blue-800">
              Create Tenant
            </div>
          </div>
          <div class="bg-gray-100 border border-t-0 p-1 ml-10 mr-4 text-xs">
            Run this command and follow instructions again:
          </div>
          <div class="bg-gray-900 font-semibold p-1 ml-10 mr-4 rounded-b text-xs">
            <span class="mr-px text-white">$</span><span class="ml-px text-green-300">php artisan xtenant:new</span>
          </div>
        </div>

        <div class="borde m-1 p-1">
          <div class="flex items-center rounded-full">
            <div class="bg-red-600 flex font-bold h-10 items-center justify-center rounded-full shadow-md text-white text-sm w-10 z-10">
              5
            </div>
            <div class="bg-blue-100 flex flex-1 h-10 items-center justify-center px-4 pl-10 -ml-10 rounded-full shadow text-blue-800">
              That's all!
            </div>
          </div>
          <div class="bg-gray-100 border border-t-0 p-1 ml-10 mr-4 text-xs">
            If everything went ok, you will be able to access your tenant at:
          </div>
          <div class="bg-gray-900 font-semibold p-1 ml-10 mr-4 rounded-b text-xs">
            <span class="mr-px text-white">></span><span class="ml-px text-green-300">http://[tenant].demo-app.test</span>
          </div>
        </div>

      </div>
    </div>
  </div>
@endsection
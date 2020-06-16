@extends('xtenant::layouts.app')

@section('page_title', 'Dashboard')

@section('header')
  @include('xtenant::layouts.header')
@endsection

@section('nav')
  @include('xtenant::layouts.nav')
@endsection

@section('content')
  <div class="flex-1">
    <div class="flex">
      <div class="border flex-1 flex-shrink-0 m-1 rounded shadow">      
        <div class="p-4">
          <h2 class="text-xl">Total Number Of Tenants</h2>
          <p class="text-blue-600 text-3xl">{{ $tenants->count() }}</p>
        </div>
      </div>
      <div class="border flex-1 flex-shrink-0 m-1 rounded shadow">      
        <div class="p-4">
          <h2 class="text-xl">Active Tenants</h2>
          <p class="text-green-600 text-3xl">{{ $tenants->where('status', 1)->count() }}</p>
        </div>
      </div>
    </div>
    <div class="bg-gray-100 border m-1 p-4 rounded text-gray-700">
      We'll figure out other intersting things to show here
    </div>
  </div>
@endsection
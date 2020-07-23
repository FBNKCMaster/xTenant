@extends('xtenant::layouts.app')

@section('page_title', 'Console')

@push('scripts')
  <script src="{{ asset('js/app.js') }}" defer></script>
@endpush

@section('header')
  @include('xtenant::layouts.header')
@endsection

@section('nav')
  @include('xtenant::layouts.nav')
@endsection

@section('content')
	<div class="flex-1">
		<div class="border flex-1 m-1 rounded shadow">
			<div class="border-b">
				<div class="p-4">
					<h2 class="text-xl">SuperAdmin Console</h2>
					<p class="text-gray-600 text-sm">Type <b>help</b> for the list of available commands</p>
				</div>
			</div>
      <div id="app">
        <!-- Console -->
        <xtenant-console cmd-url="/cmd" cmd-prefix="php artisan xtenant:" input-prefix=">"></xtenant-console>
      </div>
		</div>
  </div>
@endsection
@extends('xtenant::layouts.app')

@section('page_title', 'Login')

@section('content')
<div class="container mx-auto">
	<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
		<div class="max-w-md w-full">
			<div>
				<h1 class="font-bold text-4xl text-center">
          <span class="bg-gray-900 border border-gray-700 p-2 px-4 rounded-lg shadow">
					  <span class="text-red-500">x</span><span class="text-gray-200">Tenant</span>
          </span>
					<span class="font-normal mx-2 text-gray-700">Super Admin</span>
				</h1>
				<h2 class="mt-6 text-center text-2xl leading-9 font-extrabold text-gray-900">
					Log in to your account
				</h2>
			</div>
			<form class="mt-8" action="{{ route('login') }}" method="POST">
				@csrf
				@error('email')
				<div class="border-l-2 border-red-500 font-semibold m-2 px-2 text-red-500 text-xs" role="alert">{{ $message }}</div>
				@enderror
				@error('password')
				<div class="border-l-2 border-red-500 font-semibold m-2 px-2 text-red-500 text-xs" role="alert">{{ $message }}</div>
				@enderror
				<div class="rounded-md shadow-sm">
					<div>
						<input aria-label="{{ __('E-Mail Address') }}" name="email" type="email" value="{{ old('email') }}" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:shadow-outline-blue focus:border-blue-300 focus:z-10 sm:text-sm sm:leading-5 @error('email') bg-red-300 text-red-600 @enderror" placeholder="{{ __('E-Mail Address') }}" />
					</div>
					<div class="-mt-px">
						<input aria-label="{{ __('Password') }}" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:shadow-outline-blue focus:border-blue-300 focus:z-10 sm:text-sm sm:leading-5 @error('password') bg-red-300 text-red-600 @enderror" placeholder="{{ __('Password') }}" />
					</div>
				</div>

				<div class="mt-6 flex items-center justify-between">
					<div class="flex items-center">
						<input id="remember_me" type="checkbox" name="remember"  class="form-checkbox h-4 w-4 text-gray-600 transition duration-150 ease-in-out" {{ old('remember') ? 'checked' : '' }}/>
						<label for="remember_me" class="ml-2 block text-sm leading-5 text-gray-900">
						{{ __('Remember Me') }}
						</label>
					</div>
					@if (Route::has('password.request'))
					<div class="text-sm leading-5">
						<a href="#" class="font-medium text-gray-600 hover:text-gray-500 focus:outline-none focus:underline transition ease-in-out duration-150">
						{{ __('Forgot your password?') }}
						</a>
					</div>
					@endif
				</div>

				<div class="mt-6">
					<button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-gray-600 hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:shadow-outline-gray active:bg-gray-700 transition duration-150 ease-in-out">
						{{ __('Login') }}
					</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection

@extends('xtenant::layouts.app')

@section('page_title', 'Settings')

@push('scripts')
	<script>
		window.onload = () => {
			function prevImage(input, placeholder) {
				const reader = new FileReader();
				reader.onload = e => {
					document.getElementById(placeholder).style.backgroundImage = 'url(' + e.target.result + ')';
				};
				document.getElementById(input).addEventListener('change', e => {
					const f = e.target.files[0];
					reader.readAsDataURL(f);
				});
			}
			prevImage('profile_input', 'profile');
		};
	</script>
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
					<h2 class="text-xl">Settings Informations</h2>
					<p class="text-gray-600 text-sm">General settings and Super Admin Informations</p>
				</div>
			</div>
			<form class="text-sm" action="{{ route('settings') }}" method="post" enctype="multipart/form-data">
				@csrf
				@method('PATCH')
				@if ($errors->any())
					<div class="bg-red-200 border border-red-300 m-2 p-2 rounded text-red-600 text-sm">
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif
				<div x-data="{ edit: false }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Super Admin Subdomain</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('super_admin_subdomain') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<input class="bg-transparent p-2 w-full focus:outline-none" type="text" name="super_admin_subdomain" value="{{ $xtenant_settings->super_admin_subdomain ?? '' }}" placeholder="superadmin (default: xtenant)">
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $xtenant_settings->super_admin_subdomain ?? '' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false, allow_www: {{ $xtenant_settings->allow_www ? 1 : 0 }} }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Allow "www"</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('allow_www') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<div class="flex p-2">
									<div class="border flex items-center rounded shadow-sm">
										<button :class="{ 'bg-gray-600 text-gray-200': allow_www == 1 }" @click.prevent="allow_www = 1" class="bg-white px-1 rounded-l focus:outline-none">Yes</button>
										<button :class="{ 'bg-gray-600 text-gray-200': allow_www == 0 }" @click.prevent="allow_www = 0" class="bg-white border-l px-1 rounded-r focus:outline-none">No</button>
									</div>
									<input type="hidden" name="allow_www" x-model="allow_www">
								</div>
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $xtenant_settings->allow_www == 1 ? 'Yes' : 'No' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Super Admin Email</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('email') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<input class="bg-transparent p-2 w-full focus:outline-none" type="text" name="email" value="{{ $xtenant_settings->email ?? '' }}" placeholder="email@example.com">
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $xtenant_settings->email ?? '--' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Password</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('password') bg-red-200 text-red-700 @enderror @error('current_password') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit == true">
							<div>
								<input class="bg-transparent p-2 w-full focus:outline-none" type="password" name="current_password" value="" placeholder="Current password">
								<input class="bg-transparent border-t border-b p-2 w-full focus:outline-none" type="password" name="password" value="" placeholder="new password">
								<input class="bg-transparent p-2 w-full focus:outline-none" type="password" name="password_confirmation" value="" placeholder="confirm password">
							</div>
							</template>
							<template x-if="edit != true">
								<div class="p-2">************</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Super Admin Name</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('name') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<input class="bg-transparent p-2 w-full focus:outline-none" type="text" name="name" value="{{ $xtenant_settings->name ?? '' }}" placeholder="Your Name">
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $xtenant_settings->name ?? '--' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Profile</div>
					<div class="flex flex-1 p-2 px-4 @error('profile') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<div class="flex items-center">
								<div id="profile" class="bg-center bg-cover bg-no-repeat bg-gray-100 border h-12 rounded-full w-12 @error('profile') border-red-500 @enderror" style="background-image:url({{ asset('../profile.jpeg') }});"></div>
								<label class="border cursor-pointer mx-2 p-1 px-2 rounded-md shadow text-xs" for="profile_input">Change</label>
								<input id="profile_input" class="hidden" type="file" name="profile" accept="image/*">
							</div>
						</div>
					</div>
				</div>
				<div class="bg-gray-100 flex p-3 px-4">
					<div class="text-gray-700 w-1/3"></div>
					<div class="flex-1 text-right">
						<button class="bg-blue-600 font-semibold p-2 px-4 rounded shadow-sm text-white text-sm">Save</button>
					</div>
				</div>
			</form>
		</div>
  </div>
@endsection
@extends('xtenant::layouts.app')

@section('page_title', 'Edit Tenant')

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
			prevImage('image_input', 'image');
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
					<h2 class="text-xl">{{ $tenant->name }}</h2>
					<span class="mx-1 text-sm">Access URL:</span><a href="{{ str_replace($super_admin_subdomain, $tenant->subdomain, url('')) }}" class="text-gray-600 text-sm" target="_blank">{{ str_replace($super_admin_subdomain, $tenant->subdomain, url('')) }}</a>
				</div>
			</div>
			<form class="text-sm" action="{{ route('update', $tenant->id) }}" method="post" enctype="multipart/form-data" accept="image/jpg,image/jpeg">
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
				<div class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Photo</div>
					<div class="bg-gray-100 flex flex-1 p-2 px-4 @error('image') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<label class="bg-gray-100 border border-dashed cursor-pointer flex relative rounded-md @error('image') border-red-500 @enderror" for="image_input">
								<div id="image" class="bg-center bg-cover bg-no-repeat flex-1 h-0 rounded-md z-10" style="background-image:url({{ asset('../../../' . $tenant->subdomain . '/' . 'image.jpeg') }});padding-bottom:40%;"></div>
								<div class="absolute flex inset-0 items-center justify-center z-0">
									<span class="text-gray-700 text-xs">+ Upload Image</span>
								</div>
								<input id="image_input" class="hidden" type="file" name="image" accept="image/*">
							</label>	
						</div>
					</div>
				</div>
				<div x-data="{ edit: false }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Subdomain</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('subdomain') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<input class="bg-transparent p-2 w-full focus:outline-none" type="text" name="subdomain" value="{{ $tenant->subdomain ?? '' }}" placeholder="superadmin (default: xtenant)">
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $tenant->subdomain ?? '' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Name</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('name') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<input class="bg-transparent p-2 w-full focus:outline-none" type="text" name="name" value="{{ $tenant->name ?? '' }}" placeholder="Your Name">
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $tenant->name ?? '--' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Description</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('description') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<textarea class="bg-transparent p-2 w-full focus:outline-none" name="description" placeholder="Description of the tenant here">{{ $tenant->description ?? '' }}</textarea>
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $tenant->description ?? '--' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false, migrations: null }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Migrations</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('migrations') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<div class="flex p-2">
									<div class="border flex items-center rounded shadow-sm">
										<button :class="{ 'bg-gray-600 text-gray-200': migrations == 1 }" @click.prevent="migrations = 1" class="bg-white px-1 rounded-l focus:outline-none">Reset</button>
										<button :class="{ 'bg-gray-600 text-gray-200': migrations == 2 }" @click.prevent="migrations = 2" class="bg-white border-l px-1 rounded-r focus:outline-none">Fresh</button>
									</div>
									<input type="hidden" name="migrations" x-model="migrations">
								</div>
							</template>
							<template x-if="!edit">
								<div class="p-2 text-xs">(click [edit] to select)</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false, migrations = null" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false, seeds: null }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Seeds</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('seeds') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<div class="flex p-2">
									<div class="border flex items-center rounded shadow-sm">
										<button :class="{ 'bg-gray-600 text-gray-200': seeds == 1 }" @click.prevent="seeds = 1" class="bg-white px-1 rounded-l focus:outline-none">Seed</button>
										<button :class="{ 'bg-gray-600 text-gray-200': seeds == 2 }" @click.prevent="seeds = 2" class="bg-white border-l px-1 rounded-r focus:outline-none">Fresh Seed</button>
									</div>
									<input type="hidden" name="seeds" x-model="seeds">
								</div>
							</template>
							<template x-if="!edit">
								<div class="p-2 text-xs">(click [edit] to select)</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false, seeds = null" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false, directory: null }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Directory</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('directory') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<div class="flex p-2">
									<div class="border flex items-center rounded shadow-sm">
										<button :class="{ 'bg-gray-600 text-gray-200': directory == 1 }" @click.prevent="directory = 1" class="bg-white px-1 rounded-l focus:outline-none">Create</button>
										<button :class="{ 'bg-gray-600 text-gray-200': directory == 2 }" @click.prevent="directory = 2" class="bg-white border-l px-1 rounded-l focus:outline-none">Backup</button>
										<button :class="{ 'bg-gray-600 text-gray-200': directory == 3 }" @click.prevent="directory = 3" class="bg-white border-l px-1 rounded-r focus:outline-none">Remove</button>
									</div>
									<input type="hidden" name="directory" x-model="directory">
								</div>
							</template>
							<template x-if="!edit">
								<div class="p-2 text-xs">(click [edit] to select)</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div x-data="{ edit: false, status: {{ $tenant->status ? 1 : 0 }} }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Status</div>
					<div :class="{ 'bg-gray-200': edit }" class="flex flex-1 px-4 @error('status') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<template x-if="edit">
								<div class="flex p-2">
									<div class="border flex items-center rounded shadow-sm">
										<button :class="{ 'bg-gray-600 text-gray-200': status == 1 }" @click.prevent="status = 1" class="bg-white px-1 rounded-l focus:outline-none">Enabled</button>
										<button :class="{ 'bg-gray-600 text-gray-200': status == 0 }" @click.prevent="status = 0" class="bg-white border-l px-1 rounded-r focus:outline-none">Disabled</button>
									</div>
									<input type="hidden" name="status" x-model="status">
								</div>
							</template>
							<template x-if="!edit">
								<div class="p-2">{{ $tenant->status == 1 ? 'Enabled' : 'Disabled' }}</div>
							</template>
						</div>
						<div class="flex items-center">
							<button x-show="edit == true" @click.prevent="edit = false" class="text-gray-600 focus:outline-none">cancel</button>
							<button x-show="edit == false" @click.prevent="edit = true" class="text-red-500 focus:outline-none">edit</button>
						</div>
					</div>
				</div>
				<div class="bg-gray-100 flex p-3 px-4">
					<div class="text-gray-700 w-1/3"></div>
					<div class="flex-1 text-right">
						<button class="bg-blue-600 font-semibold p-2 px-4 rounded shadow-sm text-white text-sm">Update</button>
					</div>
				</div>
			</form>
		</div>
		<div class="border border-red-300 flex-1 m-1 mt-2 rounded shadow">
			<div class="border-b">
				<div class="p-4">
					<h2 class="text-red-600 text-xl">Danger zone</h2>
					<p class="text-red-500 text-sm">ATTENTION: Once you delete this tenant, there is no going back. Please be certain.</p>
				</div>
			</div>
			<form class="text-sm" action="{{ route('delete', $tenant->id) }}" method="post">
				@csrf
				@method('DELETE')
				<div class="bg-red-100 flex p-3 px-4">
					<div class="text-gray-700 w-1/3"></div>
					<div x-data="{ bSure: false }" class="flex-1 text-right">
						<template x-if="!bSure">
						<button @click.prevent="bSure = true" class="bg-red-600 border border-red-600 font-semibold p-2 px-4 rounded shadow-sm text-white text-sm focus:outline-none">Delete</button>
						</template>
						<template x-if="bSure">
						<div class="flex items-center justify-end">
							<span class="text-red-600">Are you sure?</span>
							<button @click.prevent="bSure = false" class="border border-red-600 font-semibold mx-2 p-2 px-4 rounded shadow-sm text-red-600 text-sm focus:outline-none">No. Please, cancel</button>
							<button class="bg-red-600 border border-red-600 font-semibold p-2 px-4 rounded shadow-sm text-white text-sm focus:outline-none">Yes, I know what I'm doing!</button>
						</div>
						</template>
					</div>
				</div>
			</form>
		</div>
  </div>
@endsection
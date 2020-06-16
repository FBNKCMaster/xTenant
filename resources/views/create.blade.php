@extends('xtenant::layouts.app')

@section('page_title', 'Create New Tenant')

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
					<h2 class="text-xl">Tenant Informations</h2>
					<p class="text-gray-600 text-sm">You can edit all these informations later</p>
				</div>
			</div>
			<form class="text-sm" action="{{ route('create') }}" method="post" enctype="multipart/form-data" accept="image/jpg,image/jpeg">
				@csrf
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
								<div id="image" class="bg-center bg-cover bg-no-repeat flex-1 h-0 rounded-md z-10" style="padding-bottom:40%;"></div>
								<div class="absolute flex inset-0 items-center justify-center z-0">
									<span class="text-gray-700 text-xs">+ Upload Image</span>
								</div>
								<input id="image_input" class="hidden" type="file" name="image" accept="image/*">
							</label>	
						</div>
					</div>
				</div>
				<div class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Subdomain</div>
					<div class="bg-gray-100 flex flex-1 px-4 @error('subdomain') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<input class="bg-transparent p-2 w-full focus:outline-none" type="text" name="subdomain" value="{{ old('subdomain') }}" placeholder="subdomain">
						</div>
					</div>
				</div>
				<div class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Name</div>
					<div class="bg-gray-100 flex flex-1 px-4 @error('name') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<input class="bg-transparent p-2 w-full focus:outline-none" type="text" name="name" value="{{ old('name') }}" placeholder="Your Name">
						</div>
					</div>
				</div>
				<div class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Description</div>
					<div class="bg-gray-100 flex flex-1 px-4 @error('description') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<textarea class="bg-transparent p-2 w-full focus:outline-none" name="description" placeholder="Description of the tenant here">{{ old('description') }}</textarea>
						</div>
					</div>
				</div>
				<div x-data="{ migrations: 0 }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Run migrations</div>
					<div class="bg-gray-100 flex flex-1 px-4 @error('migrations') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<div class="flex p-2">
								<div class="border flex items-center rounded shadow-sm">
									<button :class="{ 'bg-gray-600 text-gray-200': migrations == 1 }" @click.prevent="migrations = 1" class="bg-white px-1 rounded-l focus:outline-none">Yes</button>
									<button :class="{ 'bg-gray-600 text-gray-200': migrations == 0 }" @click.prevent="migrations = 0" class="bg-white border-l px-1 rounded-r focus:outline-none">No</button>
								</div>
								<input type="hidden" name="migrations" x-model="migrations">
							</div>
						</div>
					</div>
				</div>
				<div x-data="{ seeds: 0 }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Run seeds</div>
					<div class="bg-gray-100 flex flex-1 px-4 @error('seeds') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<div class="flex p-2">
								<div class="border flex items-center rounded shadow-sm">
									<button :class="{ 'bg-gray-600 text-gray-200': seeds == 1 }" @click.prevent="seeds = 1" class="bg-white px-1 rounded-l focus:outline-none">Yes</button>
									<button :class="{ 'bg-gray-600 text-gray-200': seeds == 0 }" @click.prevent="seeds = 0" class="bg-white border-l px-1 rounded-r focus:outline-none">No</button>
								</div>
								<input type="hidden" name="seeds" x-model="seeds">
							</div>
						</div>
					</div>
				</div>
				<div x-data="{ directory: 0 }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Create directory</div>
					<div class="bg-gray-100 flex flex-1 px-4 @error('directory') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<div class="flex p-2">
								<div class="border flex items-center rounded shadow-sm">
									<button :class="{ 'bg-gray-600 text-gray-200': directory == 1 }" @click.prevent="directory = 1" class="bg-white px-1 rounded-l focus:outline-none">Yes</button>
									<button :class="{ 'bg-gray-600 text-gray-200': directory == 0 }" @click.prevent="directory = 0" class="bg-white border-l px-1 rounded-r focus:outline-none">No</button>
								</div>
								<input type="hidden" name="directory" x-model="directory">
							</div>
						</div>
					</div>
				</div>
				<div x-data="{ status: 0 }" class="border-b flex items-center">
					<div class="px-4 text-gray-700 w-1/3">Status</div>
					<div class="bg-gray-100 flex flex-1 px-4 @error('status') bg-red-200 text-red-700 @enderror">
						<div class="flex-1">
							<div class="flex p-2">
								<div class="border flex items-center rounded shadow-sm">
									<button :class="{ 'bg-gray-600 text-gray-200': status == 1 }" @click.prevent="status = 1" class="bg-white px-1 rounded-l focus:outline-none">Enabled</button>
									<button :class="{ 'bg-gray-600 text-gray-200': status == 0 }" @click.prevent="status = 0" class="bg-white border-l px-1 rounded-r focus:outline-none">Disabled</button>
								</div>
								<input type="hidden" name="status" x-model="status">
							</div>
						</div>
					</div>
				</div>
				<div class="bg-gray-100 flex p-3 px-4">
					<div class="text-gray-700 w-1/3"></div>
					<div class="flex-1 text-right">
						<button class="bg-blue-600 font-semibold p-2 px-4 rounded shadow-sm text-white text-sm">Create</button>
					</div>
				</div>
			</form>
		</div>
  </div>
@endsection
			<nav :class="{ 'hidden': !bShowMenu }" class="text-sm md:block">
				<div class="fixed inset-0 bg-gray-200 md:w-64" style="z-index: -1;"></div>
				<div class="fixed inset-0 mt-12 overflow-y-auto z-40 md:relative md:w-64">
					<div class="font-semibold p-4 text-gray-800">
						<a class="block p-1 px-2 rounded hover:bg-gray-300 hover:text-gray-700" href="/dashboard">Dashboard</a>
						<a class="block p-1 px-2 rounded hover:bg-gray-300 hover:text-gray-700" href="/settings">Settings</a>
						<a class="block p-1 px-2 rounded hover:bg-gray-300 hover:text-gray-700" href="/console">Console</a>
					</div>
					<div class="border-t p-4 w-full">
						<div class="font-semibold px-2 text-gray-600">Manage<span class="bg-gray-300 font-normal italic ml-1 p-px rounded-full text-gray-600 text-xs">Coming soon</span></div>
						<span class="block p-1 px-4 rounded text-gray-600 text-xs hover:bg-gray-300 hover:text-gray-700">Databases</span>
						<span class="block p-1 px-4 rounded text-gray-600 text-xs hover:bg-gray-300 hover:text-gray-700">Resources</span>
						<span class="block p-1 px-4 rounded text-gray-600 text-xs hover:bg-gray-300 hover:text-gray-700">Queues</span>
					</div>
					<div class="border-t flex p-4 w-full">
						<a class="bg-red-700 border border-red-600 flex-1 font-semibold px-2 py-1 rounded shadow text-center text-xs text-white" href="/tenants/create">CREATE NEW TENANT</a>
					</div>
					<div x-data="{ tenants: {{ $tenants }}, query: '', showSearch: false }" class="border-t p-4">
						<div @click.away="showSearch = false" class="relative">
							<button @click.prevent="showSearch = true" class="absolute flex inset-y-0 right-0 items-center p-2 focus:outline-none">
								<svg class="fill-current h-4 mt-1 text-gray-500 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
									<path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
								</svg>
							</button>
							<div x-show="!showSearch" class="font-semibold p-2 text-gray-500">Tenants</div>
							<input x-show="showSearch" x-model="query" class="p-2 rounded-full shadow w-full focus:outline-none" type="text" placeholder="Search...">
						</div>
						<div class="">
							<template x-for="tenant in tenants" :key="tenant.id">
							<div>
								<template x-if="!query || (tenant.name + ' ' + tenant.description).toLowerCase().indexOf(query.trim().toLowerCase()) != -1">
								<a class="block p-1 px-4 rounded text-xs hover:bg-gray-300 hover:text-gray-700" :href="'/tenants/' + tenant.id + '/edit'" x-text="tenant.name"></a>
								</template>
							</div>
							</template>
						</div>
					</div>
				</div>
			</nav>
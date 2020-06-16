		<header class="bg-white fixed flex items-center justify-between shadow w-full z-50">
			<div class="bg-transparent flex justify-center relative w-64 md:bg-gray-900">
				<button @click.prevent="bShowMenu = !bShowMenu" type="button" class="absolute flex items-center inset-y-0 left-0 mx-4 text-gray-500 focus:outline-none focus:text-gray-700 md:hidden">
					<svg class="fill-current w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
							<path x-show="!bShowMenu" d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"></path>
							<path x-show="bShowMenu" d="M10 8.586L2.929 1.515 1.515 2.929 8.586 10l-7.071 7.071 1.414 1.414L10 11.414l7.071 7.071 1.414-1.414L11.414 10l7.071-7.071-1.414-1.414L10 8.586z"></path>
					</svg>
				</button>
				<a href="/" class="bg-gray-900 border-2 border-gray-900 font-semibold leading-none my-1 p-px px-4 rounded text-center text-3xl mx-4">
					<span class="text-red-500">x</span><span class="text-gray-200">Tenant</span>
				</a>
			</div>
			<div class="flex items-center rounded px-1">
			@guest
				<a href="{{ route('login') }}" class="bg-gray-700 border-gray-600 mx-1 px-4 rounded text-white text-sm">{{ __('Login') }}</a>
			@else
				<div @click.away="openProfileMenu = false" x-data="{ openProfileMenu: false }" class="ml-3 relative">
					<div>
						<button @click.prevent="openProfileMenu = true" class="flex items-center text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-white transition duration-150 ease-in-out" id="user-menu" aria-label="User menu" aria-haspopup="true">
							<span class="m-2 text-xs">{{ Auth::user()->name ?? 'Super Admin' }}</span>
							<div class="bg-center bg-cover bg-no-repeat bg-gray-200 border h-8 w-8 rounded-full" style="background-image: url({{ asset('../profile.jpeg') }})"></div>
						</button>
					</div>
					<div x-show="openProfileMenu"
								x-transition:enter="transition ease-out duration-300"
								x-transition:enter-start="opacity-0 transform scale-90"
								x-transition:enter-end="opacity-100 transform scale-100"
								x-transition:leave="transition ease-in duration-300"
								x-transition:leave-start="opacity-100 transform scale-100"
								x-transition:leave-end="opacity-0 transform scale-90" class="absolute mt-1 origin-top-right right-0 rounded-b shadow-lg text-xs w-48 z-50">
						<div class="py-1 rounded-b bg-white shadow-xs">
							<a href="/installation" class="block px-4 py-1 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
							{{ __('Getting Started') }}
							</a>
							<a href="/documentation" class="block px-4 py-1 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
							{{ __('Documentation') }}
							</a>
							<a href="/commands" class="block px-4 py-1 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
							{{ __('Commands') }}
							</a>
							<a href="https://github.com/FBNKCMaster/xTenant" class="block px-4 py-1 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
							{{ __('Contribute') }}
							</a>
							<a href="/about" class="block px-4 py-1 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
							{{ __('About') }}
							</a>
							<a href="{{ route('logout') }}" class="border-t block px-4 py-1 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
								{{ __('Logout') }}
								<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">@csrf</form>
							</a>
						</div>
					</div>
				</div>
			@endguest
			</div>
		</header>
<div class="col-md-3 mb-4 d-none d-md-block">
                <div class="lawyer-sidebar">

                    <h5 class="text-center mb-3" style="font-weight:700;">
                        {{ __('lawyer.nav.brand') }}
                    </h5>

                    <a href="{{ route('lawyer.dashboard') }}" class="{{ Route::is('lawyer.dashboard') ? 'active' : '' }}">
                        {{ __('lawyer.nav.dashboard') }}
                    </a>
                    <a href="{{ route('lawyer.my_cases') }}" class="{{ Route::is('lawyer.my_cases') ? 'active' : '' }}">
                        {{ __('lawyer.nav.my_cases') }}
                    </a>
                    {{-- <a href="{{ route('lawyer.notifications') }}"
                        class="{{ Route::is('lawyer.notifications') ? 'active' : '' }}">
                        {{ __('lawyer.nav.notifications') }}
                    </a>
                    <a href="{{ route('lawyer.messages') }}" class="{{ Route::is('lawyer.messages') ? 'active' : '' }}">
                        {{ __('lawyer.nav.messages') }}
                    </a> --}}
                    <a href="{{ route('lawyer.documents') }}" class="{{ Route::is('lawyer.documents') ? 'active' : '' }}">
                        {{ __('lawyer.nav.documents') }}
                    </a>
                    <a href="{{ route('lawyer.settings') }}" class="{{ Route::is('lawyer.settings') ? 'active' : '' }}">
                        {{ __('lawyer.nav.settings') }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="px-3 mt-2">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-danger w-100">{{ __('lawyer.nav.logout') }}</button>
                    </form>

                </div>
            </div>

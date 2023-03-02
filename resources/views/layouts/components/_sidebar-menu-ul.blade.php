<ul class="sidebar-menu text-capitalize">
    <li @class(['active' => request()->is('dashboard*')])>
        <a class="nav-link" href="{{ url('dashboard') }}">
            <i class="fas fa-chart-line"></i>
            <span>{{ __('dashboard') }}</span>
        </a>
    </li>

    <li class="menu-header">Data</li>

    <li @class(['active' => request()->is('materials*')])>
        <a class="nav-link" href="{{ url('materials') }}">
            <i class="fas fa-pallet"></i>
            <span>{{ __('materials') }}</span>
        </a>
    </li>

    @if (Auth::user()->hasRole('Manufacturer|Super Admin'))
        <li @class(['active' => request()->is('manufactures*')])>
            <a class="nav-link" href="{{ url('manufactures') }}">
                <i class="fas fa-boxes"></i>
                <span>{{ __('manufactures') }}</span>
            </a>
        </li>
    @endif

    <li @class(['active' => request()->is('manufactures*')])>
        <a class="nav-link" href="{{ url('products') }}">
            <i class="fas fa-box"></i>
            <span>{{ __('products') }}</span>
        </a>
    </li>



    <li class="menu-header">{{ __('report') }}</li>

    <li @class(['active' => request()->is('report/manufactures*')])>
        <a class="nav-link" href="{{ url('report/materials') }}">
            <i class="fas fa-file-alt"></i>
            <span>{{ __('materials') }}</span>
        </a>
    </li>

    <li @class(['active' => request()->is('report/manufactures*')])>
        <a class="nav-link" href="{{ url('report/manufactures') }}">
            <i class="fas fa-file-alt"></i>
            <span>{{ __('manufactures') }}</span>
        </a>
    </li>

    <li @class(['active' => request()->is('report/products*')])>
        <a class="nav-link" href="{{ url('report/products') }}">
            <i class="fas fa-file-alt"></i>
            <span>{{ __('products') }}</span>
        </a>
    </li>


    @if (Auth::user()->hasRole('Super Admin'))
        <li class="menu-header">{{ __('system') }}</li>

        <li @class(['active' => request()->is('report/users*')])>
            <a class="nav-link" href="{{ url('system/users') }}">
                <i class="fas fa-user-cog"></i>
                <span>{{ __('users') }}</span>
            </a>
        </li>

        {{-- <li class="{{ request()->is('system/ip-addr*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ url('system/ip-addr') }}">
                <i class="fas fa-network-wired"></i>
                <span>{{ __('IP Addresses') }}</span>
            </a>
        </li> --}}
    @endif
</ul>

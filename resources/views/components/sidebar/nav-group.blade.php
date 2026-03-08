<li class="nav-item has-submenu {{ $open ? 'open' : '' }}">

    <a class="nav-link "
       href="#"
       aria-expanded="{{ $open ? 'true' : 'false' }}"  data-sidebar-tooltip="{{ $label }}">

        {{-- Custom icon slot OR fallback icon class --}}
        @isset($icon)
            <i class="{{ $icon }}"></i>
        @else
            {{ $iconSlot ?? '' }}
        @endisset

        <span>{{ $label }}</span>
        <i class="ph-light ph-caret-down nav-arrow"></i>
    </a>

    <ul class="nav-submenu">
        {{ $slot }}
    </ul>

</li>
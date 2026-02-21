<li class="nav-item has-submenu {{ $open ? 'open active' : '' }}">

    <a class="nav-link {{ $open ? 'active' : '' }}"
       href="#"
       aria-expanded="{{ $open ? 'true' : 'false' }}">

        {{-- Custom icon slot OR fallback icon class --}}
        @isset($icon)
            <i class="{{ $icon }}"></i>
        @else
            {{ $iconSlot ?? '' }}
        @endisset

        <span>{{ $label }}</span>
        <i class="ph-light ph-caret-down nav-arrow"></i>
    </a>

    <ul class="nav-submenu {{ $open ? 'show' : '' }}">
        {{ $slot }}
    </ul>

</li>
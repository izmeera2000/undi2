<li class="nav-item">
    <a class="nav-link {{ $active ? 'active' : '' }}" href="{{ route($route) }}" data-sidebar-tooltip="{{ $label }}">

        @if(!empty($icon))
            <i class="{{ $icon }}"></i>
        @endif
        <span>{{ $label }}</span>

    </a>
</li>
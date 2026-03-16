<div class="page-header">
    <h1 class="page-title">
        @yield('title', 'Dashboard')
    </h1>

    <nav class="breadcrumb">
        <a href="{{ route('dashboard') }}" class="breadcrumb-item">UndiScope</a>
        @yield('breadcrumb')

        @if(!empty($crumbs))
            @foreach ($crumbs as $crumb)
                @if ($loop->last || empty($crumb['url']))
                    <span class="breadcrumb-item active">{{ $crumb['label'] }}</span>
                @else
                    <a href="{{ $crumb['url'] }}" class="breadcrumb-item">{{ $crumb['label'] }}</a>
                @endif
            @endforeach
        @else
            <span class="breadcrumb-item active">Dashboard</span>
        @endif




    </nav>
</div>
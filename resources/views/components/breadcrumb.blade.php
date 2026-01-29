<div class="pagetitle">
    <h1>{{ $title }}</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Home</a>
            </li>

            @foreach ($items as $item)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $item['label'] }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </nav>
</div>

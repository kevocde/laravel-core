<nav aria-label="breadcrumb d-flex align-items-center">
    <ol class="breadcrumb breadcrumb-custom mb-0">
        @foreach($breadcrumbs as $item)
            <li class="breadcrumb-item {{ (isset($item['link']) && request()->is($item['link'])) ? 'active' : '' }}">@if(isset($item['link']) && !$loop->last) <a href="{{ route($item['link']) }}">{{ $item['label'] }}</a> @else <span>{{ $item['label'] }}</span> @endif</li>
        @endforeach
    </ol>
</nav>
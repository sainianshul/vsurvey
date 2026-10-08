@props(['items' => []])

<ol {{ $attributes->merge(['class' => 'breadcrumb mb-3']) }} aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ti ti-home"></i></a></li>
    @foreach ($items as $item)
        @if (!$loop->last)
            <li class="breadcrumb-item"><a href="{{ $item['url'] ?? '#' }}">{{ $item['label'] }}</a></li>
        @else
            <li class="breadcrumb-item active" aria-current="page"><a href="#">{{ $item['label'] }}</a></li>
        @endif
    @endforeach
</ol>

@props(['title', 'pretitle' => ''])

<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col-auto">
            @if ($pretitle)
                <div class="page-pretitle mb-1">{{ $pretitle }}</div>
            @endif
            <h2 class="page-title">{{ $title }}</h2>
        </div>
    </div>
</div>
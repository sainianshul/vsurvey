{{-- _table-loader.blade.php --}}
@php $loaderId = $id ?? 'table-loader'; @endphp
<div id="{{ $loaderId }}" class="d-flex justify-content-center align-items-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading…</span>
    </div>
</div>

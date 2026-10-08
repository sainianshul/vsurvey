{{--
Reusable empty-state partial.
Usage: @include('admin.layouts.partials._table-empty', ['id' => 'patients-empty'])
The $id variable MUST match the ID you target in JS: $('#patients-empty').removeClass('d-none')
--}}
@php $emptyId = $id ?? 'table-empty'; @endphp
<div id="{{ $emptyId }}" class="d-none">
    <div class="empty py-5">
        <div class="empty-icon text-muted mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder-off" width="64" height="64" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                <path d="M8 4h1l3 3h7a2 2 0 0 1 2 2v8m-2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 1.189 -1.829"></path>
                <path d="M3 3l18 18"></path>
            </svg>
        </div>
        <p class="empty-title">No data found</p>
        <p class="empty-subtitle text-secondary">
            We couldn't find any records matching your criteria.
        </p>
    </div>
</div>
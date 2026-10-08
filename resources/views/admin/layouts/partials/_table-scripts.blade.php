{{--
DataTable CDN scripts + our reusable factory.
Usage: @include('admin.layouts.partials._table-scripts')
Uses @once so it only renders once, even if included by multiple partials.
--}}
@once
    @push('scripts')
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script src="{{ asset('js/admin-datatable.js') }}"></script>
    @endpush
@endonce

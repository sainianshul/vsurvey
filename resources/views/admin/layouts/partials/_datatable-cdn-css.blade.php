<link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Hide default search — we use our own */
    .dataTables_filter,
    .dt-search { display: none !important; }

    /* Clean table header */
    .table.dataTable > thead > tr > th {
        border-bottom-width: 1px;
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.04rem;
        color: var(--tblr-secondary);
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }

    /* Vertical align cells */
    .table.dataTable > tbody > tr > td {
        vertical-align: middle;
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }

    /* Subtle row hover */
    .table.dataTable > tbody > tr:hover {
        background-color: rgba(var(--tblr-primary-rgb), 0.02);
    }

    /* Pagination style — clean & aligned next/prev icons */
    .pagination,
    .dataTables_wrapper .pagination,
    .dt-container .pagination {
        margin-bottom: 0;
        display: flex;
        align-items: center;
        gap: 3px;
        list-style: none;
        padding: 0;
    }

    .pagination .page-item .page-link,
    .dataTables_wrapper .pagination .page-item .page-link,
    .dt-container .pagination .page-item .page-link,
    .dataTables_paginate .page-link,
    .dt-paging .page-link {
        border-radius: 6px !important;
        margin: 0 1px;
        font-size: 0.8125rem;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        min-width: 32px;
        height: 32px;
        padding: 0 8px !important;
        color: var(--tblr-secondary, #626976) !important;
        border: 1px solid var(--tblr-border-color, #e6e7e9) !important;
        background: var(--tblr-bg-surface, #ffffff) !important;
        text-decoration: none;
        line-height: 1 !important;
        cursor: pointer;
        box-shadow: none !important;
        outline: none !important;
        transition: all 0.15s ease-in-out;
    }

    .pagination .page-item .page-link:hover:not(.disabled),
    .dataTables_wrapper .pagination .page-item .page-link:hover:not(.disabled),
    .dt-container .pagination .page-item .page-link:hover:not(.disabled) {
        background-color: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.08) !important;
        color: var(--tblr-primary, #206bc4) !important;
        border-color: rgba(var(--tblr-primary-rgb, 32, 107, 196), 0.3) !important;
    }

    .pagination .page-item .page-link i,
    .dataTables_wrapper .pagination .page-item .page-link i,
    .dt-container .pagination .page-item .page-link i,
    .dt-paging .page-link i {
        font-size: 1.1rem !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        vertical-align: middle !important;
    }

    .pagination .page-item.active .page-link,
    .dataTables_wrapper .pagination .page-item.active .page-link,
    .dt-container .pagination .page-item.active .page-link {
        background-color: var(--tblr-primary, #206bc4) !important;
        border-color: var(--tblr-primary, #206bc4) !important;
        color: #ffffff !important;
        font-weight: 600;
    }

    .pagination .page-item.disabled .page-link,
    .dataTables_wrapper .pagination .page-item.disabled .page-link,
    .dt-container .pagination .page-item.disabled .page-link,
    .page-item:disabled .page-link {
        opacity: 0.45 !important;
        cursor: not-allowed !important;
        pointer-events: none !important;
        background: transparent !important;
        border-color: var(--tblr-border-color, #e6e7e9) !important;
    }

    .dataTables_wrapper .dataTables_info,
    .dt-container .dt-info {
        font-size: 0.8125rem;
        color: var(--tblr-secondary);
        padding-top: 0.85em;
    }

    /* Length menu styling */
    .dataTables_wrapper .dataTables_length select,
    .dt-container .dt-length select {
        font-size: 0.8125rem;
        border-radius: 6px;
        padding: 4px 24px 4px 8px;
        border-color: var(--tblr-border-color, #e6e7e9);
    }
</style>
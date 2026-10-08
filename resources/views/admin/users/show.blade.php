@extends('admin.layouts.app')

@section('title', ($user->name ?? $user->phone) . ' — User Profile')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <x-breadcrumb :items="[
                    ['label' => 'Users', 'url' => route('admin.users.index')],
                    ['label' => $user->name ?? $user->phone],
                ]" />
                <h2 class="page-title">{{ $user->name ?? $user->phone }}</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Back
                </a>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="ti ti-pencil me-1"></i>Edit User
                </a>

                {{-- Status Change Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-outline-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-settings me-1"></i>Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><h6 class="dropdown-header">Change Account Status</h6></li>
                        @foreach (\App\Models\User::getStatusList() as $value => $label)
                            @if($user->status != $value)
                                <li>
                                    <a class="dropdown-item status-change-btn" href="#"
                                       data-id="{{ $user->id }}" data-status="{{ $value }}">
                                        @if($value == \App\Models\User::STATUS_ACTIVE)
                                            <i class="ti ti-check me-2 text-success"></i>
                                        @elseif($value == \App\Models\User::STATUS_BLOCKED)
                                            <i class="ti ti-ban me-2 text-danger"></i>
                                        @elseif($value == \App\Models\User::STATUS_SUSPENDED)
                                            <i class="ti ti-alert-triangle me-2 text-warning"></i>
                                        @endif
                                        {{ $label }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Profile Header Card --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($user->profile_photo)
                        <span class="avatar avatar-xl rounded-circle" style="background-image: url({{ asset('storage/' . $user->profile_photo) }})"></span>
                    @else
                        <span class="avatar avatar-xl rounded-circle bg-primary-lt fs-2 fw-bold">
                            {{ mb_strtoupper(mb_substr($user->name ?? 'U', 0, 2)) }}
                        </span>
                    @endif
                </div>
                <div class="col">
                    <div class="d-flex align-items-center mb-1 flex-wrap gap-2">
                        <h2 class="mb-0 me-1">{{ $user->name ?? 'Unknown User' }}</h2>
                        <span class="badge bg-{{ $user->status_color }}-lt">
                            <i class="{{ $user->status_icon }} me-1"></i>{{ $user->status_name }}
                        </span>
                        @if($user->phone_verified_at)
                            <span class="badge bg-blue-lt"><i class="ti ti-circle-check me-1"></i>Phone Verified</span>
                        @endif
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-secondary small mt-1">
                        <span><i class="ti ti-phone me-1"></i>{{ $user->phone ?? 'N/A' }}</span>
                        @if($user->email)
                            <span><i class="ti ti-mail me-1"></i>{{ $user->email }}</span>
                        @endif
                        <span><i class="ti ti-map-pin me-1"></i>{{ $user->city ?? 'No City' }}@if($user->pincode), {{ $user->pincode }}@endif</span>
                        <span><i class="ti ti-clock me-1"></i>Joined {{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-primary-lt text-primary avatar">
                                <i class="ti ti-package"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ $user->products()->count() }}</div>
                            <div class="text-secondary">Products Listed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-indigo-lt text-indigo avatar">
                                <i class="ti ti-list-check"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ \App\Models\Requirement::where('user_id', $user->id)->count() }}</div>
                            <div class="text-secondary">Requirements</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-green-lt text-green avatar">
                                <i class="ti ti-bulb"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold fs-3">{{ \App\Models\Lead::where('seller_id', $user->id)->count() }}</div>
                            <div class="text-secondary">Leads Received</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="bg-cyan-lt text-cyan avatar">
                                <i class="ti ti-calendar"></i>
                            </span>
                        </div>
                        <div class="col">
                            <div class="fw-bold">
                                {{ $user->last_login_at ? $user->last_login_at->format('d M, Y') : 'Never' }}
                            </div>
                            <div class="text-secondary">
                                Last Login {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs Card --}}
    <div class="card mb-3">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-overview" role="tab">
                        <i class="ti ti-user me-1"></i>Overview
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-products" role="tab">
                        <i class="ti ti-package me-1"></i>Products
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-requirements" role="tab">
                        <i class="ti ti-list-check me-1"></i>Requirements
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-leads" role="tab">
                        <i class="ti ti-bulb me-1"></i>Leads / Enquiries
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-devices" role="tab">
                        <i class="ti ti-devices me-1"></i>Devices
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-recent-views" role="tab">
                        <i class="ti ti-history me-1"></i>Recent Views
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- Tab: Overview --}}
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <h3 class="mb-3">Personal Details</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Full Name</div>
                                    <div class="datagrid-content">{{ $user->name ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Phone Number</div>
                                    <div class="datagrid-content">
                                        {{ $user->phone ?? 'N/A' }}
                                        @if($user->phone_verified_at)
                                            <span class="badge bg-green-lt ms-1"><i class="ti ti-check me-1"></i>Verified</span>
                                        @else
                                            <span class="badge bg-red-lt ms-1">Unverified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Email Address</div>
                                    <div class="datagrid-content">{{ $user->email ?? 'Not provided' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Role</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-blue-lt">{{ $user->role_name }}</span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Account Status</div>
                                    <div class="datagrid-content">
                                        <span class="badge bg-{{ $user->status_color }}-lt">
                                            <i class="{{ $user->status_icon }} me-1"></i>{{ $user->status_name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Created By</div>
                                    <div class="datagrid-content">
                                        @if(empty($user->created_by))
                                            Self Registered (OTP)
                                        @else
                                            Created by Admin
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <h3 class="mb-3">Location & Address</h3>
                            <div class="datagrid">
                                <div class="datagrid-item">
                                    <div class="datagrid-title">City</div>
                                    <div class="datagrid-content">{{ $user->city ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Pincode</div>
                                    <div class="datagrid-content">{{ $user->pincode ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Latitude</div>
                                    <div class="datagrid-content">{{ $user->latitude ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Longitude</div>
                                    <div class="datagrid-content">{{ $user->longitude ?? 'N/A' }}</div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Location Updated</div>
                                    <div class="datagrid-content">
                                        {{ $user->location_updated_at ? $user->location_updated_at->format('d M Y, H:i') : 'Never' }}
                                    </div>
                                </div>
                                <div class="datagrid-item">
                                    <div class="datagrid-title">Joined Date</div>
                                    <div class="datagrid-content">{{ $user->created_at->format('d M Y, h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab: Products --}}
                <div class="tab-pane fade" id="tab-products" role="tabpanel">
                    <div class="d-flex justify-content-end mb-3 mt-3">
                        <a href="{{ route('admin.products.create', ['user_id' => $user->id]) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Add Product
                        </a>
                    </div>
                    <div class="table-responsive py-4">
                        <table class="table table-sm table-vcenter card-table" id="user-products-table" style="width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Condition</th>
                                    <th>Featured</th>
                                    <th>Status</th>
                                    <th>Stats</th>
                                    <th>Listed On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                
                {{-- Tab: Requirements --}}
                <div class="tab-pane fade" id="tab-requirements" role="tabpanel">
                    <div class="d-flex justify-content-end mb-3 mt-3">
                        <a href="{{ route('admin.requirements.create', ['user_id' => $user->id]) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Add Requirement
                        </a>
                    </div>
                    <div class="table-responsive py-4">
                        <table class="table table-sm table-vcenter card-table" id="user-requirements-table" style="width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Requirement</th>
                                    <th>Target Budget</th>
                                    <th>Quantity</th>
                                    <th>Status</th>
                                    <th>Posted On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                {{-- Tab: Leads / Enquiries --}}
                <div class="tab-pane fade" id="tab-leads" role="tabpanel">
                    <div class="table-responsive py-4">
                        <table class="table table-sm table-vcenter card-table" id="user-leads-table" style="width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>User Name</th>
                                    <th>Product/Requirement</th>
                                    <th>Source</th>
                                    <th>Temperature</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                {{-- Tab: Devices (Sanctum tokens) --}}
                <div class="tab-pane fade" id="tab-devices" role="tabpanel">
                    @php
                        $tokens = $user->tokens()->latest()->get();
                    @endphp
                    @if($tokens->count() > 0)
                        <div class="table-responsive py-4">
                            <table class="table table-sm table-vcenter" style="font-size: 14px;">
                                <thead>
                                    <tr>
                                        <th>Device</th>
                                        <th>IP Address</th>
                                        <th>FCM Token</th>
                                        <th>Last Used</th>
                                        <th>Created</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tokens as $token)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm bg-secondary-lt rounded me-2">
                                                        <i class="ti ti-device-mobile"></i>
                                                    </span>
                                                    <div>
                                                        <div class="fw-semibold">{{ $token->device_name ?? $token->name ?? 'Unknown Device' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-secondary">{{ $token->ip_address ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($token->fcm_token)
                                                    <span class="badge bg-green-lt border-transparent"><i class="ti ti-bell me-1"></i>Active</span>
                                                @else
                                                    <span class="badge bg-secondary-lt border-transparent">Not Set</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-secondary">{{ $token->last_used_at ? $token->last_used_at->format('d M Y, H:i') : 'Never' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-secondary">{{ $token->created_at->format('d M Y, H:i') }}</span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger btn-revoke-token"
                                                    data-token-id="{{ $token->id }}"
                                                    data-bs-toggle="tooltip" title="Revoke Session">
                                                    <i class="ti ti-x me-1"></i> Revoke
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty py-5">
                            <div class="empty-icon">
                                <i class="ti ti-devices text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <p class="empty-title">No Active Devices</p>
                            <p class="empty-subtitle text-secondary">
                                This user does not have any active sessions / logged-in devices.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Tab: Recent Views --}}
                <div class="tab-pane fade" id="tab-recent-views" role="tabpanel">
                    <div class="table-responsive py-4">
                        <table class="table table-sm table-vcenter card-table" id="user-recent-views-table" style="width: 100%; font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Viewed On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Comments / Admin Notes --}}
    <x-comments type="{{ \App\Models\Comment::TYPE_USER }}" :model-id="$user->id" />

@endsection

    {{-- Product Status Update Modal --}}
    <div class="modal modal-blur fade" id="status-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="status-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Product Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="status-modal-id">
                        <div class="mb-3">
                            <label class="form-label">Select Status</label>
                            <select id="status-modal-select" class="form-select">
                                @foreach (\App\Models\Product::getStatusList() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Product Featured Update Modal --}}
    <div class="modal modal-blur fade" id="featured-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="featured-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Featured Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="featured-modal-id">
                        <div class="mb-3">
                            <label class="form-label">Is Featured?</label>
                            <select id="featured-modal-select" class="form-select">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Requirement Status Update Modal --}}
    <div class="modal modal-blur fade" id="status-req-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="status-req-modal-form">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Requirement Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="status-req-modal-id">
                        <div class="mb-3">
                            <label class="form-label">Select Status</label>
                            <select id="status-req-modal-select" class="form-select">
                                @foreach (\App\Models\Requirement::getStatusList() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('datatables_css')
    @include('admin.layouts.partials._datatable-cdn-css')
@endpush

@push('datatables_js')
    @include('admin.layouts.partials._datatable-cdn-js')
@endpush

@push('scripts')
<script>
    $(function() {
        // Status change from dropdown
        $(document).on('click', '.status-change-btn', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let status = $(this).data('status');

            Swal.fire({
                title: 'Change User Status?',
                text: 'Are you sure you want to change this user\'s status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Change',
                customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: '/admin/users/' + id + '/status',
                    type: 'POST',
                    data: { status: status, _token: '{{ csrf_token() }}' },
                    success: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Status updated' });
                        setTimeout(function () { location.reload(); }, 1500);
                    },
                    error: function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                    }
                });
            });
        });

        // Revoke token/device
        $(document).on('click', '.btn-revoke-token', function () {
            let tokenId = $(this).data('token-id');
            Swal.fire({
                title: 'Revoke Device?',
                text: 'This will forcefully logout the user from this device.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Revoke',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                $.post('/admin/users/{{ $user->id }}/revoke-token', { token_id: tokenId, _token: '{{ csrf_token() }}' })
                    .done(function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Device session revoked' });
                        setTimeout(function () { location.reload(); }, 1500);
                    })
                    .fail(function () {
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Something went wrong' });
                    });
            });
        });

        // DataTable Initialization for Products
        let table = $('#user-products-table').DataTable({
            processing: false,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: '{{ route('admin.users.products.data', $user->id) }}',
            columns: [
                { data: 'title', name: 'title' },
                { data: 'price', name: 'price' },
                { data: 'condition', name: 'condition', orderable: false, searchable: false },
                { data: 'is_featured', name: 'is_featured', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'stats', name: 'stats', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[6, 'desc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            initComplete: function() {
                // Remove tab data refresh if already loaded once
            }
        });

        // Refresh datatable when tab is shown
        $('a[data-bs-toggle="tab"][href="#tab-products"]').on('shown.bs.tab', function (e) {
            table.columns.adjust();
            if (table.responsive) table.responsive.recalc();
        });
        
        // DataTable Initialization for Requirements
        let reqTable = $('#user-requirements-table').DataTable({
            processing: false,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: '{{ route('admin.users.requirements.data', $user->id) }}',
            columns: [
                { data: 'title', name: 'title' },
                { data: 'target_budget', name: 'target_budget' },
                { data: 'quantity', name: 'quantity' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[4, 'desc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            initComplete: function() {
            }
        });

        // Refresh datatable when tab is shown
        $('a[data-bs-toggle="tab"][href="#tab-requirements"]').on('shown.bs.tab', function (e) {
            reqTable.columns.adjust();
            if (reqTable.responsive) reqTable.responsive.recalc();
        });

        // DataTable Initialization for Leads
        let leadsTable = $('#user-leads-table').DataTable({
            processing: false,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: '{{ route('admin.users.leads.data', $user->id) }}',
            columns: [
                { data: 'buyer_id', name: 'buyer.name' },
                { data: 'item', name: 'item', orderable: false, searchable: false },
                { data: 'source', name: 'source', orderable: false, searchable: false },
                { data: 'temperature', name: 'temperature', orderable: false, searchable: false },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[5, 'desc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            initComplete: function() {
            }
        });

        // Refresh datatable when tab is shown
        $('a[data-bs-toggle="tab"][href="#tab-leads"]').on('shown.bs.tab', function (e) {
            leadsTable.columns.adjust();
            if (leadsTable.responsive) leadsTable.responsive.recalc();
        });

        // DataTable Initialization for Recent Views
        let recentViewsTable = $('#user-recent-views-table').DataTable({
            processing: false,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: '{{ route('admin.users.recent-views.data', $user->id) }}',
            columns: [
                { data: 'title', name: 'product.title' },
                { data: 'price', name: 'product.price', orderable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
            ],
            order: [[2, 'desc']],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            initComplete: function() {}
        });

        $('a[data-bs-toggle="tab"][href="#tab-recent-views"]').on('shown.bs.tab', function (e) {
            recentViewsTable.columns.adjust();
            if (recentViewsTable.responsive) recentViewsTable.responsive.recalc();
        });

        // Requirement Actions
        // Open Requirement Status Modal
        $(document).on('click', '.status-req-modal-btn', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let currentStatus = $(this).data('status');
            
            $('#status-req-modal-id').val(id);
            $('#status-req-modal-select').val(currentStatus);
            $('#status-req-modal').modal('show');
        });

        // Submit Requirement Status Modal
        $('#status-req-modal-form').on('submit', function (e) {
            e.preventDefault();
            let id = $('#status-req-modal-id').val();
            let status = $('#status-req-modal-select').val();
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();
            
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...').prop('disabled', true);

            $.ajax({
                url: '/admin/requirements/' + id + '/status',
                type: 'POST',
                data: { status: status, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    $('#status-req-modal').modal('hide');
                    reqTable.ajax.reload(null, false);
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Status updated' });
                },
                error: function () {
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                },
                complete: function () {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        $(document).on('click', '.btn-req-delete', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Delete Requirement?',
                text: 'Are you sure you want to delete "' + name + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                
                $.ajax({
                    url: '/admin/requirements/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        reqTable.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Requirement deleted' });
                    }
                });
            });
        });

        // Product Actions
        // Open Status Modal
        $(document).on('click', '.status-modal-btn', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let currentStatus = $(this).data('status');
            
            $('#status-modal-id').val(id);
            $('#status-modal-select').val(currentStatus);
            $('#status-modal').modal('show');
        });

        // Submit Status Modal
        $('#status-modal-form').on('submit', function (e) {
            e.preventDefault();
            let id = $('#status-modal-id').val();
            let status = $('#status-modal-select').val();
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();
            
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...').prop('disabled', true);

            $.ajax({
                url: '/admin/products/' + id + '/status',
                type: 'POST',
                data: { status: status, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    $('#status-modal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Status updated' });
                },
                error: function () {
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                },
                complete: function () {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Open Featured Modal
        $(document).on('click', '.featured-modal-btn', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let isFeatured = $(this).data('featured');
            
            $('#featured-modal-id').val(id);
            $('#featured-modal-select').val(isFeatured);
            $('#featured-modal').modal('show');
        });

        // Submit Featured Modal
        $('#featured-modal-form').on('submit', function (e) {
            e.preventDefault();
            let id = $('#featured-modal-id').val();
            let featured = $('#featured-modal-select').val();
            let submitBtn = $(this).find('button[type="submit"]');
            let originalText = submitBtn.html();
            
            submitBtn.html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...').prop('disabled', true);

            $.ajax({
                url: '/admin/products/' + id + '/featured',
                type: 'POST',
                data: { is_featured: featured, _token: '{{ csrf_token() }}' },
                success: function (res) {
                    $('#featured-modal').modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Updated' });
                },
                error: function () {
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to update' });
                },
                complete: function () {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });
        
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Delete Product?',
                text: 'Are you sure you want to delete "' + name + '"? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light ms-2' },
                buttonsStyling: false,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                
                $.ajax({
                    url: '/admin/products/' + id,
                    type: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (res) {
                        table.ajax.reload(null, false);
                        Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'success', title: 'Product deleted' });
                    }
                });
            });
        });
    });
</script>
@endpush

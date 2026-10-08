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
                    </div>
                    <div class="d-flex flex-wrap gap-3 text-secondary small mt-1">
                        <span><i class="ti ti-phone me-1"></i>{{ $user->phone ?? 'N/A' }}</span>
                        @if($user->email)
                            <span><i class="ti ti-mail me-1"></i>{{ $user->email }}</span>
                        @endif
                        <span><i class="ti ti-clock me-1"></i>Joined {{ $user->created_at->format('d M Y') }}</span>
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
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">
                {{-- Tab: Overview --}}
                <div class="tab-pane fade show active" id="tab-overview" role="tabpanel">
                    <div class="row g-4 mb-4">
                        <div class="col-lg-12">
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
                                            Self Registered
                                        @else
                                            Created by Admin
                                        @endif
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
            </div>
        </div>
    </div>

@endsection

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
    });
</script>
@endpush

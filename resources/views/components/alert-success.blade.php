@if (session('success'))
    <div class="alert bg-light-success border border-success border-dashed d-flex align-items-center p-3 mb-6 rounded">
        <i class="ti ti-check-circle fs-3 text-success me-3"></i>
        <div class="d-flex flex-column">
            <span class="text-success fw-medium small">{{ session('success') }}</span>
        </div>
        <button type="button" class="btn btn-icon ms-auto m-0 p-0" data-bs-dismiss="alert" style="width: 24px; height: 24px;">
            <i class="ti ti-x h4 text-success"></i>
        </button>
    </div>
@endif

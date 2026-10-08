<?php

namespace App\DataTables\Banners;

use App\Models\Banner;
use Yajra\DataTables\Services\DataTable;

class BannerDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->editColumn('image', function (Banner $banner) {
                if ($banner->image) {
                    return '<img src="' . url('storage/' . $banner->image) . '" class="avatar avatar-sm border">';
                }
                return '<span class="avatar avatar-sm border bg-light text-muted"><i class="ti ti-photo"></i></span>';
            })
            ->editColumn('type', function (Banner $banner) {
                return $banner->type_name;
            })
            ->addColumn('status', function (Banner $banner) {
                $color = match($banner->status) {
                    'active' => 'green',
                    'inactive' => 'secondary',
                    'draft' => 'warning',
                    default => 'secondary'
                };
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $banner->id . '" data-status="' . $banner->status . '">
                        ' . ucfirst($banner->status) . '
                    </a>
                ';
            })
            ->addColumn('actions', function (Banner $banner) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-warning btn-edit"
                            data-id="' . $banner->id . '"
                            data-name="' . e($banner->name) . '"
                            data-type="' . $banner->type . '"
                            data-reference_id="' . $banner->reference_id . '"
                            data-status="' . $banner->status . '"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </button>

                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $banner->id . '" data-name="' . e($banner->name) . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['image', 'status', 'actions']);
    }

    public function query(Banner $model)
    {
        return $model->newQuery()->latest();
    }

    public function filename(): string
    {
        return 'Banners_' . date('Y_m_d_His');
    }
}

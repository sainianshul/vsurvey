<?php

namespace App\DataTables\Requirements;

use App\Models\Requirement;
use Yajra\DataTables\Services\DataTable;

class RequirementDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('title', function($query, $keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            })
            ->editColumn('title', function (Requirement $requirement) {
                $imageUrl = ($requirement->images->isNotEmpty()) ? $requirement->images->first()->url : null;
                $showUrl = route('admin.requirements.show', $requirement->id);
                $imageHtml = $imageUrl 
                    ? '<a href="' . $showUrl . '" class="d-block"><div class="avatar avatar-sm me-2" style="background-image: url(' . e($imageUrl) . ')"></div></a>'
                    : '<a href="' . $showUrl . '" class="d-block"><div class="avatar avatar-sm me-2 bg-light text-muted"><i class="ti ti-photo"></i></div></a>';
                
                return '
                    <div class="d-flex align-items-center">
                        ' . $imageHtml . '
                        <a href="' . $showUrl . '" class="fw-semibold text-truncate d-block text-reset text-decoration-none" style="max-width: 150px;">' . e($requirement->title) . '</a>
                    </div>
                ';
            })
            ->addColumn('buyer', function (Requirement $requirement) {
                if (!$requirement->user) return '<span class="text-muted">—</span>';
                $url = route('admin.users.show', $requirement->user->id);
                return '<a href="' . $url . '" class="text-decoration-none">' . e($requirement->user->name) . '</a>';
            })
            ->addColumn('category', function (Requirement $requirement) {
                return $requirement->category ? e($requirement->category->name) : '<span class="text-muted">—</span>';
            })
            ->editColumn('target_budget', function (Requirement $requirement) {
                return $requirement->target_budget ? '₹' . number_format($requirement->target_budget, 2) : '<span class="text-muted">—</span>';
            })
            ->addColumn('quantity', function (Requirement $requirement) {
                return $requirement->quantity;
            })
            ->addColumn('city', function (Requirement $requirement) {
                return $requirement->city ? e($requirement->city) : '<span class="text-muted">—</span>';
            })
            ->addColumn('status', function (Requirement $requirement) {
                $statusColors = [
                    1 => 'success', // OPEN
                    2 => 'info',    // FULFILLED
                    3 => 'secondary', // CLOSED
                    4 => 'warning',  // EXPIRED
                ];
                $color = $statusColors[$requirement->status] ?? 'secondary';
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $requirement->id . '" data-status="' . $requirement->status . '">
                        <i class="ti ti-circle-filled fs-5 me-1"></i>' . e($requirement->status_name) . '
                    </a>
                ';
            })
            ->editColumn('created_at', function (Requirement $requirement) {
                return $requirement->created_at ? $requirement->created_at->format('d M Y') : '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function (Requirement $requirement) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . route('admin.requirements.show', $requirement->id) . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View">
                            <i class="ti ti-eye"></i>
                        </a>
                        <a href="' . route('admin.requirements.edit', $requirement->id) . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </a>
                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $requirement->id . '" data-name="' . e($requirement->title) . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'title',
                'buyer',
                'category',
                'target_budget',
                'city',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(Requirement $model)
    {
        $query = $model->newQuery()->with(['user', 'category', 'images']);

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('category_id')) {
            $categoryId = request('category_id');
            $category = \App\Models\Category::with('children')->find($categoryId);
            if ($category) {
                $categoryIds = array_merge([$categoryId], $category->getAllChildrenIds());
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $categoryId);
            }
        }
        
        if (request()->filled('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        if (request()->filled('created_date')) {
            $query->whereDate('created_at', request('created_date'));
        }

        $query->latest();

        return $query;
    }

    public function filename(): string
    {
        return 'Requirements_' . date('Y_m_d_His');
    }
}

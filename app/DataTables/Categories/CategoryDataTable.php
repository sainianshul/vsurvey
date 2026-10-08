<?php

namespace App\DataTables\Categories;

use App\Models\Category;
use Yajra\DataTables\Services\DataTable;

class CategoryDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('name', function($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('slug', 'like', "%{$keyword}%");
            })
            ->editColumn('name', function (Category $category) {
                $showUrl = route('admin.categories.show', $category->id);
                return '
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">' . $category->icon_html . '</span>
                        <div class="fw-semibold">
                            <a href="' . $showUrl . '" class="text-reset text-decoration-none">' . e($category->name) . '</a>
                        </div>
                    </div>
                ';
            })
            ->addColumn('parent', function (Category $category) {
                if ($category->parent) {
                    $parentUrl = route('admin.categories.show', $category->parent->id);
                    return '<a href="' . $parentUrl . '" class="text-reset text-decoration-none">' . e($category->parent->name) . '</a>';
                }
                return '<span class="text-muted">—</span>';
            })
            ->editColumn('sort_order', function (Category $category) {
                return '<span class="text-secondary">' . $category->sort_order . '</span>';
            })
            ->addColumn('is_featured', function (Category $category) {
                $color = $category->is_featured ? 'yellow' : 'secondary';
                $text = $category->is_featured ? 'Yes' : 'No';
                $icon = $category->is_featured ? 'ti ti-star-filled' : 'ti ti-star';
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' featured-modal-btn text-decoration-none" data-id="' . $category->id . '" data-featured="' . $category->is_featured . '">
                        <i class="' . $icon . ' me-1"></i>' . $text . '
                    </a>
                ';
            })

            ->addColumn('status', function (Category $category) {
                $color = $category->status_color;
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $category->id . '" data-status="' . $category->is_active . '">
                        <i class="' . $category->status_icon . ' me-1"></i>' . e($category->status_name) . '
                    </a>
                ';
            })
            ->addColumn('actions', function (Category $category) {
                $showUrl = route('admin.categories.show', $category->id);
                $editUrl = route('admin.categories.edit', $category->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $showUrl . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View">
                            <i class="ti ti-eye"></i>
                        </a>

                        <a href="' . $editUrl . '"
                            class="btn btn-icon btn-sm btn-outline-warning"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </a>

                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $category->id . '" data-name="' . e($category->name) . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'name',
                'parent',
                'sort_order',
                'is_featured',
                'status',
                'actions',
            ]);
    }

    public function query(Category $model)
    {
        $query = $model->newQuery()->with('parent');

        if (request()->filled('status')) {
            $query->where('is_active', request('status'));
        }

        if (request()->filled('is_featured')) {
            $query->where('is_featured', request('is_featured'));
        }

        if (request()->filled('parent_id')) {
            $parentId = request('parent_id');
            if ($parentId === 'root') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        // Default order by sort_order
        $query->orderBy('sort_order');

        return $query;
    }

    public function filename(): string
    {
        return 'Categories_' . date('Y_m_d_His');
    }
}

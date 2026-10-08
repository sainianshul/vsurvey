<?php

namespace App\DataTables\Users;

use App\Models\Requirement;
use Yajra\DataTables\Services\DataTable;

class UserRequirementsDataTable extends DataTable
{
    public $userId;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('title', function($query, $keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->editColumn('title', function (Requirement $requirement) {
                $imageUrl = $requirement->images->first() ? $requirement->images->first()->image_path : null;
                $showUrl = route('admin.requirements.show', $requirement->id);
                $imageHtml = $imageUrl 
                    ? '<a href="' . $showUrl . '" class="d-block"><div class="avatar avatar-sm me-2" style="background-image: url(' . asset('storage/' . $imageUrl) . ')"></div></a>'
                    : '<a href="' . $showUrl . '" class="d-block"><div class="avatar avatar-sm me-2 bg-light text-muted"><i class="ti ti-photo"></i></div></a>';
                
                return '
                    <div class="d-flex align-items-center">
                        ' . $imageHtml . '
                        <div>
                            <a href="' . $showUrl . '" class="fw-semibold text-truncate d-block text-reset text-decoration-none" style="max-width: 150px;">' . e($requirement->title) . '</a>
                        </div>
                    </div>
                ';
            })
            ->editColumn('target_budget', function (Requirement $requirement) {
                return $requirement->target_budget ? '₹' . number_format($requirement->target_budget, 2) : '<span class="text-muted">—</span>';
            })
            ->addColumn('status', function (Requirement $requirement) {
                $statusHtml = '';
                if ($requirement->status == Requirement::STATUS_OPEN) {
                    $statusHtml = '<span class="badge badge-outline text-green">Open</span>';
                } elseif ($requirement->status == Requirement::STATUS_FULFILLED) {
                    $statusHtml = '<span class="badge badge-outline text-blue">Fulfilled</span>';
                } elseif ($requirement->status == Requirement::STATUS_CLOSED) {
                    $statusHtml = '<span class="badge badge-outline text-secondary">Closed</span>';
                } elseif ($requirement->status == Requirement::STATUS_EXPIRED) {
                    $statusHtml = '<span class="badge badge-outline text-red">Expired</span>';
                }
                
                return '
                    <a href="javascript:void(0)" class="status-req-modal-btn text-decoration-none" data-id="' . $requirement->id . '" data-status="' . $requirement->status . '">
                        ' . $statusHtml . '
                    </a>
                ';
            })
            ->editColumn('created_at', function (Requirement $requirement) {
                return $requirement->created_at ? $requirement->created_at->format('d M Y') : '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function (Requirement $requirement) {
                $showUrl = route('admin.requirements.show', $requirement->id);
                $editUrl = route('admin.requirements.edit', $requirement->id);

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
                            class="btn btn-icon btn-sm btn-outline-danger btn-req-delete"
                            data-id="' . $requirement->id . '" data-name="' . e($requirement->title) . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'title',
                'target_budget',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(Requirement $model)
    {
        $query = $model->newQuery()
            ->with(['category', 'images'])
            ->where('user_id', $this->userId);

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        $query->latest();

        return $query;
    }
    
    public function html()
    {
        return $this->builder()
            ->setTableId('user-requirements-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(4, 'desc') // Order by date by default
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'createdRow' => "function(row, data, dataIndex) { $(row).addClass('small'); }",
            ]);
    }
    
    protected function getColumns()
    {
        return [
            ['data' => 'title', 'name' => 'title', 'title' => 'Requirement'],
            ['data' => 'target_budget', 'name' => 'target_budget', 'title' => 'Target Budget'],
            ['data' => 'quantity', 'name' => 'quantity', 'title' => 'Quantity'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Posted On', 'searchable' => false],
            ['data' => 'actions', 'name' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    public function filename(): string
    {
        return 'UserRequirements_' . date('Y_m_d_His');
    }
}

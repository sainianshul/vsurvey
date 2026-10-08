<?php

namespace App\DataTables\Feedbacks;

use App\Models\Feedback;
use Yajra\DataTables\Services\DataTable;

class FeedbackDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('user_id', function($query, $keyword) {
                // If there is a user relation, search by user name. Assuming user() relation exists or just search by user_id
                // Adding whereHas just in case, but let's stick to simple searching first if relation doesn't exist
                $query->where('user_id', 'like', "%{$keyword}%");
            })
            ->editColumn('feedback', function (Feedback $feedback) {
                return '<span class="text-truncate d-inline-block" style="max-width: 400px;" title="' . e($feedback->feedback) . '">' . e($feedback->feedback) . '</span>';
            })
            ->editColumn('user_id', function (Feedback $feedback) {
                // If we want to show user ID or user Name. The migration had `foreignId('user_id')`.
                return $feedback->user_id; // Will replace with relation if it exists, let's keep it simple
            })
            ->addColumn('status', function (Feedback $feedback) {
                $color = match($feedback->status) {
                    'active' => 'green',
                    'inactive' => 'secondary',
                    'draft' => 'warning',
                    default => 'secondary'
                };
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $feedback->id . '" data-status="' . $feedback->status . '">
                        ' . ucfirst($feedback->status) . '
                    </a>
                ';
            })
            ->addColumn('actions', function (Feedback $feedback) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-warning btn-edit"
                            data-id="' . $feedback->id . '"
                            data-user_id="' . $feedback->user_id . '"
                            data-feedback="' . e($feedback->feedback) . '"
                            data-status="' . $feedback->status . '"
                            data-bs-toggle="tooltip" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </button>

                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $feedback->id . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['feedback', 'status', 'actions']);
    }

    public function query(Feedback $model)
    {
        return $model->newQuery()->latest();
    }

    public function filename(): string
    {
        return 'Feedbacks_' . date('Y_m_d_His');
    }
}

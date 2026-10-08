<?php

namespace App\DataTables\Users;

use App\Models\User;
use Yajra\DataTables\Services\DataTable;

class DeletedUserDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('name', function($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('name', function (User $user) {
                return '
                    <div class="d-flex align-items-center">
                        <span class="avatar avatar-sm me-2">' . $user->avatar_html . '</span>
                        <div>
                            <div class="fw-semibold text-decoration-line-through text-muted">' . e($user->name ?? 'Unknown') . '</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('phone', function (User $user) {
                return '<span class="text-secondary fw-semibold">' . e($user->phone ?? '—') . '</span>';
            })
            ->addColumn('deleted_at', function (User $user) {
                return '<div class="text-danger">' . $user->deleted_at->format('d M Y, H:i') . '</div>';
            })
            ->addColumn('actions', function (User $user) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button"
                            class="btn btn-sm btn-outline-success btn-restore"
                            data-id="' . $user->id . '"
                            data-bs-toggle="tooltip" title="Restore User">
                            <i class="ti ti-rotate-clockwise me-1"></i> Restore
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['name', 'phone', 'deleted_at', 'actions']);
    }

    public function query(User $model)
    {
        return $model->newQuery()
            ->onlyTrashed()
            ->where('role', User::ROLE_USER)
            ->select('users.*')
            ->latest('deleted_at');
    }

    public function filename(): string
    {
        return 'Deleted_Users_' . date('Y_m_d_His');
    }
}

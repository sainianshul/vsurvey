<?php

namespace App\DataTables\Users;

use App\Models\User;
use Yajra\DataTables\Services\DataTable;

class BlockedUserDataTable extends DataTable
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
                            <div class="fw-semibold">' . e($user->name ?? 'Unknown') . '</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('phone', function (User $user) {
                return '<span class="text-secondary fw-semibold">' . e($user->phone ?? '—') . '</span>';
            })
            ->addColumn('status', function (User $user) {
                return '<span class="badge bg-danger-lt"><i class="ti ti-ban me-1"></i>Blocked</span>';
            })
            ->addColumn('last_login_at', function (User $user) {
                return $user->last_login_at ? '<div class="text-secondary">' . $user->last_login_at->format('d M Y, H:i') . '</div>' : '<span class="text-secondary">Never</span>';
            })
            ->editColumn('created_at', function (User $user) {
                return '<div class="text-secondary">' . $user->created_at->format('d M Y') . '</div>';
            })
            ->addColumn('actions', function (User $user) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button"
                            class="btn btn-sm btn-outline-success btn-unblock"
                            data-id="' . $user->id . '"
                            data-bs-toggle="tooltip" title="Unblock User">
                            <i class="ti ti-check me-1"></i> Unblock
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['name', 'phone', 'status', 'last_login_at', 'created_at', 'actions']);
    }

    public function query(User $model)
    {
        return $model->newQuery()
            ->where('role', User::ROLE_USER)
            ->where('status', User::STATUS_BLOCKED)
            ->select('users.*')
            ->latest();
    }

    public function filename(): string
    {
        return 'Blocked_Users_' . date('Y_m_d_His');
    }
}

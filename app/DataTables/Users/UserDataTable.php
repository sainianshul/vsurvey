<?php

namespace App\DataTables\Users;

use App\Models\User;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)

            // ── User Info ─────────────────────────────────────────────
            ->filterColumn('name', function($query, $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('email', 'like', "%{$keyword}%")
                      ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('name', function (User $user) {
                $showUrl = route('admin.users.show', $user->id);
                return '
                    <div class="d-flex align-items-center">
                        <a href="' . $showUrl . '" class="d-block"><span class="avatar avatar-sm me-2">' . $user->avatar_html . '</span></a>
                        <div>
                            <a href="' . $showUrl . '" class="fw-semibold text-reset text-decoration-none">' . e($user->name ?? 'Unknown') . '</a>
                        </div>
                    </div>
                ';
            })
            
            ->addColumn('phone', function (User $user) {
                return '<span class="text-secondary fw-semibold">' . e($user->phone ?? '—') . '</span>';
            })

            // ── Status ───────────────────────────────────────────────
            ->addColumn('status', function (User $user) {
                $color = $user->status_color;
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $user->id . '" data-status="' . $user->status . '">
                        <i class="' . $user->status_icon . ' me-1"></i>' . e($user->status_name) . '
                    </a>
                ';
            })

            // ── Last Login ───────────────────────────────────────────
            ->addColumn('last_login_at', function (User $user) {
                if (!$user->last_login_at) {
                    return '<span class="text-secondary">Never</span>';
                }
                return '<div class="text-secondary">' . $user->last_login_at->format('d M Y, H:i') . '</div>';
            })

            // ── Joined Date ──────────────────────────────────────────
            ->editColumn('created_at', function (User $user) {
                return '<div class="text-secondary">' . $user->created_at->format('d M Y') . '</div>';
            })

            // ── Actions ──────────────────────────────────────────────
            ->addColumn('actions', function (User $user) {
                $viewUrl = route('admin.users.show', $user->id);
                $editUrl = route('admin.users.edit', $user->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
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
                            data-id="' . $user->id . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'name',
                'phone',
                'status',
                'last_login_at',
                'created_at',
                'actions',
            ]);
    }

    public function query(User $model)
    {
        $query = $model->newQuery()
            ->where('role', User::ROLE_USER)
            ->select('users.*')
            ->latest();

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'Users_' . date('Y_m_d_His');
    }
}

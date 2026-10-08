<?php

namespace App\DataTables\CallLogs;

use App\Models\CallLog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Services\DataTable;

class CallLogDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->filterColumn('phone_number', function($query, $keyword) {
                $query->where('phone_number', 'like', "%{$keyword}%");
            })
            ->editColumn('user_id', function ($log) {
                $name = $log->user ? $log->user->name : 'System/Webhook';
                $avatar = $log->user ? '<span class="avatar avatar-sm me-2" style="background-image: url(https://ui-avatars.com/api/?name='.urlencode($name).')"></span>' : '<span class="avatar avatar-sm me-2 bg-azure-lt">API</span>';
                return '
                    <div class="d-flex align-items-center">
                        ' . $avatar . '
                        <div>
                            <div class="fw-semibold text-reset text-decoration-none">' . e($name) . '</div>
                        </div>
                    </div>
                ';
            })
            ->editColumn('phone_number', function ($log) {
                return '<span class="text-secondary fw-semibold">' . e($log->phone_number ?? '—') . '</span>';
            })
            ->editColumn('call_timing', function ($log) {
                $timing = $log->call_timing ? $log->call_timing->format('d M Y, H:i') : $log->created_at->format('d M Y, H:i');
                return '<div class="text-secondary">' . $timing . '</div>';
            })
            ->editColumn('call_duration', function ($log) {
                return '<span class="text-secondary">' . e($log->call_duration ?? '—') . '</span>';
            })
            ->editColumn('sentiment', function ($log) {
                $sentiment = strtolower($log->sentiment);
                if ($sentiment == 'positive') {
                    $color = 'success';
                    $icon = 'ti-mood-smile';
                } elseif ($sentiment == 'negative') {
                    $color = 'danger';
                    $icon = 'ti-mood-sad';
                } else {
                    $color = 'secondary';
                    $icon = 'ti-mood-empty';
                }
                
                return '
                    <span class="badge badge-outline text-' . $color . ' fs-5">
                        <i class="ti ' . $icon . ' me-1"></i>' . ucfirst($log->sentiment ?? 'Neutral') . '
                    </span>
                ';
            })
            ->editColumn('is_success', function ($log) {
                if ($log->is_success) {
                    return '<span class="badge bg-green-lt"><i class="ti ti-check me-1"></i>Success</span>';
                }
                return '<span class="badge bg-red-lt"><i class="ti ti-x me-1"></i>Failed</span>';
            })
            ->addColumn('actions', function ($log) {
                $viewUrl = route('admin.call-logs.show', $log->id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $viewUrl . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View Details">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns([
                'user_id',
                'phone_number',
                'call_timing',
                'call_duration',
                'sentiment',
                'is_success',
                'actions',
            ]);
    }

    public function query(CallLog $model): QueryBuilder
    {
        $query = $model->newQuery()->with('user')->latest('id');

        if (request()->filled('sentiment')) {
            $query->where('sentiment', request('sentiment'));
        }

        return $query;
    }

    public function filename(): string
    {
        return 'CallLog_' . date('Y_m_d_His');
    }
}

<?php

namespace App\DataTables\CallLogs;

use App\Models\CallLog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CallLogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('user_id', function ($log) {
                return $log->user ? $log->user->name : 'System/Webhook';
            })
            ->editColumn('call_timing', function ($log) {
                return $log->call_timing ? $log->call_timing->format('d M Y, h:i A') : $log->created_at->format('d M Y, h:i A');
            })
            ->editColumn('sentiment', function ($log) {
                $sentiment = strtolower($log->sentiment);
                if ($sentiment == 'positive') {
                    return '<span class="badge bg-success">Positive</span>';
                } elseif ($sentiment == 'negative') {
                    return '<span class="badge bg-danger">Negative</span>';
                }
                return '<span class="badge bg-secondary">' . ucfirst($log->sentiment ?? 'N/A') . '</span>';
            })
            ->editColumn('is_success', function ($log) {
                return $log->is_success 
                    ? '<span class="text-success"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg></span>'
                    : '<span class="text-danger"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg></span>';
            })
            ->addColumn('action', function($log) {
                return '<a href="'.route('admin.call-logs.show', $log->id).'" class="btn btn-sm btn-primary">View</a>';
            })
            ->rawColumns(['sentiment', 'is_success', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CallLog $model): QueryBuilder
    {
        return $model->newQuery()->with('user')->orderBy('id', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('calllog-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(0)
                    ->selectStyleSingle()
                    ->parameters([
                        'dom' => 'Bfrtip',
                        'buttons' => ['excel', 'csv', 'print', 'reset', 'reload'],
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('user_id')->title('Agent/User'),
            Column::make('phone_number')->title('Voter Number'),
            Column::make('call_timing')->title('Date & Time'),
            Column::make('call_duration')->title('Duration'),
            Column::make('sentiment')->title('Sentiment')->className('text-center'),
            Column::make('is_success')->title('Status')->className('text-center'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'CallLog_' . date('YmdHis');
    }
}

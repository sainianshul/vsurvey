<?php

namespace App\DataTables\System;

use App\Models\CommunicationLog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CommunicationLogDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('number', function (CommunicationLog $log) {
                return '<span class="fw-bold">' . e($log->number) . '</span>';
            })
            ->editColumn('message', function (CommunicationLog $log) {
                return '<span class="text-wrap" style="max-width: 400px; display: inline-block;">' . e($log->message) . '</span>';
            })
            ->addColumn('status', function (CommunicationLog $log) {
                $color = $log->status_color;
                return '<span class="badge badge-outline text-' . $color . '"><i class="ti ti-circle-filled fs-5 me-1"></i>' . e($log->status_name) . '</span>';
            })
            ->editColumn('created_at', function (CommunicationLog $log) {
                return $log->created_at ? $log->created_at->format('d M Y, h:i A') : '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function (CommunicationLog $log) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <button type="button"
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $log->id . '"
                            data-bs-toggle="tooltip" title="Delete Log">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['number', 'message', 'status', 'created_at', 'actions'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(CommunicationLog $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('communicationlog-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(4, 'desc') // Order by created_at desc
                    ->parameters([
                        'dom' => 
                            "<'row'<'col-12'tr>>" .
                            "<'row align-items-center mt-3 pt-3 flex-nowrap'" .
                            "<'col-sm-12 col-md-5'i>" .
                            "<'col-sm-12 col-md-7 d-flex justify-content-md-end align-items-center gap-3'lp>>",
                        'language' => [
                            'emptyTable' => 'No communication logs found',
                            'info' => 'Showing _START_–_END_ of _TOTAL_ logs',
                            'infoEmpty' => 'No logs to show',
                        ],
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->searchable(false),
            Column::make('number')->title('Phone Number'),
            Column::make('message')->title('Message Content'),
            Column::make('status')->title('Status')->searchable(false),
            Column::make('created_at')->title('Sent At')->searchable(false),
            Column::computed('actions')->title('Actions')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-end'),
        ];
    }
}

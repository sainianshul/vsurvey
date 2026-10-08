<?php

namespace App\DataTables\Products;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductLeadDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('buyer', function (Lead $lead) {
                if (!$lead->buyer) {
                    return '<div class="text-muted">Unknown User</div>';
                }
                
                $initials = mb_strtoupper(mb_substr($lead->buyer->name, 0, 2));
                $avatarHtml = $lead->buyer->profile_photo 
                    ? '<span class="avatar avatar-sm rounded-circle me-2" style="background-image: url(' . asset('storage/' . $lead->buyer->profile_photo) . ')"></span>'
                    : '<span class="avatar avatar-sm bg-primary-lt rounded-circle fw-bold me-2">' . $initials . '</span>';

                $url = route('admin.users.show', $lead->buyer->id);
                
                return '
                    <div class="d-flex align-items-center">
                        <a href="' . $url . '" class="d-block">' . $avatarHtml . '</a>
                        <div>
                            <div class="fw-semibold"><a href="' . $url . '" class="text-reset text-decoration-none">' . e($lead->buyer->name) . '</a></div>
                            <div class="text-secondary small">' . e($lead->buyer->email) . '</div>
                        </div>
                    </div>
                ';
            })
            ->addColumn('source', function (Lead $lead) {
                return '<span class="badge badge-outline text-secondary">' . e($lead->source_name) . '</span>';
            })
            ->addColumn('temperature', function (Lead $lead) {
                $tempColors = [
                    Lead::TEMP_COLD => 'blue',
                    Lead::TEMP_WARM => 'orange',
                    Lead::TEMP_HOT => 'red',
                ];
                $color = $tempColors[$lead->temperature] ?? 'secondary';
                return '<span class="badge badge-outline text-' . $color . '">' . e($lead->temperature_name) . '</span>';
            })
            ->addColumn('status', function (Lead $lead) {
                $statusColors = [
                    Lead::STATUS_NEW => 'blue',
                    Lead::STATUS_CONTACTED => 'yellow',
                    Lead::STATUS_CONVERTED => 'green',
                    Lead::STATUS_REJECTED => 'red',
                ];
                $color = $statusColors[$lead->status] ?? 'secondary';
                return '<span class="badge badge-outline text-' . $color . '">' . e($lead->status_name) . '</span>';
            })
            ->editColumn('created_at', function (Lead $lead) {
                return $lead->created_at->format('d M Y, h:i A');
            })
            ->addColumn('actions', function (Lead $lead) {
                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . route('admin.leads.show', $lead->id) . '" class="btn btn-icon btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="View Lead">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['buyer', 'source', 'temperature', 'status', 'actions'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Lead $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('product_id', $this->product_id)
            ->with('buyer')
            ->select('leads.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('productlead-table')
                    ->columns($this->getColumns())
                    ->ajax(route('admin.products.leads-data', $this->product_id))
                    ->orderBy(5, 'desc') // Order by created_at by default
                    ->parameters([
                        'dom' => '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        'buttons' => [],
                        'language' => [
                            'emptyTable' => 'No leads or enquiries found for this product.',
                            'search' => '',
                            'searchPlaceholder' => 'Search...',
                        ]
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('buyer')->title('Lead Source User')->searchable(false)->orderable(false),
            Column::make('source')->title('Source')->searchable(false),
            Column::make('temperature')->title('Temperature')->searchable(false),
            Column::make('quantity')->title('Quantity')->searchable(false),
            Column::make('status')->title('Status')->searchable(false),
            Column::make('created_at')->title('Received At')->searchable(false),
            Column::computed('actions')->title('Actions')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-end'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ProductLead_' . date('YmdHis');
    }
}

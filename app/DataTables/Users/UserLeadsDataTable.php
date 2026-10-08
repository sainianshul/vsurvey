<?php

namespace App\DataTables\Users;

use App\Models\Lead;
use Yajra\DataTables\Services\DataTable;

class UserLeadsDataTable extends DataTable
{
    public $userId;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('buyer_id', function (Lead $lead) {
                if (!$lead->buyer) return '<span class="text-muted">Unknown</span>';
                $url = route('admin.users.show', $lead->buyer->id);
                return '<a href="' . $url . '" class="text-reset text-decoration-none fw-medium">' . e($lead->buyer->name) . '</a>';
            })
            ->editColumn('item', function (Lead $lead) {
                if ($lead->product) {
                    $name = $lead->product->title;
                    $url = route('admin.products.show', $lead->product_id);
                } elseif ($lead->requirement) {
                    $name = $lead->requirement->title;
                    $url = route('admin.requirements.show', $lead->requirement_id);
                } else {
                    return '<span class="text-muted">Unknown</span>';
                }

                return '<a href="' . $url . '" class="text-reset text-decoration-none fw-semibold d-block text-truncate" style="max-width: 250px;">' . e($name) . '</a>';
            })
            ->editColumn('source', function(Lead $lead) {
                return e($lead->source_name);
            })
            ->editColumn('temperature', function(Lead $lead) {
                return e($lead->temperature_name);
            })
            ->addColumn('status', function (Lead $lead) {
                $colors = [
                    Lead::STATUS_NEW => 'blue',
                    Lead::STATUS_CONTACTED => 'orange',
                    Lead::STATUS_CONVERTED => 'green',
                    Lead::STATUS_REJECTED => 'red',
                ];
                $color = $colors[$lead->status] ?? 'secondary';
                return '<span class="badge badge-outline text-' . $color . '">' . e($lead->status_name) . '</span>';
            })
            ->editColumn('created_at', function (Lead $lead) {
                return $lead->created_at ? $lead->created_at->format('d M Y h:i A') : '<span class="text-muted">—</span>';
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
            ->rawColumns([
                'buyer_id',
                'item',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(Lead $model)
    {
        $query = $model->newQuery()
            ->with(['buyer', 'product', 'requirement'])
            ->where('seller_id', $this->userId)
            ->whereHas('buyer'); // jisme user to h unhe hi dihao

        return $query->latest();
    }
    
    public function html()
    {
        return $this->builder()
            ->setTableId('user-leads-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(5, 'desc')
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'createdRow' => "function(row, data, dataIndex) { $(row).addClass('small'); }",
            ]);
    }
    
    protected function getColumns()
    {
        return [
            ['data' => 'buyer_id', 'name' => 'buyer.name', 'title' => 'User Name'],
            ['data' => 'item', 'name' => 'item', 'title' => 'Product/Requirement', 'orderable' => false, 'searchable' => false],
            ['data' => 'source', 'name' => 'source', 'title' => 'Source', 'orderable' => false, 'searchable' => false],
            ['data' => 'temperature', 'name' => 'temperature', 'title' => 'Temperature', 'orderable' => false, 'searchable' => false],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At', 'searchable' => false],
            ['data' => 'actions', 'name' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    public function filename(): string
    {
        return 'UserLeads_' . date('Y_m_d_His');
    }
}

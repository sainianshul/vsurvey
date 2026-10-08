<?php

namespace App\DataTables\Users;

use App\Models\Product;
use Yajra\DataTables\Services\DataTable;

class UserProductsDataTable extends DataTable
{
    public $userId;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('title', function($query, $keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                      ->orWhere('slug', 'like', "%{$keyword}%");
            })
            ->editColumn('title', function (Product $product) {
                $imageUrl = $product->primaryImage ? $product->primaryImage->url : null;
                $showUrl = route('admin.products.show', $product->id);
                $imageHtml = $imageUrl 
                    ? '<a href="' . $showUrl . '" class="d-block"><div class="avatar avatar-sm me-2" style="background-image: url(' . e($imageUrl) . ')"></div></a>'
                    : '<a href="' . $showUrl . '" class="d-block"><div class="avatar avatar-sm me-2 bg-light text-muted"><i class="ti ti-photo"></i></div></a>';
                
                return '
                    <div class="d-flex align-items-center">
                        ' . $imageHtml . '
                        <div>
                            <a href="' . $showUrl . '" class="fw-semibold text-truncate d-block text-reset text-decoration-none" style="max-width: 150px;">' . e($product->title) . '</a>
                        </div>
                    </div>
                ';
            })
            ->addColumn('condition', function (Product $product) {
                $color = $product->condition == \App\Models\Product::CONDITION_NEW ? 'green' : 'orange';
                return '<span class="badge badge-outline text-' . $color . '">' . e($product->condition_name) . '</span>';
            })
            ->editColumn('price', function (Product $product) {
                return '₹' . number_format($product->price, 2) . ($product->price_unit ? ' / ' . e($product->price_unit) : '');
            })
            ->addColumn('is_featured', function (Product $product) {
                $color = $product->is_featured ? 'yellow' : 'secondary';
                $text = $product->is_featured ? 'Yes' : 'No';
                $icon = $product->is_featured ? 'ti ti-star-filled' : 'ti ti-star';
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' featured-modal-btn text-decoration-none" data-id="' . $product->id . '" data-featured="' . $product->is_featured . '">
                        <i class="' . $icon . ' me-1"></i>' . $text . '
                    </a>
                ';
            })
            ->addColumn('status', function (Product $product) {
                $color = $product->status_color;
                return '
                    <a href="javascript:void(0)" class="badge badge-outline text-' . $color . ' status-modal-btn text-decoration-none" data-id="' . $product->id . '" data-status="' . $product->status . '">
                        <i class="ti ti-circle-filled me-1"></i>' . e($product->status_name) . '
                    </a>
                ';
            })
            ->addColumn('stats', function (Product $product) {
                return '<span class="text-nowrap">' . $product->views_count . ' <span class="text-muted mx-1">/</span> ' . $product->leads_count . '</span>';
            })
            ->editColumn('created_at', function (Product $product) {
                return $product->created_at ? $product->created_at->format('d M Y') : '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function (Product $product) {
                $showUrl = route('admin.products.show', $product->id);
                $editUrl = route('admin.products.edit', $product->id);

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
                            class="btn btn-icon btn-sm btn-outline-danger btn-delete"
                            data-id="' . $product->id . '" data-name="' . e($product->title) . '"
                            data-bs-toggle="tooltip" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns([
                'title',
                'condition',
                'price',
                'is_featured',
                'status',
                'stats',
                'created_at',
                'actions',
            ]);
    }

    public function query(Product $model)
    {
        $query = $model->newQuery()
            ->with(['category', 'primaryImage'])
            ->where('user_id', $this->userId);

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('is_featured')) {
            $query->where('is_featured', request('is_featured'));
        }

        $query->latest();

        return $query;
    }
    
    public function html()
    {
        return $this->builder()
            ->setTableId('user-products-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(1, 'asc')
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'createdRow' => "function(row, data, dataIndex) { $(row).addClass('small'); }",
            ]);
    }
    
    protected function getColumns()
    {
        return [
            ['data' => 'title', 'name' => 'title', 'title' => 'Product'],
            ['data' => 'price', 'name' => 'price', 'title' => 'Price'],
            ['data' => 'condition', 'name' => 'condition', 'title' => 'Condition', 'orderable' => false, 'searchable' => false],
            ['data' => 'is_featured', 'name' => 'is_featured', 'title' => 'Featured', 'orderable' => false, 'searchable' => false],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status', 'orderable' => false, 'searchable' => false],
            ['data' => 'stats', 'name' => 'stats', 'title' => 'Stats', 'orderable' => false, 'searchable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Listed On', 'searchable' => false],
            ['data' => 'actions', 'name' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    public function filename(): string
    {
        return 'UserProducts_' . date('Y_m_d_His');
    }
}

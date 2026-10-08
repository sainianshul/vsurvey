<?php

namespace App\DataTables\Users;

use App\Models\ProductView;
use Yajra\DataTables\Services\DataTable;

class UserRecentViewsDataTable extends DataTable
{
    public $userId;

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('title', function($query, $keyword) {
                $query->whereHas('product', function($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%")
                      ->orWhere('slug', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('title', function (ProductView $view) {
                $product = $view->product;
                if (!$product) return '<span class="text-muted">Deleted Product</span>';
                
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
            ->editColumn('price', function (ProductView $view) {
                $product = $view->product;
                if (!$product) return '-';
                return '₹' . number_format($product->price, 2) . ($product->price_unit ? ' / ' . e($product->price_unit) : '');
            })
            ->editColumn('created_at', function (ProductView $view) {
                return $view->created_at ? $view->created_at->format('d M Y, H:i') : '<span class="text-muted">—</span>';
            })
            ->addColumn('actions', function (ProductView $view) {
                if (!$view->product) return '';
                $showUrl = route('admin.products.show', $view->product_id);

                return '
                    <div class="d-flex gap-1 justify-content-end">
                        <a href="' . $showUrl . '"
                            class="btn btn-icon btn-sm btn-outline-primary"
                            data-bs-toggle="tooltip" title="View Product">
                            <i class="ti ti-eye"></i>
                        </a>
                    </div>
                ';
            })
            ->rawColumns([
                'title',
                'created_at',
                'actions',
            ]);
    }

    public function query(ProductView $model)
    {
        return $model->newQuery()
            ->with(['product.primaryImage'])
            ->where('user_id', $this->userId)
            ->latest();
    }
    
    public function html()
    {
        return $this->builder()
            ->setTableId('user-recent-views-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(2, 'desc')
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'createdRow' => "function(row, data, dataIndex) { $(row).addClass('small'); }",
            ]);
    }
    
    protected function getColumns()
    {
        return [
            ['data' => 'title', 'name' => 'product.title', 'title' => 'Product'],
            ['data' => 'price', 'name' => 'product.price', 'title' => 'Price', 'orderable' => false],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Viewed On'],
            ['data' => 'actions', 'name' => 'actions', 'title' => 'Actions', 'orderable' => false, 'searchable' => false, 'class' => 'text-end'],
        ];
    }

    public function filename(): string
    {
        return 'UserRecentViews_' . date('Y_m_d_His');
    }
}

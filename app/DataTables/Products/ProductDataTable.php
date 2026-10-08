<?php

namespace App\DataTables\Products;

use App\Models\Product;
use Yajra\DataTables\Services\DataTable;

class ProductDataTable extends DataTable
{
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
                        <a href="' . $showUrl . '" class="fw-semibold text-truncate d-block text-reset text-decoration-none" style="max-width: 150px;">' . e($product->title) . '</a>
                    </div>
                ';
            })
            ->addColumn('seller', function (Product $product) {
                if (!$product->seller) return '<span class="text-muted">—</span>';
                $url = route('admin.users.show', $product->seller->id);
                return '<a href="' . $url . '" class="text-decoration-none">' . e($product->seller->name) . '</a>';
            })
            ->addColumn('category', function (Product $product) {
                return $product->category ? e($product->category->name) : '<span class="text-muted">—</span>';
            })
            ->addColumn('condition', function (Product $product) {
                $color = $product->condition == \App\Models\Product::CONDITION_NEW ? 'green' : 'orange';
                return '<span class="badge badge-outline text-' . $color . '">' . e($product->condition_name) . '</span>';
            })
            ->editColumn('price', function (Product $product) {
                return '₹' . number_format($product->price, 2) . ($product->price_unit ? ' / ' . e($product->price_unit) : '');
            })
            ->addColumn('minimum_quantity', function (Product $product) {
                return $product->minimum_quantity;
            })
            ->addColumn('city', function (Product $product) {
                return $product->city ? e($product->city) : '<span class="text-muted">—</span>';
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
                        <i class="ti ti-circle-filled fs-5 me-1"></i>' . e($product->status_name) . '
                    </a>
                ';
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
                'seller',
                'condition',
                'city',
                'price',
                'is_featured',
                'status',
                'created_at',
                'actions',
            ]);
    }

    public function query(Product $model)
    {
        $query = $model->newQuery()->with(['seller', 'category', 'primaryImage']);

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('is_featured')) {
            $query->where('is_featured', request('is_featured'));
        }

        if (request()->filled('category_id')) {
            $categoryId = request('category_id');
            $category = \App\Models\Category::with('children')->find($categoryId);
            if ($category) {
                $categoryIds = array_merge([$categoryId], $category->getAllChildrenIds());
                $query->whereIn('category_id', $categoryIds);
            } else {
                $query->where('category_id', $categoryId);
            }
        }
        
        if (request()->filled('user_id')) {
            $query->where('user_id', request('user_id'));
        }

        if (request()->filled('condition')) {
            $query->where('condition', request('condition'));
        }

        if (request()->filled('created_date')) {
            $query->whereDate('created_at', request('created_date'));
        }

        $query->latest();

        return $query;
    }

    public function filename(): string
    {
        return 'Products_' . date('Y_m_d_His');
    }
}

<?php

namespace App\DataTables\Products;

use App\Models\ProductView;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductViewDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('user', function (ProductView $view) {
                if (!$view->user) {
                    return '<div class="text-muted">Unknown User</div>';
                }
                
                $initials = mb_strtoupper(mb_substr($view->user->name, 0, 2));
                $avatarHtml = $view->user->profile_photo 
                    ? '<span class="avatar avatar-sm rounded-circle me-2" style="background-image: url(' . asset('storage/' . $view->user->profile_photo) . ')"></span>'
                    : '<span class="avatar avatar-sm bg-primary-lt rounded-circle fw-bold me-2">' . $initials . '</span>';

                $url = route('admin.users.show', $view->user->id);
                
                return '
                    <div class="d-flex align-items-center">
                        ' . $avatarHtml . '
                        <div>
                            <div class="fw-semibold"><a href="' . $url . '" class="text-reset">' . $view->user->name . '</a></div>
                            <div class="text-secondary small">' . $view->user->email . '</div>
                        </div>
                    </div>
                ';
            })
            ->editColumn('created_at', function (ProductView $view) {
                return $view->created_at->format('d M Y, h:i A');
            })
            ->rawColumns(['user'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductView $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('product_id', $this->product_id)
            ->with('user')
            ->select('product_views.*');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('productview-table')
                    ->columns($this->getColumns())
                    ->ajax(route('admin.products.views-data', $this->product_id))
                    ->orderBy(1, 'desc')
                    ->parameters([
                        'dom' => '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>',
                        'buttons' => [],
                        'language' => [
                            'emptyTable' => 'No views recorded for this product yet.',
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
            Column::make('user')->title('User')->searchable(false)->orderable(false),
            Column::make('created_at')->title('Viewed At')->searchable(false),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'ProductView_' . date('YmdHis');
    }
}

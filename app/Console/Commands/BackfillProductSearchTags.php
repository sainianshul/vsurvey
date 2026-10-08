<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackfillProductSearchTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-product-search-tags';
    protected $description = 'Backfills the search_tags column for all existing products.';

    public function handle()
    {
        $this->info('Starting backfill for product search_tags...');

        $products = \App\Models\Product::all();
        $bar = $this->output->createProgressBar(count($products));

        foreach ($products as $product) {
            $product->search_tags = \App\Models\Product::generateSearchTags($product);
            $product->saveQuietly(); // save without triggering events
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nBackfill completed successfully.");
    }
}

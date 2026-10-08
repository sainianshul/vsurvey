<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Electronics' => [
                'Mobiles', 'Laptops', 'Cameras', 'Televisions', 'Audio & Accessories'
            ],
            'Clothing & Fashion' => [
                'Men\'s Clothing', 'Women\'s Clothing', 'Kids\' Wear', 'Winter Wear', 'Fashion Accessories'
            ],
            'Home & Furniture' => [
                'Living Room', 'Bedroom', 'Kitchen & Dining', 'Home Decor', 'Office Furniture'
            ],
            'Beauty & Personal Care' => [
                'Makeup', 'Skincare', 'Haircare', 'Fragrances', 'Men\'s Grooming'
            ],
            'Grocery & Food' => [
                'Staples', 'Snacks & Branded Foods', 'Beverages', 'Dairy & Bakery', 'Packaged Food'
            ],
            'Books & Stationery' => [
                'Fiction Books', 'Non-Fiction', 'School Supplies', 'Office Supplies', 'Magazines'
            ],
            'Sports & Fitness' => [
                'Cricket', 'Fitness Equipment', 'Yoga & Pilates', 'Cycling', 'Team Sports'
            ],
            'Toys & Baby Products' => [
                'Action Figures', 'Educational Toys', 'Baby Care', 'Strollers & Prams', 'Soft Toys'
            ],
            'Automotive' => [
                'Car Accessories', 'Bike Accessories', 'Vehicle Cleaning', 'Spare Parts', 'Helmets'
            ],
            'Footwear' => [
                'Sports Shoes', 'Casual Shoes', 'Formal Shoes', 'Sandals & Floaters', 'Slippers & Flip Flops'
            ],
            'Health & Medical' => [
                'Vitamins & Supplements', 'Medical Devices', 'First Aid', 'Protein Supplements', 'Ayurvedic Care'
            ],
            'Jewellery & Watches' => [
                'Gold Jewellery', 'Silver Jewellery', 'Artificial Jewellery', 'Men\'s Watches', 'Women\'s Watches'
            ],
        ];

        $sortOrder = 1;

        foreach ($categories as $parentName => $subCategories) {
            // Create Parent Category
            $parent = Category::create([
                'name' => $parentName,
                'slug' => Str::slug($parentName),
                'sort_order' => $sortOrder++,
                'is_active' => true,
                'is_featured' => ($sortOrder <= 6), // Make first 5 featured
                'level' => 0,
            ]);

            $subSortOrder = 1;

            // Create Sub Categories
            foreach ($subCategories as $subCategoryName) {
                Category::create([
                    'name' => $subCategoryName,
                    'slug' => Str::slug($subCategoryName),
                    'parent_id' => $parent->id,
                    'sort_order' => $subSortOrder++,
                    'is_active' => true,
                    'is_featured' => false,
                    'level' => 1,
                ]);
            }
        }
    }
}

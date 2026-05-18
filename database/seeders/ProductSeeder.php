<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $products = [
            [
                'type' => Product::TYPE_FULL_SERVICE,
                'title' => 'GET HIRED WFH 2-Week Full Service Package',
                'badge_text' => 'ONLY ONE SPOT LEFT',
                'regular_price' => 325.00,
                'sale_price' => 275.00,
                'cta_label' => 'Buy Now',
                'cta_link' => '#',
                'image_url' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'type' => Product::TYPE_FULL_SERVICE,
                'title' => 'GET HIRED WFH 3-Week Full Service Package',
                'badge_text' => 'ONLY ONE SPOT LEFT',
                'regular_price' => 425.00,
                'sale_price' => 350.00,
                'cta_label' => 'Buy Now',
                'cta_link' => '#',
                'image_url' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'type' => Product::TYPE_FULL_SERVICE,
                'title' => 'GET HIRED WFH 6-Week Full Service Package',
                'badge_text' => 'ONLY ONE SPOT LEFT',
                'regular_price' => 575.00,
                'sale_price' => 475.00,
                'cta_label' => 'Buy Now',
                'cta_link' => '#',
                'image_url' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($products as $productData) {
            Product::query()->updateOrCreate(
                ['type' => $productData['type'], 'title' => $productData['title']],
                $productData
            );
        }
    }
}

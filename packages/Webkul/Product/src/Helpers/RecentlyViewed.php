<?php

namespace Webkul\Product\Helpers;

use Webkul\Product\Repositories\ProductRepository;

class RecentlyViewed
{
    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    /**
     * Get recently viewed products
     *
     * @param  int  $limit
     * @return array
     */
    public function getRecentlyViewedProducts($limit = 8)
    {
        $productIds = $this->getRecentlyViewedProductIds();

        if (empty($productIds)) {
            return [];
        }

        $products = $this->productRepository
            ->setSearchEngine('database')
            ->whereIn('id', $productIds)
            ->get();

        // Sort products according to the order in session
        $orderedProducts = [];
        foreach ($productIds as $productId) {
            foreach ($products as $product) {
                if ($product->id == $productId) {
                    $orderedProducts[] = $product;
                    break;
                }
            }
        }

        return array_slice($orderedProducts, 0, $limit);
    }

    /**
     * Add product to recently viewed
     *
     * @param  int  $productId
     * @return void
     */
    public function addProductToRecentlyViewed($productId)
    {
        $recentlyViewed = session()->get('recently_viewed_products', []);

        // Remove product if already exists
        if (($key = array_search($productId, $recentlyViewed)) !== false) {
            unset($recentlyViewed[$key]);
        }

        // Add product to the beginning
        array_unshift($recentlyViewed, $productId);

        // Limit to 20 products
        $recentlyViewed = array_slice($recentlyViewed, 0, 20);

        session()->put('recently_viewed_products', $recentlyViewed);
    }

    /**
     * Get recently viewed product IDs
     *
     * @return array
     */
    protected function getRecentlyViewedProductIds()
    {
        return session()->get('recently_viewed_products', []);
    }
}
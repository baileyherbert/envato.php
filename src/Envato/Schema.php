<?php

namespace Herbert\Envato {

    /**
     * When invoked as a function, returns a two-dimensional array representing a "schema" for the Market API and
     * its endpoints.
     *
     * @package Herbert\Envato
     * @returns array
     */
    class Schema
    {
        public function __invoke() {
            return [
                // Envato Market Catalog
                'catalog' => [
                    'collection' => '/v3/market/catalog/collection',
                    'item' => '/v3/market/catalog/item',
                    'item_version' => '/v3/market/catalog/item-version',
                    'items' => '/v1/discovery/search/search/item',
                    'comments' => '/v1/discovery/search/search/comment',
                    'popular' => '/v1/market/popular:{site}.json',
                    'categories' => '/v1/market/categories:{site}.json',
                    'prices' => '/v1/market/item-prices:{item_id}.json',
                    'newest' => '/v1/market/new-files:{site},{category}.json',
                    'featured' => '/v1/market/features:{site}.json',
                    'random' => '/v1/market/random-new-files:{site}.json'
                ],

                // User details
                'profile' => [
                    'collections' => '/v3/market/user/collections',
                    'collection' => '/v3/market/user/collection',
                    'details' => '/v1/market/user:{username}.json',
                    'badges' => '/v1/market/user-badges:{username}.json',
                    'portfolio' => '/v1/market/user-items-by-site:{username}.json',
                    'newest' => '/v1/market/new-files-from-user:{username},{site}.json'
                ],

                // Private user details
                'user' => [
                    'sales' => '/v3/market/author/sales',
                    'sale' => '/v3/market/author/sale',
                    'purchases' => '/v3/market/buyer/list-purchases',
                    'purchase' => '/v3/market/buyer/purchase',
                    'download' => '/v3/market/buyer/download',
                    'details' => '/v1/market/private/user/account.json',
                    'username' => '/v1/market/private/user/username.json',
                    'email' => '/v1/market/private/user/email.json',
                    'earnings' => '/v1/market/private/user/earnings-and-sales-by-month.json',
                    'statement' => '/v3/market/user/statement',
                ],

                // Market stats
                'market' => [
                    'users' => '/v1/market/total-users.json',
                    'items' => '/v1/market/total-items.json',
                    'site' => '/v1/market/number-of-files:{site}.json'
                ]
            ];
        }
    }

}

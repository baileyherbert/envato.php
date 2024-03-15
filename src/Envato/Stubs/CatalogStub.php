<?php

namespace Herbert\Envato\Stubs;

use Herbert\Envato\ResultSet;

/**
 * A collection of endpoints for browsing the Envato Market catalog.
 */
interface CatalogStub {

	/**
	 * Returns details of, and items contained within, a public collection.
	 *
	 * ```php
	 * $client->catalog->collection(['id' => 12345]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getCatalogCollection
	 */
	public function collection(array $parameters);

	/**
	 * Returns all details of a particular item on Envato Market.
	 *
	 * ```php
	 * $client->catalog->item(['id' => 12345]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getCatalogItem
	 */
	public function item(array $parameters);

	/**
	 * Returns the latest available version of a theme/plugin. This is the recommended endpoint for Wordpress
	 * theme/plugin authors building an auto-upgrade system into their item that needs to check if a new version is
	 * available.
	 *
	 * ```php
	 * $client->catalog->item_version(['id' => 12345]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getCatalogItemVersion
	 */
	public function item_version(array $parameters);

	/**
	 * Search for items.
	 *
	 * ```php
	 * $client->catalog->items(['site' => 'codecanyon.net', 'term' => '']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#search_getSearchItem
	 */
	public function items(array $parameters);

	/**
	 * Search for comments.
	 *
	 * ```php
	 * $client->catalog->comments(['item_id' => 12345]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#search_getSearchComment
	 */
	public function comments(array $parameters);

	/**
	 * Returns the popular files for a particular site. Requires a site parameter, e.g. `themeforest`.
	 *
	 * ```php
	 * $client->catalog->popular(['site' => 'codecanyon']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getPopular
	 */
	public function popular(array $parameters);

	/**
	 * Lists the categories of a particular site. Requires a site parameter, e.g. `themeforest`.
	 *
	 * ```php
	 * $client->catalog->categories(['site' => 'codecanyon']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getCategories
	 */
	public function categories(array $parameters);

	/**
	 * Return available licenses and prices for the given item ID.
	 *
	 * ```php
	 * $client->catalog->prices(['item_id' => 12345]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getItemPrices
	 */
	public function prices(array $parameters);

	/**
	 * New files, recently uploaded to a particular site. Requires `site` and `category` parameters.
	 *
	 * ```php
	 * $client->catalog->newest(['site' => 'codecanyon', 'category' => 'php-scripts']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getNewFiles
	 */
	public function newest(array $parameters);

	/**
	 * Shows the current site features.
	 *
	 * ```php
	 * $client->catalog->featured(['site' => 'codecanyon']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getFeatures
	 */
	public function featured(array $parameters);

	/**
	 * Shows a random list of newly uploaded files from a particular site. Requires a site parameter, e.g.
	 * `themeforest`.
	 *
	 * ```php
	 * $client->catalog->random(['site' => 'codecanyon']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getRandomNewFiles
	 */
	public function random(array $parameters);

}

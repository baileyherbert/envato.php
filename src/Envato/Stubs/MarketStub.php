<?php

namespace Herbert\Envato\Stubs;

use Herbert\Envato\ResultSet;

/**
 * A collection of endpoints for obtaining Envato Market statistics.
 */
interface MarketStub {

	/**
	 * Returns the total number of subscribed users on Envato Market.
	 *
	 * ```php
	 * $client->market->users();
	 * ```
	 *
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getTotalUsers
	 */
	public function users();

	/**
	 * Returns the total number of items listed on Envato Market.
	 *
	 * ```php
	 * $client->market->items();
	 * ```
	 *
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getTotalItems
	 */
	public function items();

	/**
	 * Returns the total number of items listed on Envato Market for a specific site.
	 *
	 * ```php
	 * $client->market->site(['site' => 'codecanyon']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getNumberOfFiles
	 */
	public function site(array $parameters);

}

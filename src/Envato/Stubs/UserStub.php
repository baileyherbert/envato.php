<?php

namespace Herbert\Envato\Stubs;

use Herbert\Envato\ResultSet;

/**
 * A collection of endpoints for obtaining private information about the authenticated user.
 */
interface UserStub {

	/**
	 * Lists all unrefunded sales of the authenticated user's items listed on Envato Market.
	 *
	 * ```php
	 * $client->user->sales();
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getAuthorSales
	 */
	public function sales();

	/**
	 * Returns the details of an author's sale identified by the purchase code (for authors only).
	 *
	 * ```php
	 * $client->user->sale(['code' => '*****']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getAuthorSale
	 */
	public function sale(array $parameters);

	/**
	 * Lists purchases made by the authenticated user.
	 *
	 * ```php
	 * $client->user->purchases();
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getBuyerListPurchases
	 */
	public function purchases();

	/**
	 * Returns the details of a purchase made by the authenticated user (for buyers only).
	 *
	 * ```php
	 * $client->user->purchase(['code' => '*****']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getBuyerPurchase
	 */
	public function purchase(array $parameters);

	/**
	 * Creates a download link for a Market purchase by either the `item_id` or the `purchase_code`. Each invocation of
	 * this endpoint will count against the items daily download limit.
	 *
	 * ```php
	 * $client->user->download(['purchase_code' => '*****']);
	 * $client->user->download(['item_id' => 123456]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getBuyerDownload
	 */
	public function download(array $parameters);

	/**
	 * Returns the first name, surname, earnings available to withdraw, total deposits, balance (deposits + earnings)
	 * and country for the authenticated user.
	 *
	 * ```php
	 * $client->user->details();
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getUserAccount
	 */
	public function details();

	/**
	 * Returns the username of the authenticated user.
	 *
	 * ```php
	 * $client->user->username();
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getUserUsername
	 */
	public function username();

	/**
	 * Returns the email address of the authenticated user.
	 *
	 * ```php
	 * $client->user->email();
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getUserEmail
	 */
	public function email();

	/**
	 * Returns the sales/earnings and sales for the authenticated user by month, as displayed on the "earnings" page.
	 *
	 * ```php
	 * $client->user->earnings();
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getUserEarningsAndSalesByMonth
	 */
	public function earnings();

	/**
	 * Lists transactions from the user's statement page.
	 *
	 * ```php
	 * $client->user->statement([
	 *   'page' => 1,
	 *   'from_date' => '2021-02-01',
	 *   'to_date' => '2022-06-21',
	 *   'type' => 'Sale',
	 *   'site' => 'codecanyon.net'
	 * ]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getUserStatement
	 */
	public function statement(array $parameters);

}

<?php

namespace Herbert\Envato\Stubs;

use Herbert\Envato\ResultSet;

/**
 * A collection of endpoints for obtaining public information about other users.
 */
interface ProfileStub {

	/**
	 * Lists all of the **current user's** private and public collections.
	 *
	 * ```php
	 * $client->profile->collections();
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getUserCollections
	 */
	public function collections();

	/**
	 * Returns details and items for any public collection, or returns details and items for one of the current user's
	 * private collections, by its ID.
	 *
	 * ```php
	 * $client->profile->collection(['id' => 12345]);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_0_getUserCollection
	 */
	public function collection(array $parameters);

	/**
	 * Returns the username, country, number of sales, number of followers, location and image for a user. Requires a
	 * username, e.g. `collis`.
	 *
	 * ```php
	 * $client->profile->details(['username' => 'collis']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getUser
	 */
	public function details(array $parameters);

	/**
	 * Returns a list of badges for the given user.
	 *
	 * ```php
	 * $client->profile->badges(['username' => 'baileyherbert']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getUserBadges
	 */
	public function badges(array $parameters);

	/**
	 * Returns the number of items that the user has for sale on each Market site. Requires a username, e.g. `collis`.
	 *
	 * ```php
	 * $client->profile->portfolio(['username' => 'baileyherbert']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getUserItemsBySite
	 */
	public function portfolio(array $parameters);

	/**
	 * Returns up to 1,000 of the newest files from a particular user on the target Market site. Requires a username
	 * and a site parameter, e.g. `collis` and `themeforest`.
	 *
	 * ```php
	 * $client->profile->newest(['username' => 'collis', 'site' => 'themeforest']);
	 * ```
	 *
	 * @param array $parameters
	 * @return ResultSet
	 * @see https://build.envato.com/api/#market_getNewFilesFromUser
	 */
	public function newest(array $parameters);

}

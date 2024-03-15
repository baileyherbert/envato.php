# Envato.php

An API client for Envato in PHP, with simplified OAuth, token storage, and request sending.

- [Notes](#notes)
- [Installation](#installation)
- [Authentication](#authentication)
  - [Personal Token](#personal-token)
  - [OAuth](#oauth)
- [Sending Requests](#sending-requests)
  - [Getting Request Time](#getting-request-time)
  - [Rate Limiting](#rate-limiting)
  - [Custom Requests](#custom-requests)
- [Catalog](#catalog)
  - [Look up a public collection](#look-up-a-public-collection)
  - [Look up a single item](#look-up-a-single-item)
  - [Look up a single WordPress theme/plugin version](#look-up-a-single-wordpress-themeplugin-version)
  - [Search for items](#search-for-items)
  - [Search for comments](#search-for-comments)
  - [Popular items by site](#popular-items-by-site)
  - [Categories by site](#categories-by-site)
  - [Prices for a particular item](#prices-for-a-particular-item)
  - [New items by site and category](#new-items-by-site-and-category)
  - [Find featured items](#find-featured-items)
  - [Random new items](#random-new-items)
- [Profile](#profile)
  - [List all of current user's collections](#list-all-of-current-users-collections)
  - [Look up a collection by ID](#look-up-a-collection-by-id)
  - [Get a user's profile details](#get-a-users-profile-details)
  - [List a user's badges](#list-a-users-badges)
  - [Get a user's items by site](#get-a-users-items-by-site)
  - [Get a user's newest items](#get-a-users-newest-items)
- [User](#user)
  - [List an author's sales](#list-an-authors-sales)
  - [Look up a sale by purchase code](#look-up-a-sale-by-purchase-code)
  - [List a buyer's purchases](#list-a-buyers-purchases)
  - [Look up a buyer's purchase by code](#look-up-a-buyers-purchase-by-code)
  - [Download a buyer's purchase](#download-a-buyers-purchase)
  - [Get private account details](#get-private-account-details)
  - [Get the current user's username](#get-the-current-users-username)
  - [Get the current user's email](#get-the-current-users-email)
  - [Get the user's earnings by month](#get-the-users-earnings-by-month)
  - [Get the user's statement](#get-the-users-statement)
- [Market](#market)
  - [Get total number of users](#get-total-number-of-users)
  - [Get total number of items](#get-total-number-of-items)
  - [Get total number of items by site](#get-total-number-of-items-by-site)
- [Other Endpoints](#other-endpoints)
  - [Get the client's identity](#get-the-clients-identity)
- [Handling Errors \& Exceptions](#handling-errors--exceptions)
  - [Authorization Errors](#authorization-errors)
  - [Request Errors](#request-errors)
- [Examples](#examples)
  - [Verifying Purchase Codes](#verifying-purchase-codes)
- [Breaking changes in v3](#breaking-changes-in-v3)
- [Contributors](#contributors)

## Notes

This API client is fully working though not necessarily completed. Of course, any updates that aren't backwards-compatible will be bumped up a major version. Other than that, here's some info you'll probably want to know.

- This client does **not** disable SSL. It ships with a Certificate Authority bundle and uses this bundle to verify the Envato API's SSL certificate, instead of relying on the system's often-unavailable certificate.

## Installation

Include this into your project using Composer. It will be autoloaded.

```
composer require baileyherbert/envato
```

## Authentication

### Personal Token

Start a new client with a personal token (create one at build.envato.com).

```php
$token = new Herbert\Envato\Auth\Token('Your Personal Token');
$client = new Herbert\EnvatoClient($token);
```

### OAuth

With OAuth, you must redirect your users to Envato's authentication screen, where they will approve your requested permissions. Then, they will be redirected back to your application
with a `code` query parameter that can be used to obtain an API token.

The following example demonstrates a complete OAuth guard that redirects the user and creates a client when they return. It also persists their credentials to a PHP session, so the
client can be recreated and the token can be renewed in future requests.

```php
// Generate the OAuth object
$oauth = new Herbert\Envato\Auth\OAuth([
    'client_id' => 'Your client id',
    'client_secret' => 'Your client secret',
    'redirect_uri' => 'https://your-redirect-uri.com/',
    'store' => function(string $session) {
        // Example: Save the OAuth credentials to a PHP session
        // This is just a JSON-encoded string that looks like below:
        // {"access_token": "...", "refresh_token": "...", "expires": 1710526960}

        $_SESSION['envato_oauth'] = $session;
    }
]);

// Example: Restore existing OAuth credentials from a PHP session
if (isset($_SESSION['envato_oauth'])) {
    $oauth->load($_SESSION['envato_oauth']);
}

// Get the client (will return NULL if not authenticated)
// This will auto-detect the "code" query parameter on the current request
// If found, it will attempt to create a token and instantiate the client
$client = $oauth->getClient();

// Redirect the user if they're not authorized yet
if (!$client) {
    header("Location: " . $oauth->getAuthorizationUri());
    die;
}

// Example: Get the user's unique Envato Account ID
// This sends a request to the Envato API, so don't call it often!
$userId = $client->getUserId(); // int(1908998)

// Example: Request the user's username
// This sends a request to the Envato API, so don't call it often!
$response = $client->user->username();
$username = $response->username; // string(13) "baileyherbert"
```

To make this example work, create a new app at the [build.envato.com](https://build.envato.com/my-apps/) website. The `redirect_uri` should point directly to the file where the above
code is hosted and must be exactly the same both in the code and on the registered Envato App.

Tokens generated in this manner will expire after one hour. However, by using the `store` callback and `load()` method as shown above, the client can automatically renew them in the
background. When that happens, the `store` callback will be invoked again with the new credentials.

## Sending Requests

Here's an example request to get the current user's username. Currently, it returns a `ResultSet` object; this object exposes a `results` property which is an array of the raw API response.

```php
$response = $client->user->username();

if (!$response->error) {
    $username = $response->results['username'];

    echo "Logged in as {$username}";
}
else {
    echo "Error: {$response->error}";
}
```

For an endpoint which has variables, you can pass them as an array. This works for all endpoint versions, including legacy v1 and the new v3.

```php
$response = $client->profile->portfolio([
    'username' => 'baileyherbert'
]);
```


### Getting Request Time

To determine how long a request took to execute (in seconds), you can reference the `$response->time` property.


### Rate Limiting

If you're being rate limited, the client will throw a `TooManyRequestsException` exception. The exception instance has
methods to help work with the rate limit.

```php
use Herbert\Envato\Exceptions\TooManyRequestsException;

try {
    $item = $client->catalog->item(['id' => 1234567]);
}
catch (TooManyRequestsException $e) {
    // Get the number of seconds remaining (float)
    $secondsRemaining = $e->getSecondsRemaining();

    // Get the timestamp for when we can make our next request
    $timestamp = $e->getRetryTime();

    // Sleep until the rate limiting has ended
    $e->wait();
}
```

### Custom Requests

If there is a new endpoint which is not yet available in this package, you may use the `$request` property on the
client to manually send the request until it is added.

There are methods available for each request type (`get`, `post`, etc). Pass the path as the first parameter. Pass your
POST body variables as the second parameter, these will also replace variables in the path denoted by `{}`.

```php
$client->request->get('/v1/market/user:{username}.json', [
  'username' => 'collis'
]);
```

## Catalog

### Look up a public collection

```php
$client->catalog->collection(['id' => 12345]);
```

### Look up a single item

```php
$client->catalog->item(['id' => 12345]);
```

### Look up a single WordPress theme/plugin version

```php
$client->catalog->item_version(['id' => 12345]);
```

### Search for items

```php
$client->catalog->items(['site' => 'codecanyon.net', 'term' => '']);
```

### Search for comments

```php
$client->catalog->comments(['item_id' => 12345]);
```

### Popular items by site

```php
$client->catalog->popular(['site' => 'codecanyon']);
```

### Categories by site

```php
$client->catalog->categories(['site' => 'codecanyon']);
```

### Prices for a particular item

```php
$client->catalog->prices(['item_id' => 12345]);
```

### New items by site and category

```php
$client->catalog->newest(['site' => 'codecanyon', 'category' => 'php-scripts']);
```

### Find featured items

```php
$client->catalog->featured(['site' => 'codecanyon']);
```

### Random new items

```php
$client->catalog->random(['site' => 'codecanyon']);
```


## Profile

The `profile` category represents a public Envato user.

### List all of current user's collections

```php
$client->profile->collections();
```

### Look up a collection by ID

```php
$client->profile->collection(['id' => 12345]);
```

### Get a user's profile details

```php
$client->profile->details(['username' => 'baileyherbert']);
```

### List a user's badges

```php
$client->profile->badges(['username' => 'baileyherbert']);
```

### Get a user's items by site

```php
$client->profile->portfolio(['username' => 'baileyherbert']);
```

### Get a user's newest items

```php
$client->profile->newest(['username' => 'baileyherbert', 'site' => 'codecanyon']);
```


## User

The `user` category represents the currently-authenticated user.


### List an author's sales

```php
$client->user->sales();
```

### Look up a sale by purchase code

```php
$client->user->sale(['code' => '*****']);
```

### List a buyer's purchases

```php
$client->user->purchases();
```

### Look up a buyer's purchase by code

```php
$client->user->purchase(['code' => '*****']);
```

### Download a buyer's purchase

```php
$client->user->download(['purchase_code' => '*****']);
$client->user->download(['item_id' => '123456']);
```

### Get private account details

```php
$client->user->details();
```

### Get the current user's username

```php
$client->user->username();
```

### Get the current user's email

```php
$client->user->email();
```

### Get the user's earnings by month

```php
$client->user->earnings();
```

### Get the user's statement

```php
$client->user->statement([
  'page' => 1,
  'from_date' => '2021-02-01',
  'to_date' => '2022-06-21',
  'type' => 'Sale',
  'site' => 'codecanyon.net'
]);
```


## Market

### Get total number of users

```php
$client->market->users();
```

### Get total number of items

```php
$client->market->items();
```

### Get total number of items by site

```php
$client->market->site(['site' => 'codecanyon']);
```

## Other Endpoints

### Get the client's identity

```php
$identity = $client->getIdentity();
```

The identity will be an object that looks like this:

```php
object(stdClass)#1 (4) {
  ["clientId"]=> NULL
  ["userId"]=> int(1908998)
  ["scopes"]=> array(18) {
    [0]=> string(7) "default"
    [1]=> string(13) "user:username"
    [2]=> string(10) "user:email"
    [3]=> string(12) "user:account"
    [4]=> string(14) "user:financial"
    [5]=> string(17) "purchase:download"
    [6]=> string(12) "sale:history"
    [7]=> string(11) "sale:verify"
  }
  ["ttl"]=> int(315360000)
}
```

If you only care about the `userId` property, you can retrieve it more easily:

```php
$userId = $client->getUserId(); // int(1908998)
```

## Handling Errors & Exceptions

All exceptions in this libary are under the `Herbert\Envato\Exceptions` namespace.

### Authorization Errors

When performing OAuth authorization, you may encounter one of these exceptions:

- `InvalidTokenException` if the token provided is not a string.
- `MissingPropertyException` if OAuth is missing one of the constructor parameters (client_id, client_secret, redirect_uri).
- `NotAuthenticatedException` if you try to construct an `EnvatoClient` before being authenticated.

### Request Errors

When performing a request, there are four possible exceptions that can be thrown.

- `BadRequestException` if required arguments are missing or are invalid.
- `UnauthorizedException` if the current authorization is invalid.
- `TooManyRequestsException` if requests are being throttled for high activity.
- `EndpointException` if there was a major error (API down, no internet connection, etc).

Otherwise, if an error occurs in the request, it will be accessible in detail using the `$response->error` property (which is `null` when successful or a `string` with error details otherwise).

## Examples

### Verifying Purchase Codes

If you're an author and want to check a purchase code provided to you from a buyer, this is an example for you. To make this work, you'll want to use [Personal Token](#personal-token) authentication.

```php
$purchase_code = 'purchase code here';
$token = new Herbert\Envato\Auth\Token('Your Personal Token');
$client = new Herbert\EnvatoClient($token);

$response = $client->user->sale(['code' => $purchase_code]);

if (!$response->error) {
    $sale = $response->results;

    $sold_at = $sale['sold_at']; // "2013-04-16T01:59:35+10:00"
    $license = $sale['license']; // "Regular License"
    $supported_until = $sale['supported_until']; // "2013-10-16T00:00:00+10:00"

    $item_id = $sale['item']['id']; // 1252984
    $item_name = $sale['item']['name']; // "Item Name"
    $author_username = $sale['item']['author_username']; // "baileyherbert"

    $num_licenses = $sale['purchase_count']; // 3
    $buyer_username = $sale['buyer']; // "bestbuyerever"

    echo "I got information!";
}
else {
    echo "The code produced an error:\n";
    echo $response->error;
}
```

## Breaking changes in v3

If upgrading the package to `v3` from an earlier version, there is a single breaking change. The `user->sales()` method
was pointing to the wrong endpoint.

- The previous `$client->user->sales()` endpoint has been renamed to `$client->user->earnings()`
- The new `$client->user->earnings()` endpoint lists your earnings by month
- The new `$client->user->sales()` endpoint lists your individual sales

## Contributors

Special thanks to the following contributors for their help in maintaining this package:

- [@gdarko](https://github.com/gdarko)
- [@Dibbyo456](https://github.com/Dibbyo456)
- [@evrpress](https://github.com/evrpress)
- [@Demonicious](https://github.com/Demonicious)

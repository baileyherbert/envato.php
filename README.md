# Envato.php

An API client for Envato in PHP, with simplified OAuth, token storage, and request sending.

## Contents

- [Notes](#notes)
- [Installation](#installation)
- [Authentication](#authentication)
    - [Personal Token](#personal-token)
    - [OAuth](#oauth)
    - [Persistent OAuth](#persistent-oauth)
- [Sending Requests](#sending-requests)
    - [Getting Request Time](#getting-request-time)
- [Catalog](#catalog)
    - [Look up a public collection](#look-up-a-public-collection)
    - [Look up a single item](#look-up-a-single-item)
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
    - [Get private account details](#get-private-account-details)
    - [Get the current user's username](#get-the-current-users-username)
    - [Get the current user's email](#get-the-current-users-email)
    - [Get the user's sales by month](#get-the-users-sales-by-month)
- [Market](#market)
    - [Get total number of users](#get-total-number-of-users)
    - [Get total number of items](#get-total-number-of-items)
    - [Get total number of items by site](#get-total-number-of-items-by-site)
- [Handling Errors & Exceptions](#handling-errors-exceptions)
    - [Authorization Errors](#authorization-errors)
    - [Request Errors](#request-errors)
- [Examples](#examples)
    - [Verifying Purchase Codes](#verifying-purchase-codes)

## Notes

This API client is fully working though not necessarily completed. Of course, any updates that aren't backwards-compatible will be bumped up a major version. Other than that, here's some info you'll probably want to know.

- This client does **not** disable SSL. It ships with a Certificate Authority bundle and uses this bundle to verify the Envato API's SSL certificate, instead of relying on the system's often-unavailable certificate.

- Major overhauls to response objects and OAuth are planned in a future `3.0` release.

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

This is a bit more complicated. Using OAuth means you'll need to redirect your users. In general, the below code will work fine. If you wish to store the OAuth session (for example, in a database or cookie) to be able to load it later, see [Persistent OAuth](#persistent-oauth).

```php
// Generate the OAuth object
$oauth = new Herbert\Envato\Auth\OAuth([
    'client_id' => 'Your client id',
    'client_secret' => 'Your client secret',
    'redirect_uri' => 'https://your-redirect-uri.com/'
]);

// Get the token (returns null if unavailable)
$token = $oauth->token;

// Redirect the user if they're not authorized yet
if (!$token) {
    header("Location: " . $oauth->getAuthorizationUri());
    die;
}

// Create the client
$client = new Herbert\EnvatoClient($token);
```

- The `OAuth` object contains methods to generate an authorization URI.
- To make this example work, the `redirect_uri` should point back to the current file.
- Calling `$oauth->token` will automatically recognize the `code` sent back by Envato and will return something truthy that you can pass into `EnvatoClient()`.

### Persistent OAuth

In the example above, we authenticated using OAuth. This redirected the user to the Envato API for authorization, and generated a new set of access and refresh tokens.

However, in many cases, we will want to _save_ this information for future use. Fortunately, this is easy to do.

```php
$oauth = new Herbert\Envato\Auth\OAuth([
    'client_id' => 'Your client id',
    'client_secret' => 'Your client secret',
    'redirect_uri' => 'https://your-redirect-uri.com/'
]);

// Load an OAuth session
if (isset($_SESSION['oauth_session'])) {
    $oauth->load($_SESSION['oauth_session']);
}

// No saved session, so let's start a new one
else {
    if ($oauth->token) {
        // Save the OAuth session
        $_SESSION['oauth_session'] = $oauth->session;
    }
    else {
        // User is not logged in, so redirect them
        header("Location: " . $oauth->getAuthorizationUri());
        die;
    }
}

// Create the client
$client = new Herbert\EnvatoClient($oauth->token);
```

- The `$oauth->session` member will contain a JSON string with data for the current authorization.
- The `$oauth->load()` method accepts that JSON string and uses it to load the authorization.
- You can create a new `EnvatoClient` after this, like normal.
- When the access token provided by Envato expires, the client will be able to automatically create a new access token using the saved session data.

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


## Catalog

### Look up a public collection

```php
$client->catalog->collection(['id' => 12345]);
```

### Look up a single item

```php
$client->catalog->item(['id' => 12345]);
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
$client->catalog->popular(['site' => 'codecanyon.net']);
```

### Categories by site

```php
$client->catalog->categories(['site' => 'codecanyon.net']);
```

### Prices for a particular item

```php
$client->catalog->prices(['item_id' => 12345]);
```

### New items by site and category

```php
$client->catalog->prices(['site' => 'codecanyon.net', 'category' => 'php-scripts']);
```

### Find featured items

```php
$client->catalog->featured(['site' => 'codecanyon.net']);
```

### Random new items

```php
$client->catalog->prices(['site' => 'codecanyon.net']);
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
$client->profile->portfolio(['username' => 'baileyherbert', 'site' => 'codecanyon.net']);
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

### Get the user's sales by month

```php
$client->user->sales();
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
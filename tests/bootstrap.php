<?php

require __DIR__ . '/../vendor/autoload.php';

// Configure an app for testing
// Register one here: https://build.envato.com/my-apps/
define('TEST_OAUTH_SECRET', 'Enter a client secret here for testing');
define('TEST_OAUTH_REDIRECT', 'https://localhost/');
define('TEST_OAUTH_CLIENT', 'oauth-client-id123456');

// Create a token with the OAuth app above and paste it here
define('TEST_OAUTH_CODE', '');

// Configure a personal token for testing
// Register one here: https://build.envato.com/my-apps/
define('TEST_PERSONAL_TOKEN', 'Enter a personal token here for testing');

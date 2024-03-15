<?php

require dirname(__DIR__) . '/vendor/autoload.php';

if (!isset($_ENV['ENVATO_PERSONAL_TOKEN'])) {
	echo 'ENVATO_TOKEN environment variable required';
	exit(1);
}

// Configure an app for testing
// Register one here: https://build.envato.com/my-apps/
define('TEST_OAUTH_SECRET', '');
define('TEST_OAUTH_REDIRECT', '');
define('TEST_OAUTH_CLIENT', '');

// Create a token with the OAuth app above and paste it here
define('TEST_OAUTH_CODE', '');

// Configure a personal token for testing
// Register one here: https://build.envato.com/my-apps/
define('TEST_PERSONAL_TOKEN', $_ENV['ENVATO_PERSONAL_TOKEN']);

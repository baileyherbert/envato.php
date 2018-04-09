<?php

namespace Herbert\Tests\Envato;

use Herbert\Envato\Auth\OAuth;
use Herbert\Envato\Auth\Token;
use PHPUnit\Framework\TestCase;

class OAuthTest extends TestCase
{
    public function testOAuthRedirection() {
        $oauth = new OAuth([
            'redirect_uri' => TEST_OAUTH_REDIRECT,
            'client_id' => TEST_OAUTH_CLIENT,
            'client_secret' => TEST_OAUTH_SECRET
        ]);

        $this->assertNotNull($oauth->getAuthorizationUri(), 'Failed to generate a redirection URI.');
    }

    public function testOAuthTokenGeneration() {
        if (empty(TEST_OAUTH_CODE)) {
            echo "Skipping token generation test (no token specified in tests/bootstrap.php).";
            $this->assertTrue(true);
            return;
        };

        // Inject into GET
        $_GET['code'] = TEST_OAUTH_CODE;

        // Variables
        $token = "";
        $uri = "";

        // Run the OAuth request
        try {
            $oauth = new OAuth([
                'redirect_uri' => TEST_OAUTH_REDIRECT,
                'client_id' => TEST_OAUTH_CLIENT,
                'client_secret' => TEST_OAUTH_SECRET,
                'store' => function ($session) {
                    $this->assertJson($session, 'The session object provided to the storage callback was not valid JSON.');
                    $this->assertNotEmpty($session, 'The session object provided to the storage callback was empty.');
                }
            ]);

            $token = $oauth->token;
            $uri = $oauth->getAuthorizationUri();
        }
        catch (\Exception $e) {
            $this->fail('Got an exception: ' . $e->getMessage());
        }

        $this->assertNotNull($token, 'Failed to generate a token with the current test code. Go here and get a new code: ' . $uri);
        $this->assertInstanceOf(Token::class, $token, 'Did not return an authenticated Token object.');
        $this->assertNotNull($token->expires, 'Authenticated Token object does not have an expiration time as expected.');
        $this->assertNotNull($token->token, 'Authenticated Token object does not have a token as expected.');
    }
}

<?php

namespace Herbert\Tests\Envato;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Herbert\Envato\Auth\OAuth;
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
        $auth = null;

        // Run the OAuth request
        try {
            $body = json_encode(array(
                'refresh_token' => 'PZzGKkWLo9w7QVBLwQbOpWLksVjzjyDY',
                'token_type' => 'bearer',
                'access_token' => 'kh2UUTkaxqbV4zS93xMT2knjGdBbtbVq',
                'expires_in' => 3600
            ));

            $response = new Response(200, [], $body);
            $mock = new MockHandler([ $response ]);
            $handler = HandlerStack::create($mock);

            $oauth = new OAuth([
                'redirect_uri' => TEST_OAUTH_REDIRECT,
                'client_id' => TEST_OAUTH_CLIENT,
                'client_secret' => TEST_OAUTH_SECRET,
                'store' => function ($session) {
                    $this->assertJson($session, 'The session object provided to the storage callback was not valid JSON.');
                    $this->assertNotEmpty($session, 'The session object provided to the storage callback was empty.');
                }
            ], null, ['handler' => $handler]);

            $token = $oauth->token;
            $auth = $oauth->auth;
            $uri = $oauth->getAuthorizationUri();
        }
        catch (\Exception $e) {
            $this->fail('Got an exception: ' . $e->getMessage());
        }

        $this->assertNotNull($auth, 'Failed to generate a token with the current test code. Go here and get a new code: ' . $uri);
        $this->assertTrue(is_string($token), 'Did not return an authenticated Token object.');
        $this->assertNotNull($auth->expires, 'Authenticated Token object does not have an expiration time as expected.');
        $this->assertNotNull($auth->token, 'Authenticated Token object does not have a token as expected.');
    }
}

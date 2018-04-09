<?php

namespace Herbert\Tests\Envato;

use Herbert\Envato\Auth\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testPersonalToken() {
        $token = new Token(TEST_PERSONAL_TOKEN);
        $this->assertEquals(TEST_PERSONAL_TOKEN, $token->token);
    }

    public function testOAuthTokenArray() {
        $token = new Token(array(
            'access_token' => TEST_PERSONAL_TOKEN,
            'refresh_token' => TEST_PERSONAL_TOKEN,
            'expires' => time()
        ));

        $this->assertEquals(TEST_PERSONAL_TOKEN, $token->token);
    }

    public function testOAuthTokenJson() {
        $token = new Token(json_encode(array(
            'access_token' => TEST_PERSONAL_TOKEN,
            'refresh_token' => TEST_PERSONAL_TOKEN,
            'expires' => time()
        )));

        $this->assertEquals(TEST_PERSONAL_TOKEN, $token->token);
    }
}

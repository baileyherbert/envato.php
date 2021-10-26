<?php

namespace Herbert\Tests\Envato;

use Herbert\Envato\Auth\Token;
use Herbert\Envato\Endpoint;
use Herbert\Envato\ResultSet;
use Herbert\Envato\Schema;
use Herbert\EnvatoClient;
use PHPUnit\Framework\TestCase;

class EndpointTest extends TestCase
{
    public function testSchema() {
        $schema = (new Schema())();
        $this->assertArrayHasKey('market', $schema, 'Got an invalid schema.');
    }

    public function testRetrieveEndpoint() {
        $token = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN));

        $this->assertInstanceOf(Endpoint::class, $token->market);
    }

    public function testPerformEndpoint() {
        $client = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN));
        $items = $client->market->items();

        // Must be a ResultSet
        $this->assertInstanceOf(ResultSet::class, $items, 'Endpoint performed but did not return a ResultSet.');
    }

    public function testPerformEndpointWithVariables() {
        $client = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN));
        $items = $client->market->site([
            'site' => 'themeforest'
        ]);

        // Must be a ResultSet
        $this->assertInstanceOf(ResultSet::class, $items, 'Endpoint performed but did not return a ResultSet.');
    }

    public function testRetrieveIdentity() {
        $client = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN));
        $identity = $client->getIdentity();
        $userId = $client->getUserId();

        $this->assertInternalType('int', $userId);
        $this->assertInternalType('array', $identity->scopes);
        $this->assertEquals($userId, $identity->userId);
    }
}

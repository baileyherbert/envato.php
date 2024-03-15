<?php

namespace Herbert\Tests\Envato;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Herbert\Envato\Auth\Token;
use Herbert\Envato\Endpoint;
use Herbert\Envato\ResultSet;
use Herbert\Envato\Schema;
use Herbert\EnvatoClient;
use PHPUnit\Framework\TestCase;

class EndpointTest extends TestCase
{
    public function testSchema() {
        $schema = (new Schema());
        $schema = $schema();

        $this->assertArrayHasKey('market', $schema, 'Got an invalid schema.');
    }

    public function testRetrieveEndpoint() {
        $token = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN));

        $this->assertInstanceOf(Endpoint::class, $token->market);
    }

    public function testPerformEndpoint() {
        $body = json_encode(array('matches' => array()));
        $response = new Response(200, [], $body);
        $mock = new MockHandler([ $response ]);
        $handler = HandlerStack::create($mock);

        $client = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN), [
            'handler' => $handler
        ]);

        $items = $client->market->items();

        // Must be a ResultSet
        $this->assertInstanceOf(ResultSet::class, $items, 'Endpoint performed but did not return a ResultSet.');
    }

    public function testPerformEndpointWithVariables() {
        $body = json_encode(array('matches' => array()));
        $response = new Response(200, [], $body);
        $mock = new MockHandler([ $response ]);
        $handler = HandlerStack::create($mock);

        $client = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN), [
            'handler' => $handler
        ]);

        $items = $client->market->site([
            'site' => 'themeforest'
        ]);

        // Must be a ResultSet
        $this->assertInstanceOf(ResultSet::class, $items, 'Endpoint performed but did not return a ResultSet.');
    }

    public function testRetrieveIdentity() {
        $body = json_encode(array(
            'clientId' => null,
            'userId' => TEST_USER_ID,
            'scopes' => array(),
            'ttl' => 315360000
        ));

        $response = new Response(200, [], $body);
        $mock = new MockHandler([ $response, $response ]);
        $handler = HandlerStack::create($mock);

        $client = new EnvatoClient(new Token(TEST_PERSONAL_TOKEN), [
            'handler' => $handler
        ]);

        $identity = $client->getIdentity();
        $userId = $client->getUserId();

        $this->assertTrue(is_int($userId));
        $this->assertTrue(is_array($identity->scopes));
        $this->assertEquals($userId, $identity->userId);
    }
}

<?php

namespace Herbert\Envato;

use GuzzleHttp\Client;
use Herbert\Envato\Exceptions\BadRequestException;
use Herbert\Envato\Exceptions\TooManyRequestsException;
use Herbert\Envato\Exceptions\UnauthorizedException;
use Herbert\EnvatoClient;

class RequestWriter {

	/**
	 * @var EnvatoClient
	 */
	private $client;

	/**
	 * Constructs a new `RequestWriter` instance.
	 *
	 * @param EnvatoClient $client
	 */
	public function __construct(EnvatoClient $client) {
		$this->client = $client;
	}

	/**
	 * Sends a `GET` request to the specified path on the API. If an array of variables is provided, they will be
	 * appended to the request as query parameters.
	 */
	public function get($uri, array &$variables = array()) {
		return $this->send('GET', $uri, $variables);
	}

	/**
	 * Sends a `POST` request to the specified path on the API. If an array of variables is provided, they will be
	 * appended to the request as query parameters.
	 */
	public function post($uri, array &$variables = array()) {
		return $this->send('POST', $uri, $variables);
	}

	/**
	 * Sends a `PUT` request to the specified path on the API. If an array of variables is provided, they will be
	 * appended to the request as query parameters.
	 */
	public function put($uri, array &$variables = array()) {
		return $this->send('PUT', $uri, $variables);
	}

	/**
	 * Sends a `PATCH` request to the specified path on the API. If an array of variables is provided, they will be
	 * appended to the request as query parameters.
	 */
	public function patch($uri, array &$variables = array()) {
		return $this->send('PATCH', $uri, $variables);
	}

	/**
	 * Sends a `delete` request to the specified path on the API. If an array of variables is provided, they will be
	 * appended to the request as query parameters.
	 */
	public function delete($uri, array &$variables = array()) {
		return $this->send('DELETE', $uri, $variables);
	}

	/**
	 * @param string $method
	 * @param string $uri
	 * @param array $variables
	 *
	 * @return ResultSet
	 *
	 * @throws Exceptions\AuthenticationException
	 * @throws Exceptions\InvalidTokenException
	 */
	private function send($method, &$uri, array &$variables) {
		// Get start time
		$start_time = microtime(true);

		// Create a default client
		$client = $this->createHttpClient();

		// Build the HTTP query
		if(!empty($variables)) {
			$query = http_build_query($variables);
			$uri  .= '?' . $query;
		}

		// Send the request
		$response = $client->request($method, $uri, [
			'http_errors' => false
		]);

		// Parse the request
		$data = json_decode($response->getBody(), true);

		// Handle errors
		if (isset($data['error_message'])) {
			return new ResultSet(null, (microtime(true) - $start_time), $data['error_message']);
		}

		// 400 Bad Request
		elseif ($response->getStatusCode() == 400) {
			throw new BadRequestException();
		}

		// 401 Unauthorized
		elseif ($response->getStatusCode() == 401) {
			throw new UnauthorizedException();
		}

		// 429 Too Many Requests
		elseif ($response->getStatusCode() == 429) {
			throw new TooManyRequestsException(
				$response->hasHeader('Retry-After') ?
				intval($response->getHeader('Retry-After')[0]) : 0
			);
		}

		// Generate response object
		else {
			return new ResultSet($data, (microtime(true) - $start_time));
		}
	}

	/**
	 * @return Client
	 *
	 * @throws Exceptions\AuthenticationException
	 * @throws Exceptions\InvalidTokenException
	 */
	private function createHttpClient() {
		return new Client([
			'headers' => [
				'User-Agent' => $this->client->userAgent,
				'Authorization' => 'Bearer ' . $this->client->getToken()
			],
			'verify' => dirname(dirname(__DIR__)) . '/data/ca-bundle.crt',
			'base_uri' => $this->client->baseUri
		]);
	}

}

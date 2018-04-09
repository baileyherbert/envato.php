<?php

namespace Herbert\Envato\Auth {

    use Herbert\Envato\Exceptions\InvalidTokenException;

    /**
     * Represents a token-based authentication.
     *
     * @package Herbert\Envato\Auth
     *
     * @property string $token Authentication token.
     * @property int $expires Epoch time at which this token will be expired.
     */
    class Token implements AuthProcedure
    {
        /**
         * @var array Representing an authentication session.
         */
        private $connection = array();

        /**
         * @var callable|null Optional callable which executes whenever the token is renewed.
         */
        private $store;

        /**
         * Starts a new authentication session with a personal token or a stored OAuth session.
         *
         * @param string|array $token Can be a string representing a token, a string in JSON format containing
         *   OAuth session data, or an array containing OAuth session data.
         * @param callable|null $store Optional callable which will execute whenever the token automatically renews
         *   due to becoming expired. Use this to store the updated session in your database.
         *
         * @throws InvalidTokenException when the data is invalid or the JSON could not be parsed.
         */
        public function __construct($token, $store = null) {
            // If the provided data is an array, load it as an OAuth session
            if (is_array($token)) {
                $this->loadOAuthSession($token);
            }

            // If the data is a string, check if it is JSON or a personal token
            elseif (is_string($token)) {
                // The string is JSON, load it as an OAuth session
                if (in_array($token[0], ['[', '{'])) {
                    $this->loadOAuthSession($token);
                }

                // The string is a personal token
                else {
                    $this->connection = array(
                        'token' => trim($token),
                        'expires' => PHP_INT_MAX
                    );
                }
            }

            // Not a valid token
            else {
                throw new InvalidTokenException('Not authenticated: provided token is not valid.');
            }

            // Store the update callback
            $this->store = $store;
        }

        /**
         * @param string|array $data A JSON string or array representing the OAuth session.
         * @throws InvalidTokenException when the data is invalid or the JSON could not be parsed.
         */
        private function loadOAuthSession($data) {
            // Convert the data into an array if not already
            $session = (is_array($data)) ? $data : @json_decode($data, true);

            // Handle any JSON errors
            if (!is_array($data) && json_last_error() != JSON_ERROR_NONE) {
                throw new InvalidTokenException(sprintf('Failed to parse OAuth session data as JSON: %s', json_last_error_msg()));
            }

            // Make sure the "token" is present
            if (!isset($session['access_token'])) {
                throw new InvalidTokenException('Missing field "access_token" in session data.');
            }

            // Make sure the "token" is present
            if (!isset($session['refresh_token'])) {
                throw new InvalidTokenException('Missing field "refresh_token" in session data.');
            }

            // Make sure the "token" is present
            if (!isset($session['expires'])) {
                throw new InvalidTokenException('Missing field "expires" in session data.');
            }

            // Store the session
            $this->connection = array(
                'token' => $session['access_token'],
                'refresh' => $session['refresh_token'],
                'expires' => $session['expires']
            );
        }

        /**
         * @param string $property
         * @return string|int
         * @throws \Exception
         */
        public function __get($property) {
            // Convert the property to lowercase to comply with PHP's case-insensitive property requirement.
            $lower = strtolower($property);

            // Return the value if it exists internally
            if (isset($this->connection[$lower])) {
                return $this->connection[$lower];
            }

            // Throw an understandable error that the property doesn't exist
            throw new \Exception(sprintf("Unknown property '%s' for class '%s'", $property, 'Token'));
        }
    }

}
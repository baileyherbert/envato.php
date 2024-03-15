<?php

namespace Herbert\Envato\Auth {

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\GuzzleException;

    use Herbert\Envato\Exceptions\AuthenticationException;
    use Herbert\Envato\Exceptions\InvalidTokenException;
    use Herbert\Envato\Exceptions\MissingPropertyException;
    use Herbert\EnvatoClient;

    /**
     * Utility for authenticating with the Envato API using OAuth.
     * @package Herbert\Envato\Auth
     *
     * @property AuthProcedure $auth Authentication token object to use when creating a client.
     * @property string $session JSON session string which can be stored and loaded back into a Token object for re-use.
     * @property int $expires The timestamp at which the access token expires.
     * @property string $token The current access token as a string.
     */
    class OAuth implements AuthProcedure
    {
        /**
         * @var Token
         */
        private $generatedToken;

        /**
         * @var array
         */
        private $generatedSession;

        /**
         * @var callable|null Optional callable which executes whenever the token is renewed.
         */
        private $store;

        /**
         * @var string
         */
        private $redirectUri;

        /**
         * @var string
         */
        private $clientId;

        /**
         * @var string
         */
        private $clientSecret;

        /**
         * Additional options to pass when constructing the Guzzle client. Defaults to a blank array.
         *
         * Note that the Envato Client will automatically set the `user-agent` and `authorization` headers, in addition
         * to the CA bundle `verify` and `base_uri` properties, on the client. However, you can override those defaults
         * here.
         *
         * @see https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client
         */
        public $httpOptions = array();

        /**
         * Starts a new OAuth authentication with a personal token or a stored OAuth session.
         *
         * @param array $options An array of options, containing the client_id, client_secret, and redirect_uri.
         *   Optionally, a 'store' callback can be provided in this array to in place of the second argument.
         * @param callable|null $store Optional callable which will execute whenever the token automatically renews
         *   due to becoming expired. Use this to store the updated session in your database.
         * @param array $httpOptions Additional options to pass when constructing the Guzzle client.
         *
         * @throws MissingPropertyException When the options array does not contain all required properties.
         */
        public function __construct(array $options, $store = null, $httpOptions = array())
        {
            // Check for 'store' in the options array
            if (isset($options['store'])) {
                if (is_callable($options['store'])) {
                    $store = $options['store'];
                }
            }

            // Make sure the required parameters are here
            if (!isset($options['client_id'])) throw new MissingPropertyException('Missing property "client_id".');
            if (!isset($options['client_secret'])) throw new MissingPropertyException('Missing property "client_secret".');
            if (!isset($options['redirect_uri'])) throw new MissingPropertyException('Missing property "redirect_uri".');

            // Generate redirect URI
            $this->redirectUri = sprintf(
                'https://api.envato.com/authorization?response_type=code&client_id=%s&redirect_uri=%s',
                urlencode($options['client_id']),
                urlencode($options['redirect_uri'])
            );

            // Store callback
            $this->store = $store;

            // Store HTTP client
            $this->httpOptions = $httpOptions;

            // Store parameters
            $this->clientId = $options['client_id'];
            $this->clientSecret = $options['client_secret'];
        }

        /**
         * Loads a stored OAuth session.
         *
         * @param string|array $token Can be a string representing a token, a string in JSON format containing
         *   OAuth session data, or an array containing OAuth session data.
         *
         * @throws InvalidTokenException when the data is invalid or the JSON could not be parsed.
         */
        public function load($token) {
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
                    throw new InvalidTokenException('Not authenticated: provided token is not valid JSON.');
                }
            }

            // Not a valid token
            else {
                throw new InvalidTokenException('Not authenticated: provided token is not valid.');
            }
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
            $session = array(
                'access_token' => $session['access_token'],
                'refresh_token' => $session['refresh_token'],
                'expires' => $session['expires']
            );

            // Create the token
            $this->generatedSession = $session;
            $this->generatedToken = new Token($session, $this->store);
        }

        /**
         * Returns a new `EnvatoClient` instance for the current user.
         *
         * @return EnvatoClient|null
         */
        public function getClient() {
            $token = $this->auth;

            if (!is_null($token)) {
                return new EnvatoClient($this, $this->httpOptions);
            }

            return null;
        }

        /**
         * @param string $property
         * @return null|Token|string
         *
         * @throws AuthenticationException When the token cannot be generated due to an error.
         * @throws InvalidTokenException If the Envato API returns a null token (should never happen).
         */
        public function __get($property) {
            // Handle auth generation requests
            if ($property == 'auth') {
                // Only if we've just returned from authorization
                if (isset($_GET['code'])) {
                    if (!$this->generatedToken) {
                        $this->generateToken(trim($_GET['code']));
                    }
                }

                return $this->generatedToken;
            }

            // Handle session requests
            if ($property == 'session') {
                return json_encode($this->generatedSession);
            }

            // Handle token requests
            if ($property == 'token') {
                if (!isset($this->generatedToken)) {
                    if (isset($_GET['code'])) {
                        $this->generateToken(trim($_GET['code']));
                    }
                    else {
                        return null;
                    }
                }

                return $this->generatedToken->token;
            }

            // Handle expiration requests
            if ($property == 'expires') {
                return $this->generatedToken->expires;
            }

            return null;
        }

        /**
         * @return string Absolute URL to which the user must be redirected for authorization.
         */
        public function getAuthorizationUri()
        {
            return $this->redirectUri;
        }

        /**
         * @param string $code
         * @return null
         *
         * @throws AuthenticationException
         * @throws InvalidTokenException
         */
        private function generateToken($code) {
            $overrides = $this->httpOptions;
            $options = array(
                'headers' => [
                    'user-agent' => 'Envato.php (https://github.com/baileyherbert/envato.php)'
                ],
                'verify' => dirname(dirname(dirname(__DIR__))) . '/data/ca-bundle.crt'
            );

            if (is_array($overrides)) {
                foreach ($overrides as $key => $value) {
                    $key = strtolower($key);

                    if ($key !== 'headers') {
                        $options[$key] = $value;
                    }
                    else if (is_array($value)) {
                        foreach ($value as $headerName => $headerValue) {
                            $headerName = strtolower($headerName);
                            $options['headers'][$headerName] = $headerValue;
                        }
                    }
                }
            }

            try {
                $client = new Client($options);
                $response = $client->request('POST', 'https://api.envato.com/token', [
                    'form_params' => [
                        'grant_type' => 'authorization_code',
                        'code' => $code,
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret
                    ],
                    'http_errors' => false
                ]);

                // Parse the response
                $data = json_decode($response->getBody(), true);

                // Generate the session
                if (isset($data['refresh_token'])) {
                    $session = array(
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'],
                        'expires' => (time() + $data['expires_in']) - 60
                    );

                    // Create the token
                    $this->generatedSession = $session;
                    $this->generatedToken = new Token($session, $this->store);

                    // Call the callback
                    if (is_callable($this->store)) {
                        $f = $this->store;
                        $f($this->session);
                    }

                    // Return self to indicate successful token generation
                    return $this;
                }
                elseif (isset($data['error_description'])) {
                    if ($data['error_description'] === 'Code not found') {
                        return null;
                    }

                    throw new AuthenticationException(sprintf('Failed to generate token: %s', $data['error_description']));
                }
            }
            catch (GuzzleException $e) {
                throw new AuthenticationException(sprintf('Failed to generate token: %s', $e->getMessage()));
            }

            return null;
        }

        /**
         * Refreshes the session to get a new acess token.
         *
         * @return bool True if successful.
         *
         * @throws AuthenticationException When the token cannot be generated due to an error.
         * @throws InvalidTokenException If the Envato API returns a null token (should never happen).
         */
        public function refresh() {
            if (!isset($this->generatedSession['refresh_token'])) return false;

            $client = new Client([
                'headers' => [
                    'User-Agent' => 'https://github.com/baileyherbert/envato.php'
                ],
                'verify' => dirname(dirname(dirname(__DIR__))) . '/data/ca-bundle.crt'
            ]);

            try {
                $response = $client->request('POST', 'https://api.envato.com/token', [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $this->generatedSession['refresh_token'],
                        'client_id' => $this->clientId,
                        'client_secret' => $this->clientSecret
                    ],
                    'http_errors' => false
                ]);

                // Parse the response
                $data = json_decode($response->getBody(), true);

                // Generate the session
                if (isset($data['access_token'])) {
                    $session = array(
                        'access_token' => $data['access_token'],
                        'refresh_token' => $this->generatedSession['refresh_token'],
                        'expires' => (time() + $data['expires_in']) - 60
                    );

                    // Create the token
                    $this->generatedSession = $session;
                    $this->generatedToken = new Token($session, $this->store);

                    // Call the callback
                    if (is_callable($this->store)) {
                        $f = $this->store;
                        $f($this->session);
                    }

                    // Return true to indicate success
                    return true;
                }
                elseif (isset($data['error_description'])) {
                    throw new AuthenticationException(sprintf('Failed to generate token: %s', $data['error_description']));
                }
            }
            catch (GuzzleException $e) {
                throw new AuthenticationException(sprintf('Failed to generate token: %s', $e->getMessage()));
            }
        }
    }

}

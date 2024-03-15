<?php

namespace Herbert {

    use Herbert\Envato\Auth\AuthProcedure;
    use Herbert\Envato\Auth\OAuth;
    use Herbert\Envato\Endpoint;
    use Herbert\Envato\Exceptions\AuthenticationException;
    use Herbert\Envato\Exceptions\InvalidTokenException;
    use Herbert\Envato\Exceptions\NotAuthenticatedException;
    use Herbert\Envato\RequestWriter;
    use Herbert\Envato\Schema;
    use Herbert\Envato\Stubs\CatalogStub;
    use Herbert\Envato\Stubs\MarketStub;
    use Herbert\Envato\Stubs\ProfileStub;
    use Herbert\Envato\Stubs\UserStub;

    /**
     * Represents an authenticated connection to and interface for accessing the Envato API.
     *
     * @package Herbert
     *
     * @property MarketStub $market
     * @property CatalogStub $catalog
     * @property ProfileStub $profile
     * @property UserStub $user
     */
    class EnvatoClient
    {
        /**
         * @var AuthProcedure
         */
        private $procedure;

        /**
         * @var array
         */
        private $schema;

        /**
         * The base URI for all requests to the API. You should change this if using a sandbox API instead.
         *
         * @var string
         */
        public $baseUri = 'https://api.envato.com/';

        /**
         * The user agent to send with requests. For high volume users, it is recommended to overwrite this and use it
         * to specify why/how you're using the API. The Envato Team can see this agent and may use it to adjust your
         * rate limit.
         */
        public $userAgent = 'Envato.php (https://github.com/baileyherbert/envato.php)';

        /**
         * The request writer instance is used to send outgoing requests to the API.
         *
         * @var RequestWriter
         */
        public $request;

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
         * Starts a new client connection with the specified authentication procedure. The procedure must be
         * completed prior to the constructor being called; that is, it must have already established the token
         * and expiration time.
         *
         * @param AuthProcedure $auth Procedure to authenticate with.
         * @param array $httpOptions Additional options to pass when constructing the Guzzle client.
         *
         * @throws NotAuthenticatedException if the authentication procedure is not completed or has failed.
         */
        public function __construct(AuthProcedure $auth, $httpOptions = array()) {
            // Ensure that we are already authenticated
            if ($auth->token == null) {
                throw new NotAuthenticatedException();
            }

            // Store the HTTP options
            $this->httpOptions = $httpOptions;

            // Store the procedure
            $this->procedure = $auth;
            $this->request = new RequestWriter($this);

            // Get the schema
            $o = (new Schema());
            $this->schema = $o();
        }

        /**
         * Creates an endpoint helper class for the specified schema collection.
         */
        public function __get($property) {
            $name = strtolower($property);

            if (isset($this->schema[$name])) {
                return new Endpoint($this, $this->schema[$name]);
            }
        }

        /**
         * @return string Current authorization bearer token.
         *
         * @throws AuthenticationException When the token cannot be generated due to an error.
         * @throws InvalidTokenException If the Envato API returns a null token (should never happen).
         */
        public function getToken() {
            if ($this->procedure->expires <= time()) {
                if ($this->procedure instanceof OAuth) {
                    $this->procedure->refresh();
                }
            }

            return $this->procedure->token;
        }

        /**
         * Returns the unique Envato ID, permission scopes, and TTL for the authenticated user and token.
         */
        public function getIdentity() {
            return (object) $this->request->get('/whoami')->raw();
        }

        /**
         * Returns the unique Envato ID for the authenticated user.
         */
        public function getUserId() {
            $identity = $this->getIdentity();
            return $identity->userId;
        }

    }

}

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

    /**
     * Represents an authenticated connection to and interface for accessing the Envato API.
     *
     * @package Herbert
     *
     * @property Endpoint $market
     * @property Endpoint $catalog
     * @property Endpoint $profile
     * @property Endpoint $user
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
        public $userAgent = 'https://github.com/baileyherbert/envato.php';

        /**
         * The request writer instance is used to send outgoing requests to the API.
         *
         * @var RequestWriter
         */
        public $request;

        /**
         * Starts a new client connection with the specified authentication procedure. The procedure must be
         * completed prior to the constructor being called; that is, it must have already established the token
         * and expiration time.
         *
         * @param AuthProcedure $auth Procedure to authenticate with.
         *
         * @throws NotAuthenticatedException if the authentication procedure is not completed or has failed.
         */
        public function __construct(AuthProcedure $auth) {
            // Ensure that we are already authenticated
            if ($auth->token == null) {
                throw new NotAuthenticatedException();
            }

            // Store the procedure
            $this->procedure = $auth;
            $this->request = new RequestWriter($this);

            // Get the schema
            $o = (new Schema());
            $this->schema = $o();
        }

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

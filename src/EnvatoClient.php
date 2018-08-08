<?php

namespace Herbert {

    use Herbert\Envato\Auth\AuthProcedure;
    use Herbert\Envato\Auth\OAuth;
    use Herbert\Envato\Endpoint;
    use Herbert\Envato\Exceptions\AuthenticationException;
    use Herbert\Envato\Exceptions\InvalidTokenException;
    use Herbert\Envato\Exceptions\NotAuthenticatedException;
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
                if ($this->procedure instanceof OAuth)
                $this->procedure->refresh();
            }

            return $this->procedure->token;
        }
    }

}

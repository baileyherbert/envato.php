<?php

namespace Herbert\Envato {

    use Herbert\EnvatoClient;

    class Endpoint
    {
        /**
         * @var EnvatoClient
         */
        private $client;

        /**
         * @var array
         */
        private $schema;

        /**
         * Endpoint constructor.
         * @param EnvatoClient $client
         * @param array $schema
         */
        public function __construct(EnvatoClient $client, array $schema) {
            $this->client = $client;
            $this->schema = $schema;
        }

        /**
         * @param $name
         * @param $arguments
         *
         * @return ResultSet
         *
         * @throws Exceptions\AuthenticationException
         * @throws Exceptions\InvalidTokenException
         */
        public function __call($name, $arguments) {
            $name = strtolower($name);
            $variables = array();

            // Set variables if the first argument is an array
            if (count($arguments) > 0 && is_array($arguments[0])) {
                $variables = $arguments[0];
            }

            // Check if we're calling a valid endpoint
            if (isset($this->schema[$name])) {
                // Get the endpoint URI
                $uri = $this->schema[$name];

                // Replace any variables inside the URL
                $this->populateVariables($uri, $variables);

                // Perform the request
                return $this->client->request->get($uri, $variables);
            }
        }

        private function populateVariables(&$uri, array &$variables) {
            // Loop through variables to replace in-URI variables
            foreach ($variables as $name => $value) {
                // Get the lowercase version of the name
                $lowerName = strtolower($name);

                // Replace any in-URI variables
                $uri = str_replace("{{$lowerName}}", $value, $uri, $num);

                // Unset the variable if any replacements occurred
                if ($num > 0) {
                    unset($variables[$name]);
                }
            }
        }
    }

}

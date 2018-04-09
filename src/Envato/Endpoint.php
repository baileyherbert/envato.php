<?php

namespace Herbert\Envato {

    use GuzzleHttp\Client;
    use Herbert\Envato\Exceptions\BadRequestException;
    use Herbert\Envato\Exceptions\EndpointException;
    use Herbert\Envato\Exceptions\TooManyRequestsException;
    use Herbert\Envato\Exceptions\UnauthorizedException;
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
                return $this->perform($uri, $variables);
            }
        }

        /**
         * @param $uri
         * @param array $variables
         *
         * @return ResultSet
         *
         * @throws Exceptions\AuthenticationException
         * @throws Exceptions\InvalidTokenException
         */
        private function perform(&$uri, array &$variables) {
            // Get start time
            $start_time = microtime(true);

            // Create a default client
            $client = $this->createHttpClient();

            // Build the HTTP query
            $query = http_build_query($variables);

            // Send the request
            $response = $client->request('GET', $uri, [
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
                throw new TooManyRequestsException();
            }

            // Generate response object
            else {
                return new ResultSet($data, (microtime(true) - $start_time));
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

        /**
         * @return Client
         *
         * @throws Exceptions\AuthenticationException
         * @throws Exceptions\InvalidTokenException
         */
        private function createHttpClient() {
            return new Client([
                'headers' => [
                    'User-Agent' => 'https://github.com/baileyherbert/envato.php',
                    'Authorization' => 'Bearer ' . $this->client->getToken()
                ],
                'verify' => dirname(dirname(__DIR__)) . '/data/ca-bundle.crt',
                'base_uri' => 'https://api.envato.com/'
            ]);
        }
    }

}
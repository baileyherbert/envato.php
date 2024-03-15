<?php

namespace Herbert\Envato {

    /**
     * @property array $results
     * @property string|null $error The error message from Envato, if any.
     * @property float $time The time it took to execute the request, in seconds.
     */
    class ResultSet
    {
        private $results = array();
        private $error = null;
        private $time = 0;

        /**
         * ResultSet constructor.
         *
         * @param $results
         * @param $time
         * @param null $error
         */
        public function __construct($results, $time, $error = null) {
            $this->results = $results;
            $this->time = $time;
            $this->error = $error;
        }

        public function __get($property) {
            if (isset($this->$property)) {
                return $this->$property;
            }

            if (isset($this->results[$property])) {
                return $this->results[$property];
            }
        }

        /**
         * Returns the raw response as an associative array.
         */
        public function raw() {
            return $this->results;
        }
    }

}

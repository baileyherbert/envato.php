<?php

namespace Herbert\Envato\Exceptions {
    class TooManyRequestsException extends EnvatoException
    {
        private $epoch;

        public function __construct($seconds) {
            $this->epoch = microtime(true) + $seconds;
        }

        /**
         * Returns the number of seconds remaining until the next available request.
         * @return float
         */
        public function getSecondsRemaining() {
            return max(0, $this->epoch - microtime(true));
        }

        /**
         * Returns an epoch (unix) timestamp containing the time when the request can be retried. Note that this
         * timestamp is in seconds, but contains microseconds.
         *
         * @return float
         */
        public function getRetryTime() {
            return $this->epoch;
        }

        /**
         * Sleeps the script until the `Retry-After` time.
         */
        public function wait() {
            $seconds = ceil($this->getSecondsRemaining());

            if ($seconds > 0) {
                sleep($seconds);
            }
        }
    }
}


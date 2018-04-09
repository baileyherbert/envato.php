<?php

namespace Herbert\Envato\Exceptions {
    class NotAuthenticatedException extends EnvatoException
    {
        public function __construct() {
            parent::__construct('The provided authentication procedure was not completed.');
        }
    }
}


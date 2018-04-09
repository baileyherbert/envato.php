<?php

namespace Herbert\Envato\Auth {

    /**
     * Interface AuthProcedure
     * @package Herbert\Envato\Auth
     *
     * @property string $token Authentication token.
     * @property int $expires Epoch time at which this token will be expired.
     */
    interface AuthProcedure
    {
        public function __get($name);
    }

}
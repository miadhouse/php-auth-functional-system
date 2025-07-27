<?php

namespace Aqayepardakht\PhpSdk;

use Aqayepardakht\PhpSdk\Services\{
    GatewayService, 
    AccountService
};

class Api { 
    public function gateway($pin) {        
        return new GatewayService($pin);
    }

    public function account($accountNumber = null, $code = null) {
        return new AccountService($accountNumber, $code);
    }
}
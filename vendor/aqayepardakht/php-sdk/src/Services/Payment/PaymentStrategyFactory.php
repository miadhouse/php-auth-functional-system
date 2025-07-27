<?php

namespace Aqayepardakht\PhpSdk\Services\Payment;

use Aqayepardakht\PhpSdk\Services\Payment\Strategy\{
    CreatePaymentStrategy,
    VerifyPaymentStrategy,
    StartPaymentStrategy
};
use Aqayepardakht\PhpSdk\Interfaces\PaymentStrategy;
use Aqayepardakht\PhpSdk\Invoice;

class PaymentStrategyFactory
{
    public static function make(string $type, ...$params): PaymentStrategy
    {
        switch ($type) {
            case 'create':
                [$pin, $invoice] = $params;
                return new CreatePaymentStrategy($pin, $invoice);
            case 'verify':
                [$pin, $traceCode, $amount] = $params;
                return new VerifyPaymentStrategy($pin, $traceCode, $amount);
            case 'start':
                [$trackingCode] = $params;
                return new StartPaymentStrategy($trackingCode);
            default:
                throw new \InvalidArgumentException("Invalid payment strategy type: $type");
        }
    }
}

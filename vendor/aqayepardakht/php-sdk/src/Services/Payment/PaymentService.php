<?php

namespace Aqayepardakht\PhpSdk\Services\Payment;

use Aqayepardakht\PhpSdk\Interfaces\PaymentStrategy;
use Aqayepardakht\PhpSdk\Services\Payment\PaymentStrategyFactory;
use Aqayepardakht\PhpSdk\Invoice;

class PaymentService {
    private string $pin;
    public Invoice $invoice;

    public function __construct(string $pin, Invoice $invoice) {
        $this->pin = $pin;
        $this->invoice = $invoice;
    }

    public function create(): self {
        $trackingCode = $this->process(
            PaymentStrategyFactory::make('create', $this->pin, $this->invoice)
        );

        $this->invoice->setTrackingCode($trackingCode);

        return $this;
    }

    public function start(?string $trackingCode = null): self {
        $trackingCode = $trackingCode ?? $this->invoice->getTrackingCode();

        $this->process(PaymentStrategyFactory::make('start', $trackingCode));

        return $this;
    }

    public function verify(string $traceCode): self {
        $this->process(
            PaymentStrategyFactory::make('verify', $this->pin, $traceCode, $this->invoice->amount)
        );

        return $this;
    }

    protected function process(PaymentStrategy $strategy) {
        try {
            return $strategy->process();
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to process payment: " . $e->getMessage());
        }
    }

    public function getInvoice(): Invoice {
        return $this->invoice;
    }
}

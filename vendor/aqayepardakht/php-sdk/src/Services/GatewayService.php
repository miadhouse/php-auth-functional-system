<?php 

namespace Aqayepardakht\PhpSdk\Services;

use Aqayepardakht\PhpSdk\Services\Payment\PaymentService;
use Aqayepardakht\PhpSdk\Services\Transaction\TransactionService;
use Aqayepardakht\PhpSdk\Invoice;
use InvalidArgumentException;

class GatewayService {
    private string $pin;

    public function __construct(string $pin) {
        $this->pin = $pin;
    } 

    /**
     * Creates a PaymentService instance with the provided invoice.
     *
     * @param Invoice|array $invoice
     * @return PaymentService
     * @throws InvalidArgumentException
     */
    public function invoice($invoice): PaymentService {
        if (!($invoice instanceof Invoice)) {
            if (!is_array($invoice)) {
                throw new InvalidArgumentException("Invoice must be an instance of Invoice or an array.");
            }
            $invoice = new Invoice($invoice);
        }

        return new PaymentService($this->pin, $invoice);
    }

    /**
     * Returns a new instance of TransactionService.
     *
     * @return TransactionService
     */
    public function transactions(): TransactionService {
        return new TransactionService();
    }

    public function getPin(): string {
        return $this->pin;
    }
}

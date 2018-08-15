<?php

namespace Bozboz\Ecommerce\Payment;

class Exception extends \Exception
{
    private $canRetry;

    public function __construct($message, $canRetry = false)
    {
        $this->canRetry = $canRetry;
        parent::__construct($message, 1);
    }

    public static function retry($message)
    {
        return new static('An error occurred while processing your payment, please try again. "' . $message . '"', true);
    }

    public static function fail($message = null)
    {
        return new static($message);
    }

    public function canRetry()
    {
        return $this->canRetry;
    }
}
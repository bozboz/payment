<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Payment\Exception;
use Bozboz\Ecommerce\Payment\CreditCardGateway;

use Psr\Log\LoggerInterface as Logger;

class LegacyOrderGateway implements PaymentGateway, Refundable
{
	protected $logger;

	/**
	 * @param  Psr\Log\LoggerInterface  $logger
	 */
	public function __construct(Logger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Unsupported purchase method
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @throws Bozboz\Ecommerce\Payment\Exception
	 */
	public function purchase($data, Order $order)
	{
		throw new Exception('Purchase not supported on LegacyOrderGateway');
	}

	/**
	 * Log notice of legacy order refund, and return dummy successful response
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 */
	public function refund(array $data, Order $order)
	{
		$this->logger->info("Legacy Order '{$order->id}' refunded");

		return new Test\TestGatewayResponse;
	}
}

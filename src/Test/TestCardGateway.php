<?php

namespace Bozboz\Ecommerce\Payment\Test;

use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Orders\Exception;
use Bozboz\Ecommerce\Payment\CreditCardGateway;
use Bozboz\Ecommerce\Payment\Refundable;

class TestCardGateway extends CreditCardGateway implements Refundable
{
	/**
	 * Spoof a purchase request and return a generic successful response
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @throws Bozboz\Ecommerce\Order\Exception
	 * @return Bozboz\Ecommerce\Order\Payment\Test\TestGatewayResponse
	 */
	public function purchase($data, Order $order)
	{
		$this->validate($data);

		return new TestGatewayResponse;
	}

	/**
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @throws Bozboz\Ecommerce\Order\Exception
	 * @return Bozboz\Ecommerce\Order\Payment\Test\TestGatewayResponse
	 */
	public function refund(array $data, Order $order)
	{
		return new TestGatewayResponse;
	}
}

<?php

namespace Bozboz\Ecommerce\Payment\Test;

use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Payment\ExternalGateway;
use Bozboz\Ecommerce\Payment\Refundable;

use Illuminate\Http\Request;

class TestIFrameGateway extends ExternalGateway implements Refundable
{
	/**
	 * @param  Illuminate\Http\Request  $request
	 */
	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function purchase($data, Order $order)
	{
		return new TestRedirectResponse;
	}

	public function completePurchase(Order $order)
	{
		return new TestGatewayResponse;
	}

	public function getIdentifier()
	{
		return $this->request->get('order_id');
	}

	public function refund(array $data, Order $order)
	{
		return new TestGatewayResponse;
	}
}

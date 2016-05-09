<?php

namespace Bozboz\Ecommerce\Payment;

use App;

class GatewayFactory
{
	/**
	 * Serve up a payment gateway based on string
	 *
	 * @param  string  $gateway
	 * @return [type]
	 */
	public function make($gateway)
	{
		return App::make($gateway);
	}
}

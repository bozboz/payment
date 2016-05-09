<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;

interface PaymentGateway
{
	public function purchase($data, Order $order);
}

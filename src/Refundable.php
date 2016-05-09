<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;

interface Refundable
{
	public function refund(array $data, Order $order);
}

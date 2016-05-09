<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;

abstract class ExternalGateway implements PaymentGateway
{
	/**
	 * Get the transaction reference for the current order
	 *
	 * @return string
	 */
	public abstract function getIdentifier();

	/**
	 * Complete the purchase
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 */
	public abstract function completePurchase(Order $order);
}

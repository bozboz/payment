<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Exception;
use Bozboz\Ecommerce\Orders\Order;
use Illuminate\Validation\Factory as Validator;

class DefferedPaymentGateway implements PaymentGateway
{
	public function __construct(Validator $validator)
	{
		$this->validator = $validator;
	}

	public function purchase($data, Order $order)
	{
		$validation = $this->validator->make($data, [
			'purchase_order' => 'required'
		]);

		if ($validation->fails()) throw new Exception($validation);
	}
}

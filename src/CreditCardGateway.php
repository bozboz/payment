<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Exception;
use Bozboz\Ecommerce\Orders\Order;
use Illuminate\Validation\Factory AS Validator;

abstract class CreditCardGateway implements PaymentGateway
{
	protected $validatorFactory;

	public function __construct(Validator $validatorFactory)
	{
		$this->validatorFactory = $validatorFactory;
	}

	/**
	 * Validate user input
	 *
	 * @param  array  $data
	 * @throws Bozboz\Ecommerce\Order\Exception
	 * @return void
	 */
	protected function validate(array $data)
	{
		$validation = $this->validatorFactory->make($data,
			[
				'card_type' => 'required',
				'card_number' => 'required',
				'from_month' => 'sometimes|max:12',
				'from_year' => 'sometimes|date_format:Y',
				'expiry_month' => 'required|max:12',
				'expiry_year' => 'required|date_format:Y',
				'cvv' => 'required|digits:3'
			],
			[
				'card_type.required' => 'Please select your type of credit card',
				'card_number.required' => 'Please provide your credit card number',
				'expiry_month.required' => 'Please provide the month in which your credit card will expire',
				'expiry_year.required' => 'Please provide the year in which your credit card will expire',
				'cvv.required' => 'Please provide your card\'s security code'
			]
		);

		if ($validation->fails()) {
			throw new Exception($validation);
		}
	}

	/**
	 * Generate an associate array of billing and delivery address parts
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return array
	 */
	private function getAddressDetails(Order $order)
	{
		$addressDetails = [
			'name' => 'name',
			'address1' => 'address_1',
			'address2' => 'address_2',
			'city' => 'city',
			'postCode' => 'postcode',
			'state' => 'state',
			'country' => 'country',
			'phone' => 'phone',
		];

		$details = array();

		foreach(['billing', 'shipping'] as $type) {
			foreach($addressDetails as $key => $value) {
				$rel = $order->{$type . 'Address'} ?: $order->billingAddress;
				$details[$type . ucfirst($key)] = $rel->$value;
			}
		}

		return $details;
	}
}

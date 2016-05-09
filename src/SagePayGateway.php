<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Orders\Exception;

use Omnipay\SagePay\DirectGateway;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\OmnipayException;

use Illuminate\Validation\Factory AS Validator;

class SagePayGateway extends CreditCardGateway implements Refundable
{
	protected $gateway;
	protected $validatorFactory;

	public function __construct(DirectGateway $gateway, Validator $validatorFactory)
	{
		$this->gateway = $gateway;
		$this->validatorFactory = $validatorFactory;
	}

	/**
	 * Submit a purchase request using the Sagepay DirectGateway
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @throws Bozboz\Ecommerce\Order\Exception
	 */
	public function purchase($data, Order $order)
	{
		$this->validate($data);

		$creditCardData = [
			'number' => $data['card_number'],
			'startMonth' => $data['from_month'],
			'startYear' => $data['from_year'],
			'expiryMonth' => $data['expiry_month'],
			'expiryYear' => $data['expiry_year'],
			'cvv' => $data['cvv'],
			'email' => $order->customer_email
		];

		$creditCardData += $this->getAddressDetails($order);

		$paymentData = [
			'amount' => number_format($order->totalPrice() / 100, 2),
			'currency' => 'GBP',
			'description' => 'iSmash order from ' . gethostname(),
			'card' => new CreditCard($creditCardData),
			'transactionId' => $order->id
		];

		$response = $this->gateway->purchase($paymentData)->send();

		$order->payment_ref = $response->getTransactionReference();
		$order->save();

		return $response;
	}

	/**
	 * Refund given $order
	 *
	 * @param  Order  $order [description]
	 */
	public function refund(array $data, Order $order)
	{
		$refundData = $data + [
			'amount' => number_format($order->totalPrice() / 100, 2),
			'currency' => 'GBP',
			'description' => 'Refunded order from ' . gethostname(),
			'transactionId' => $order->id,
			'transactionReference' => $order->payment_ref
		];

		$request = $this->gateway->refund($data);

		return $request->send();
	}
}

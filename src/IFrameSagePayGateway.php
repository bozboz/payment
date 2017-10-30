<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Orders\Exception;
use Bozboz\Ecommerce\Payment\ExternalGateway;

use Omnipay\SagePay\ServerGateway;
use Omnipay\SagePay\DirectGateway;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\OmnipayException;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Http\Request;

class IFrameSagePayGateway extends ExternalGateway implements Refundable
{
	protected $gateway;
	protected $url;
	protected $request;

	/**
	 * @param  Omnipay\SagePay\ServerGateway  $gateway
	 * @param  Illuminate\Routing\UrlGenerator  $url
	 * @param  Illuminate\Http\Request  $request
	 */
	public function __construct(ServerGateway $server, DirectGateway $direct, UrlGenerator $url, Request $request)
	{
		$this->gateway = (object)[
			'server' => $server,
			'direct' => $direct
		];
		$this->url = $url;
		$this->request = $request;
	}

	/**
	 * Submit a purchase request using the Sagepay Server Gateway
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @throws Bozboz\Ecommerce\Order\Exception
	 * @return Omnipay\Sagepay\Message\ServerAuthorizeResponse
	 */
	public function purchase($data, Order $order)
	{
		$order->generateTransactionId();
		$order->save();

		$paymentData = $data + [
			'profile' => 'LOW',
			'amount' => number_format($order->totalPrice() / 100, 2),
			'currency' => 'GBP',
			'card' => $this->getAddressDetails($order),
			'description' => 'Order from ' . gethostname(),
			'transactionId' => $order->getTransactionId()
		];

		$response = $this->gateway->server->purchase($paymentData)->send();

		$order->payment_ref = $response->getTransactionReference();
		$order->save();

		return $response;
	}

	/**
	 * Complete external Sagepay Server purchase
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return Omnipay\Sagepay\Message\ServerCompleteAuthorizeResponse
	 */
	public function completePurchase(Order $order)
	{
		$paymentData = [
			'transactionId' => $order->getTransactionId(),
			'transactionReference' => $order->payment_ref,
			'amount' => number_format($order->totalPrice() / 100, 2),
			'currency' => 'GBP'
		];

		$order->card_identifier = $this->request->get('Last4Digits');
		$order->save();

		$response = $this->gateway->server->completePurchase($paymentData)->send();

		$order->payment_ref = $response->getTransactionReference();
		$order->save();

		return $response;
	}

	/**
	 * Generate an associate array of billing and delivery address parts
	 *
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 * @return array
	 */
	private function getAddressDetails(Order $order)
	{
		$details = [
			'email' => $order->customer_email,
			'phone' => $order->customer_phone,
			'company' => $order->customer_company,
		];

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

		foreach(['billing', 'shipping'] as $type) {
			foreach($addressDetails as $key => $value) {
				$rel = $order->{$type . 'Address'} ?: $order->billingAddress;
				$details[$type . ucfirst($key)] = $rel->$value;
			}
		}

		return $details;
	}

	/**
	 * Get the transaction reference for the current order
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->request->get('VendorTxCode');
	}

	/**
	 * Refund given $order
	 *
	 * @param  array  $data
	 * @param  Bozboz\Ecommerce\Order\Order  $order
	 */
	public function refund(array $data, Order $order)
	{
		$order->generateTransactionId();

		$refundData = $data + [
			'amount' => number_format($order->totalPrice() * - 1 / 100, 2),
			'currency' => 'GBP',
			'description' => 'Refunded order from ' . gethostname(),
			'transactionId' => $order->getTransactionId(),
			'transactionReference' => $order->payment_ref
		];

		$response = $this->gateway->direct->refund($refundData)->send();

		$order->payment_ref = $response->getTransactionReference();
		$order->save();

		return $response;
	}
}

<?php

namespace Bozboz\Ecommerce\Payment;

use Omnipay\PayPal\ExpressGateway;
use Bozboz\Ecommerce\Orders\Order;
use Illuminate\Routing\UrlGenerator;

class PayPalGateway extends ExternalGateway
{
	private $gateway;
	private $url;

	public function __construct(ExpressGateway $gateway, UrlGenerator $url)
	{
		$this->gateway = $gateway;
		$this->url = $url;
	}

	public function purchase($data, Order $order)
	{
		$order->generateTransactionId();
		$order->save();

		$request = $this->gateway->purchase($this->orderDetails($order));
		$request->setItems($this->orderToArray($order));

		$response = $request->send();

		$order->payment_ref = $response->getTransactionReference();
		$order->save();

		return $response;;
	}

	public function completePurchase(Order $order)
	{
		$request = $this->gateway->completePurchase($this->orderDetails($order));
		$request->setItems($this->orderToArray($order));

		return $request->send();
	}

	private function orderDetails(Order $order)
	{
		return [
			'amount' => number_format($order->totalprice() / 100, 2),
			'returnUrl' => $this->url->route('checkout.callback.completed'),
			'cancelUrl' => $this->url->route('checkout.callback.cancel'),
			'currency' => 'gbp',
			'transactionId' => $order->getTransactionId(),
		];
	}

	private function orderToArray($order)
	{
		$orderItems = [];
		foreach ($order->items as $orderItem) {
			$orderItems[] = [
				'name' => $orderItem->name,
				'quantity' => $orderItem->quantity,
				'price' => number_format($orderItem->price_pence / 100, 2)
			];
		}

		return $orderItems;
	}

	public function getIdentifier()
	{
		// do nothing...
	}
}

<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;
use Illuminate\Routing\UrlGenerator;
use Omnipay\PayPal\ExpressGateway;

class PayPalGateway extends ExternalGateway
{
	protected $gateway;
	protected $url;

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

		return $response;
	}

	public function completePurchase(Order $order)
	{
		$request = $this->gateway->completePurchase($this->orderDetails($order));
		$request->setItems($this->orderToArray($order));

		return $request->send();
	}

	protected function orderDetails(Order $order)
	{
		return [
			'amount' => number_format($order->totalprice() / 100, 2),
			'returnUrl' => $this->url->route($this->returnRoute()),
			'cancelUrl' => $this->url->route($this->cancelRoute()),
			'currency' => 'gbp',
			'transactionId' => $order->getTransactionId(),
		];
	}

	protected function returnRoute()
	{
		return 'checkout.callback.completed';
	}

	protected function cancelRoute()
	{
		return 'checkout.callback.cancel';
	}

	protected function orderToArray($order)
	{
		$orderItems = [];
		foreach ($order->items as $orderItem) {
			$orderItems[] = [
				'name' => strip_tags($orderItem->name),
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

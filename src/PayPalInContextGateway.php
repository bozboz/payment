<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Orders\Order;
use Illuminate\Routing\UrlGenerator;
use Omnipay\PayPal\ExpressInContextGateway;

class PayPalInContextGateway extends PayPalGateway
{
    public function __construct(ExpressInContextGateway $gateway, UrlGenerator $url)
    {
        parent::__construct($gateway, $url);
    }

    public function authorize(Order $order)
    {
        $order->generateTransactionId();
        $order->save();

        $orderDetails = $this->orderDetails($order);
        $orderDetails['card']['billingCountry'] = $order->shipping_country;
        $request = $this->gateway->authorize($orderDetails);

        $request->setItems($this->orderToArray($order));

        $response = $request->send();

        $order->payment_ref = $response->getTransactionReference();
        $order->save();

        return $response;
    }

    public function fetchCheckout(Order $order, $token)
    {
        $orderDetails = $this->orderDetails($order);
        $orderDetails['token'] = $token;
        return $this->cachedCall('fetchCheckout', $orderDetails);
    }

    public function completeAuthorize(Order $order, $token, $payerId)
    {
        $orderDetails = $this->orderDetails($order);
        $orderDetails['token'] = $token;
        $orderDetails['payerid'] = $payerId;
        return $this->cachedCall('completeAuthorize', $orderDetails);
    }

    private function cachedCall($method, $data)
    {
        return \Cache::remember($method . implode(',', $data), 15, function() use ($method, $data) {
            return $this->gateway->{$method}($data)->send();
        });
    }

    public function processCheckoutData($data)
    {
        return json_decode(json_encode([
            'customer' => [
                'customer_email' => $data->EMAIL,
                'customer_first_name' => $data->FIRSTNAME,
                'customer_last_name' => $data->LASTNAME,
            ],
            'shipping' => [
                'name' => $data->SHIPTONAME,
                'address_1' => $data->SHIPTOSTREET,
                'city' => $data->SHIPTOCITY,
                'country' => $data->SHIPTOCOUNTRYCODE,
                'postcode' => $data->SHIPTOZIP,
            ],
        ]));
    }

    protected function returnRoute()
    {
        return 'paypal.confirm';
    }

    protected function cancelRoute()
    {
        return 'cart';
    }
}

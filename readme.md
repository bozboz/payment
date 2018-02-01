# Payment package

## Installation

1. See http://gitlab.lab/laravel-packages/ecommerce
2. Run `php artisan vendor:publish && php artisan migrate` 
3. Edit `config/payment.php` (see [Order States](#order-states))

## Usage

The payment package contains implementations for SagePay and PayPal. It should be pretty easy to add Stripe, it's been done on TicketRunway which is an older version ofthe Laravel CMS but it should be fairly easy to adapt.

http://gitlab.lab/bozboz/ticketrunway/blob/master/app/Bozboz/Ecommerce/Payment/StripeGateway.php

All the different payment gateways are registered in `PaymentServiceProvider::registerPaymentGateways`. This sets up the gateway with the relevant config ready for use. At the bottom of the method there are abstract gateways that are bound to implementations of actual gateways depending on whether `test_payments` is set in the config or not.

The payment gateways use a package called Omnipay to do all the actual communication with the payment provider (https://omnipay.thephpleague.com/). 

If using a gateway that requires making a callback from the payment provider to our server, e.g. ExternalGateway (i.e. TestIFrameGateway or IFrameSagePayGateway), the URLs 'checkout/callback' and 'checkout/billing' must be added to `$except` in `App\Http\Middleware\VerifyCsrfToken` and `$whitelist` in `App\Http\Middleware\FrontHoldingPage`.

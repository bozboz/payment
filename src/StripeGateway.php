<?php

namespace Bozboz\Ecommerce\Payment;

use Bozboz\Ecommerce\Customer\Customer;
use Bozboz\Ecommerce\Orders\Exception;
use Bozboz\Ecommerce\Orders\Order;
use Bozboz\Ecommerce\Payment\Exception as PaymentException;
use Illuminate\Validation\Factory AS Validator;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\OmnipayException;
use Omnipay\SagePay\DirectGateway;
use Omnipay\Stripe\Gateway;
use Stripe\Charge;
use Stripe\Customer as StripeCustomer;
use Stripe\Refund;

class StripeGateway extends CreditCardGateway implements Refundable
{
    protected $config;
    protected $validatorFactory;
    private $retrying;

    public function __construct(array $config, Validator $validatorFactory)
    {
        $this->config = $config;
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
        $token = $data['stripeToken'];

        try {
            $customer = $this->getOrCreateCustomer($order->user, $token);

            $charge = Charge::create([
                'customer' => $customer->id,
                'amount' => $order->totalPrice(),
                'currency' => 'gbp',
                'metadata' => [
                    'order_id' => $order->id,
                    'reference' => $order->reference,
                ],
            ], [
                'idempotency_key' => $order->reference
            ]);

            $order->payment_data = $charge;
            $order->payment_ref = $charge->id;
            $order->save();

        } catch(\Stripe\Error\Card $e) {
            if ( ! $this->retrying) {
                sleep(2);
                $this->retrying = true;
                return $this->purchase($data, $order);
            }
            throw PaymentException::retry($e->getMessage());

        } catch (\Stripe\Error\RateLimit $e) {
            throw PaymentException::retry($e->getMessage());

        } catch (\Stripe\Error\InvalidRequest $e) {
            throw PaymentException::retry($e->getMessage());

        } catch (\Stripe\Error\ApiConnection $e) {
            throw PaymentException::retry($e->getMessage());

        } catch (\Stripe\Error\Authentication $e) {
            throw PaymentException::fail($e->getMessage());

        } catch (\Stripe\Error\Base $e) {
            throw PaymentException::fail($e->getMessage());

        }

        return $charge;
    }

    /**
     * Refund given $order
     *
     * @param  Order  $order [description]
     */
    public function refund(array $data, Order $order)
    {
        try {

            Refund::create([
                'charge' => $data['transactionReference'],
            ]);

            return $this;

        } catch (\Stripe\Error\RateLimit $e) {

            if ( ! $this->retrying) {
                sleep(2);
                $this->retrying = true;
                return $this->refund($ref, $order);
            }
            throw $e;
        }
    }

    public function getOrCreateCustomer(Customer $customer, $token)
    {
        if ($customer->stripe_id) {
            return StripeCustomer::retrieve($customer->stripe_id);
        }

        $stripeCustomer = StripeCustomer::create([
            'email' => $customer->email,
            'source' => $token,
        ]);

        $customer->stripe_id = $stripeCustomer->id;
        $customer->save();

        return $stripeCustomer;
    }

    public function isSuccessful()
    {
        return true;
    }
}

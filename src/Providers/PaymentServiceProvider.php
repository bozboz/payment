<?php

namespace Bozboz\Ecommerce\Payment\Providers;

use Illuminate\Support\ServiceProvider;
use Omnipay\Omnipay;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerPaymentGateways();
    }

    public function boot()
    {
        $packageRoot = __DIR__ . "/../..";

        $this->publishes([
            $packageRoot . '/config.php' => config_path('payment.php'),
        ]);

        if (! $this->app->routesAreCached()) {
            require "$packageRoot/src/Http/routes.php";
        }
    }

    private function registerPaymentGateways()
    {
        $this->app->bind('Bozboz\Ecommerce\Payment\PayPalGateway', function($app)
        {
            $gateway = Omnipay::create('PayPal_Express');

            $key = $app['config']->get('payment.paypal.sandbox_mode_enabled') ? 'sandbox' : 'live';

            $gateway->setUsername($app['config']->get('payment.paypal.' . $key . '_username'));
            $gateway->setPassword($app['config']->get('payment.paypal.' . $key . '_password'));
            $gateway->setSignature($app['config']->get('payment.paypal.' . $key . '_signature'));
            $gateway->setBrandName($app['config']->get('payment.paypal.brand_name'));
            $gateway->setTestMode($app['config']->get('payment.paypal.sandbox_mode_enabled'));

            return new Payment\PayPalGateway($gateway, $app['url']);
        });

        $this->app->bind('Bozboz\Ecommerce\Payment\SagePayGateway', function($app)
        {
            $gateway = Omnipay::create('SagePay_Direct');

            $gateway->setTestMode($app['config']->get('payment.sagepay.testMode'));
            $gateway->setVendor($app['config']->get('payment.sagepay.vendor'));

            return new \Bozboz\Ecommerce\Payment\SagePayGateway($gateway, $app['validator']);
        });

        $this->app->bind('Bozboz\Ecommerce\Payment\IFrameSagePayGateway', function($app)
        {
            $gateways = [
                Omnipay::create('SagePay_Server'),
                Omnipay::create('SagePay_Direct')
            ];

            $testMode = $app['config']->get('payment.sagepay.testMode');
            $vendor = $app['config']->get('payment.sagepay.vendor');

            foreach($gateways as $gateway) {
                $gateway->setTestMode($testMode);
                $gateway->setVendor($vendor);
            }

            list($server, $direct) = $gateways;

            return new \Bozboz\Ecommerce\Payment\IFrameSagePayGateway($server, $direct, $app['url'], $app['request']);
        });

        $this->app->bind('Bozboz\Ecommerce\Payment\ExternalGateway', function($app)
        {
            if ($app['config']->get('payment.test_payments')) {
                return $app['Bozboz\Ecommerce\Payment\Test\TestIFrameGateway'];
            } else {
                return $app['Bozboz\Ecommerce\Payment\IFrameSagePayGateway'];
            }
        });

        $this->app->bind('Bozboz\Ecommerce\Payment\CreditCardGateway', function($app)
        {
            if ($app['config']->get('payment.test_payments')) {
                return $app['Bozboz\Ecommerce\Payment\Test\TestCardGateway'];
            } else {
                return $app['Bozboz\Ecommerce\Payment\SagePayGateway'];
            }
        });
    }
}
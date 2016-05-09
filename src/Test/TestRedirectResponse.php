<?php

namespace Bozboz\Ecommerce\Payment\Test;

use URL;
use Omnipay\Common\Message\AbstractResponse;

class TestRedirectResponse extends AbstractResponse
{
	public function __construct()
	{
	}

	public function isSuccessful()
	{
		return false;
	}

	public function isRedirect()
	{
		return true;
	}

	public function getTransactionReference()
	{
		return 'TEST';
	}

	public function getRedirectUrl()
	{
		return URL::route('checkout.sagepay-spoof');
	}
}

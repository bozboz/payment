<?php

namespace Bozboz\Ecommerce\Payment\Test;

use Omnipay\Common\Message\AbstractResponse;
use Response;

class TestGatewayResponse extends AbstractResponse
{
	public function __construct()
	{
	}

	public function isSuccessful()
	{
		return true;
	}

	public function confirm($url)
	{
		return Response::json([
			'Status' => 'OK',
			'RedirectUrl' => $url
		]);
	}

	public function invalid($invalidUrl, $reason)
	{
		return Response::json([
			'Status' => 'INVALID',
			'RedirectUrl' => $invalidUrl,
			'StatusDetail' => $reason
		]);
	}
}

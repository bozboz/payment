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
		echo json_encode([
			'Status' => 'OK',
			'RedirectUrl' => $url
		]);
		exit;
	}

	public function invalid($invalidUrl, $reason)
	{
		echo json_encode([
			'Status' => 'INVALID',
			'RedirectUrl' => $invalidUrl,
			'StatusDetail' => $reason
		]);
		exit;
	}
}

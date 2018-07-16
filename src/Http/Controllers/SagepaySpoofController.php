<?php

namespace Bozboz\Ecommerce\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Bozboz\Ecommerce\Orders\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;

class SagepaySpoofController extends Controller
{
    protected $client;
    protected $order;

    public function __construct(Client $client, Order $order)
    {
        $this->client = $client;
        $this->order = $order;
    }

    public function index()
    {
        $order = $this->order->find(Session::get('order'));

        return View::make('ecommerce::checkout.sagepay-spoof')->with([
            'order' => $order,
            'returnUrl' => Session::get('return_url')
        ]);
    }

    public function notify()
    {
        $returnUrl = Request::get('return_url') ?: URL::route('checkout.billing');

        $client = new Client(['verify' => false]);

        try {
            $res = json_decode($client->post($returnUrl, [
                'form_params' => Request::all()
            ])->getBody()->getContents());
        } catch (RequestException $e) {
            echo $e->getRequest()->getBody()->getContents();
            if ($e->hasResponse()) {
                echo $e->getResponse()->getBody()->getContents();
            }
        }
        return Redirect::to($res->RedirectUrl);
    }
}

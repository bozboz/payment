<?php

namespace Bozboz\Ecommerce\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use Bozboz\Ecommerce\Orders\Order;
use GuzzleHttp\Client;
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

        return View::make('ecommerce::checkout.sagepay-spoof')->withOrder($order);
    }

    public function notify()
    {
        $res = json_decode($this->client->post(URL::route('checkout.billing'), [
            'form_params' => Request::all()
        ])->getBody()->getContents());

        return Redirect::to($res->RedirectUrl);
    }
}

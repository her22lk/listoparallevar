<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\Preference;
use MercadoPago\Item;
use App\Models\Payment;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class MercadoPagoController extends Controller
{
    public function createPreference(Request $request)
    {
        \MercadoPago\SDK::setAccessToken(config('services.mercadopago.token'));

        $orderData = $request->json()->all();

        $preference = new Preference();

        $item = new Item();
        $item->title = $orderData['description'];
        $item->quantity = $orderData['quantity'];
        $item->unit_price = $orderData['price'];

        $preference->items = [$item];

        $preference->back_urls = [
            "success" => "https://s10-10-m-php-react.onrender.com",
            "failure" => "https://s10-10-m-php-react.onrender.com",
            "pending" => "https://s10-10-m-php-react.onrender.com"
        ];

        $preference->auto_return = "approved";

        $preference->save();
        
        $payment = Payment::create([
                'user_id' => Auth::user()->getAuthIdentifier(),
                'payment_preference_id' => $preference->id,
                'amount' => $orderData['price'],
            ]);

        return response()->json([
            'preference_id' => $preference->id,
            'payment_id' => $payment->id
        ]);
    }
}

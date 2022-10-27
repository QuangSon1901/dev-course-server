<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassesUsers;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Srmklive\PayPal\Facades\PayPal;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalPaymentController extends Controller
{
    public function create(Request $request)
    {
        $provider  = new PayPalClient();

        $provider = PayPal::setProvider();

        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $order = $provider->createOrder([
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->price
                    ],
                    'description' => 'Payment'
                ]
            ],
        ]);


        $user = User::find($request->user_id);
        $user->class_rooms()->attach($request->class_id, [
            'vendor_order_id' => $order['id'],
            'date' => Carbon::now(),
            'price' => $request->price,
            'status' => 0
        ]);

        return response()->json($order);
    }


    public function capture(Request $request)
    {
        $orderId = $request->vendor_order_id;
        $provider  = new PayPalClient();

        $provider = PayPal::setProvider();

        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $result = $provider->capturePaymentOrder($orderId);

        $result = $result->purchase_units[0]->payments->captures[0];
        try {
            if ($result['status'] === "COMPLETED") {
                // $transaction = new Transaction;
                // $transaction->vendor_payment_id = $orderId;
                // $transaction->payment_gateway_id  = $data['payment_gateway_id'];
                // $transaction->user_id   = $data['user_id'];
                // $transaction->status   = TransactionStatus::COMPLETED;
                // $transaction->save();

                $order = ClassesUsers::where('vendor_order_id', $orderId)->first();
                $order->status = 1;
                $order->save();
            }
        } catch (Exception $e) {
            dd($e);
        }
        return response()->json($result);
    }
}

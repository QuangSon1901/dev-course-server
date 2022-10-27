<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassesUsers;
use App\Models\ClassRoom;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Srmklive\PayPal\Facades\PayPal;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalPaymentController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => 'required|integer',
                'class_id' => 'required|integer',
                'price' => 'required|string',
            ],
            [],
            [
                'user_id' => 'Student',
                'class_id' => 'Classroom',
                'price' => 'Giá',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $checkUser = User::find($request->user_id);
        $checkClass = ClassRoom::find($request->class_id);
        if (!$checkUser || !$checkClass) return response(['status' => 403, 'success' => 'danger', 'message' => 'Error'], 403);

        $getOrder = $checkUser->class_rooms->where('id', $checkClass->id)->first();
        
        if ($getOrder && $getOrder->pivot->status == 0) $checkUser->class_rooms()->detach($checkClass->id);
        else if ($getOrder && $getOrder->pivot->status == 1) return response(['status' => 403, 'success' => 'danger', 'message' => 'You have already enroll for this course!'], 403);        

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

        if ($order['status'] && $order['status'] == 'CREATED') {
            $checkUser->class_rooms()->attach($checkClass->id, [
                'vendor_order_id' => $order['id'],
                'date' => Carbon::now(),
                'price' => $request->price,
                'status' => 0
            ]);
    
            return response()->json($order);
        }

        return response()->json($order);
    }


    public function capture(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'vendor_order_id' => 'required|string',
                'user_id' => 'required|integer',
                'class_id' => 'required|integer',
            ],
            [],
            [
                'vendor_order_id' => 'Payment ID',
                'user_id' => 'Student',
                'class_id' => 'Classroom',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $checkUser = User::find($request->user_id);
        $checkClass = ClassRoom::find($request->class_id);
        if (!$checkUser || !$checkClass) return response(['status' => 403, 'success' => 'danger', 'message' => 'Error'], 403);

        $getOrder = $checkUser->class_rooms->where('id', $checkClass->id)->first();
        if (!$getOrder || $getOrder->pivot->vendor_order_id != $request->vendor_order_id) return response(['status' => 403, 'success' => 'danger', 'message' => 'Payment session does not exist!'], 403);

        $provider  = new PayPalClient();

        $provider = PayPal::setProvider();

        $provider->setApiCredentials(config('paypal'));
        $token = $provider->getAccessToken();
        $provider->setAccessToken($token);
        $result = $provider->capturePaymentOrder($getOrder->pivot->vendor_order_id);

        $result = $result['purchase_units'][0]['payments']['captures'][0];
        try {
            if ($result['status'] === "COMPLETED") {
                $getOrder->pivot->status = 1;
                $getOrder->pivot->save();

                return response([
                    'status' => 200,
                    'success' => 'success',
                    'result' => $result
                ], 200);
            }
        } catch (Exception $e) {
            return response([
                'status' => 403,
                'success' => 'danger',
                'result' => $result
            ], 403);
        }
        
    }
}

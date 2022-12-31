<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassesUsers;
use App\Models\ClassRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class VNPayController extends Controller
{
    public function createVN(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'user_id' => 'required|integer',
                'class_id' => 'required|string',
            ],
            [],
            [
                'user_id' => 'Student',
                'class_id' => 'Classroom',
            ]
        );

        if ($validator->fails()) {
            return response(['status' => 403, 'success' => 'danger', 'message' => $validator->errors()], 403);
        }

        $checkUser = User::find($request->user_id);
        $checkClass = ClassRoom::where('class_id', $request->class_id)->first();
        if (!$checkUser || !$checkClass) return response(['status' => 403, 'success' => 'danger', 'message' => 'Error'], 403);

        $getOrder = $checkUser->class_rooms->where('id', $checkClass->id)->first();

        if ($getOrder && $getOrder->pivot->status == 0) $checkUser->class_rooms()->detach($checkClass->id);
        else if ($getOrder && $getOrder->pivot->status == 1) return response(['status' => 403, 'success' => 'danger', 'message' => 'You have already enroll for this course!'], 403);

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $vnp_TmnCode = "QAES6KLH"; //Website ID in VNPAY System
        $vnp_HashSecret = "YHKBBLMMRDNBFEPVBKEWPLAGTIUTYCAT"; //Secret key
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        $slug = $request->slug;
        $vnp_Returnurl = "https://api.tinhocstar.site/api/vnpay/order/capture";
        // $vnp_apiUrl = "http://sandbox.vnpayment.vn/merchant_webapi/merchant.html";
        //Config input format
        //Expire
        $startTime = date("YmdHis");
        $expire = date('YmdHis', strtotime('+15 minutes', strtotime($startTime)));

        $orderID = $this->quickRandom();

        $vnp_TxnRef = $orderID; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
        $vnp_OrderInfo = $request->order_desc;
        $vnp_Amount = $request->amount * 23672 * 100;
        $vnp_Locale = $request->language;
        $vnp_BankCode = $request->bank_code;
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
            $inputData['vnp_Bill_State'] = $vnp_Bill_State;
        }

        //var_dump($inputData);
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        $checkUser->class_rooms()->attach($checkClass->id, [
            'vendor_order_id' => $orderID,
            'date' => Carbon::now(),
            'price' => $request->amount,
            'name' => $request->name,
            'birth' => date('Y-m-d', strtotime($request->birth)),
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 0
        ]);

        $returnData = array(
            'code' => '00', 'message' => 'success', 'data' => $vnp_Url, 'order_id'
        );
        return response($returnData, 200);
    }

    public function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    public function capture(Request $request)
    {
        $getOrder = ClassesUsers::where('vendor_order_id', $request->vnp_TxnRef)->first();
        if (!$getOrder) return response(['status' => 403, 'success' => 'danger', 'message' => 'Payment session does not exist!'], 403);

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $vnp_HashSecret = "YHKBBLMMRDNBFEPVBKEWPLAGTIUTYCAT"; //Secret key

        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = array();
        $data = $request->all();
        foreach ($data as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                ClassesUsers::where('vendor_order_id', $request->vnp_TxnRef)->update([
                    'status' => 1
                ]);

                $slug = $getOrder->class_rooms->courses->slug;

                return Redirect::to("https://tinhocstar.site/course/$slug");
            } else {
                echo 'danger';

            }
        } else {
            echo 'danger1';

        }
    }
}

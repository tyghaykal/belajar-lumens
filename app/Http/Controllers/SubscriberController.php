<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Mail\EmailSubscriber;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;

class SubscriberController extends Controller
{
    public function submit(Request $request){
        DB::beginTransaction();
        try {
            $validate = Request::validate([
                "email" => 'required|email',
            ]);

            $is_subs = Subscriber::query()->where('email', Request::input('email'))->get();
            // dd($is_subs);
            if(count($is_subs) > 0){
                $validator["success"] = false;
                $validator["message"] = "This email already being a subscriber";
                return json_encode($validator);
            }

            $subs = new Subscriber;
            $subs->email = Request::input('email');
            $subs->save();

            Mail::to(Request::input('email'))->queue(new EmailSubscriber);
            DB::commit();

            $validator["success"] = true;

            return response()->json($validator, 201);
        } catch (\Exception $e) {
            DB::rollback();
            $validator["success"] = false;
            $validator["message"] = $e->getMessage();

            return response()->json($validator, 417);
        }
        
    }
}

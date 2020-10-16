<?php

namespace App\Http\Controllers;

use App\Models\ChatBot;
use App\Models\Service;
use App\Models\RunningText;
use App\Models\ChatQuestion;
use App\Models\ContactService;
use App\Models\ChatQuestionSet;
use App\Mail\EmailHello;
use App\Mail\EmailVisitor;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request;

class ContactController extends Controller
{
    public function index(){
        $head['title'] = 'Contact';
        $head['sections']['paralax'] = [];
        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'contact';

        $runningText = RunningText::query()->where('status',1)->get()->toArray();
        
        return response()->json([
            'head' => $head,    
            'runningText' => $runningText    
        ], 200);
        
    }

    public function chatLoad(Request $request){
        $getId = ($request::input('id') != null) ? $request::input('id'): 10;
        $data = ChatQuestionSet::query()->where('id_question_label', $getId)->first();
        if($data == null){
            return json_encode(["error"=>true,"message"=>"No Data"]);
        }

        $answer_type = [];
        $answer = [];
        // dd($data->ChatQuestion);
        if(count($data->ChatQuestion) > 0){
            foreach($data->ChatQuestion as $s){
                if($s->answer_type != null){
                    $obj = new \stdClass();
                    $obj->answer_type = $s->answer_type;
                    $obj->answer = $s->answer_type->answer;
                    array_push($answer_type, $obj);
                    
                }
            }
        }

        // dd($data);

        $datas = [
            "error" => false,
            "question_label" => $data->ChatQuestion[0]->question_label,
            "answer_type" => $answer_type,
            "next" => $data->id_to,
            "back" => $data->id_back,
            "is_submit" => $data->is_submit,
            "submit_route" => $data->submit_route,
            "can_be_null" => $data->can_null,
        ];
        return response()->json($datas, 200);
    }

    public function contact_service_submit(Request $request){
        DB::beginTransaction();
        try {
            $validate = Request::validate([
                "visitor_name" => 'required',
                "visitor_number" => 'required|numeric',
                "visitor_email" => 'required|email:rfc,dns',
                "contact_service" => 'required',
                "visitor_message" => 'required',
                "visitor_from" => 'required',
                ]);


            // die();
            $data = Request::All();

            if($data['visitor_from'] == 'other'){
                if($data['visitor_from_others'] == null){
                    DB::rollback();
                    $validator["success"] = false;
                    $validator["message"] = "The given data was invalid.";
                    return json_encode($validator);
                }
            }


            $services = Request::input('contact_service');
            if(is_array(Request::input('contact_service'))){
                $contactSelected = Service::query()->whereIn('id', Request::input('contact_service'))->get()->transform(function ($service) {
                            return $service->title;
                        })->toArray();
                $services = implode(', ', $contactSelected);
                        // dd($services);
            }
            $company = empty(Request::input('company_name')) ? 'secret' : Request::input('company_name');
            if($data['visitor_from'] == 'other'){
                $visitor_from = $data['visitor_from_others'];
            }else{
                $visitor_from = $data['visitor_from'];
            }

            $data['contact_service'] = $services;
            $data['company_name'] = $company;

            $CS = new ContactService;
            $CS->visitor_name = Request::input('visitor_name');
            $CS->nama_perusahaan = $company;
            $CS->visitor_phone = Request::input('visitor_number');
            $CS->visitor_email = Request::input('visitor_email');
            $CS->message = Request::input('visitor_message');
            $CS->services_interested = $services;
            $CS->visitor_from = $visitor_from;
            $CS->save();

            Mail::to("hello@redcomm.co.id")->queue(new EmailHello($data));
            Mail::to($data['visitor_email'])->queue(new EmailVisitor($data));
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

    public function form(){
        $head['title'] = 'Contact';
        $head['sections']['paralax'] = [];
        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'contact-form';

        $runningText = RunningText::query()->where('status',1)->get()->toArray();

        $services = Service::get()->toArray();
        

        return response()->json([
            'head' => $head,    
            'runningText' => $runningText,    
            'services' => $services
        ], 200);
    }
}

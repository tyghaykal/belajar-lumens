<?php

namespace App\Http\Controllers;

use App\Models\Career;
use App\Models\CaseStudy;
use App\Models\RunningText;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class JoinController extends Controller
{
    public function index(){
        $head['title'] = 'Join Us';
        $head['sections']['paralax'] = [];
        $head['sections']['url'] = [];

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'join-us';

        $runningText = RunningText::query()->where('status',1)->get()->toArray();

        $careers = Career::query()->get()->transform(function ($career) {
                    return [
                        'id' => $career->id,
                        'job_title' => $career->job_title,
                        'job_req' => explode("\n",$career->job_req),
                        'show' => false,
                    ];
                })->toArray();
        
        return response()->json([
            'head' => $head,
            'runningText' => $runningText,
            'careers' => $careers,
            
        ], 200);
        
    }
}

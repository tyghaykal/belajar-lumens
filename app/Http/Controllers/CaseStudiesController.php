<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\CaseStudy;
use App\Models\RunningText;
use Illuminate\Support\Arr;
use App\Http\Resources\Client;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class CaseStudiesController extends Controller
{
    public function index(){
        $page = Page::query()->where('page_name','case_index')->first();
        $head['title'] = $page->page_title;
        $head['description'] = $page->page_description;
        $head['sections']['paralax'] = [];
        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'case-studies';

        $caseStudies = CaseStudy::query()->where('status',1)->orderBy('id', 'desc')->limit(4)->get()->transform(function ($case) {
                    return [
                        'id' => $case->id,
                        'client' => new Client($case->client),
                        'campaign' => $case->campaign,
                        'title' => $case->title,
                        'description' => $case->description,
                        'banner' => $case->banner,
                        'featured_client' => $case->featured_client,
                        'slug' => $case->slug,
                        'video_link' => $case->video_link,
                        'video_type' => $case->video_type,
                    ];
                })->toArray();
        if(count($caseStudies) == 4){
            $nextCaseStudies = array_pop($caseStudies);
        }else{
            $nextCaseStudies = null;
        }
        // dd($caseStudies);
        $runningText = RunningText::query()->where('status',1)->get()->toArray();
        
        return response()->json([
            'head' => $head,
            'nextCaseStudies' => $nextCaseStudies,
            'CaseStudies' => $caseStudies,
            'runningText' => $runningText
        ], 200);
    }

    public function load(Request $request){
        $caseStudies = CaseStudy::query()->where('status',1)->orderBy('id', 'desc')->offset(Request::input('count'))->limit(4)->get()->transform(function ($case) {
                $data = new \stdClass();
                $data->data = null;
                $data->data = new Client($case->client);
                    return [
                        'id' => $case->id,
                        'client' => $data,
                        'campaign' => $case->campaign,
                        'title' => $case->title,
                        'description' => $case->description,
                        'banner' => $case->banner,
                        'featured_client' => $case->featured_client,
                        'slug' => $case->slug,
                        'video_link' => $case->video_link,
                        'video_type' => $case->video_type,
                    ];
                })->toArray();
        
        if(count($caseStudies) == 4){
            $nextCaseStudies = array_pop($caseStudies);
        }else{
            $nextCaseStudies = null;
        }
        $data = new \stdClass();
        $data->caseStudies = $caseStudies;
        $data->nextCaseStudies = $nextCaseStudies;

        return response()->json($data, 200);
    }

    public function detail($slug){
        $head['title'] = 'Case Studies';
        $head['sections']['paralax'] = [];

        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];
       
        foreach($head['sections']['paralax'] as $key => $value){
            $value = strtolower($value);
            $value = str_replace(' ','-',$value);
            $value = str_replace('&','and',$value);
            $head['slug_sections_paralax'][$key] = $value;
        }

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'case-studies-detail';

        $caseStudy = CaseStudy::query()->where('status',1)->where('slug', $slug)->first();
        if($caseStudy == null){
            return response()->json([
                "message" => "Case studies not found"
            ], 404);
        }
        $others = CaseStudy::query()->inRandomOrder()->where('id_client', '!=', $caseStudy->id_client)->groupBy('id_client')->limit(6)->get();
        foreach($others as $o){
            // dd();
            array_push($head['sections']['url'], $o->client->name);
            array_push($head['slug_sections_url'], $o->slug);
        }

        $nextCaseStudy = CaseStudy::query()->where('status',1)->where('id', '<' ,$caseStudy['id'])->max('id');
        if($nextCaseStudy != null){
            // dd($nextCaseStudy);
            $nextCaseStudy = CaseStudy::findOrFail($nextCaseStudy);
            $nextCaseStudy = [
                        'id' => $nextCaseStudy->id,
                        'client' => new Client($nextCaseStudy->client),
                        'banner' => $nextCaseStudy->banner,
                        'yt_video' => $nextCaseStudy->youtube_video,
                        'slug' => $nextCaseStudy->slug,
            ];
        }
        $runningText = RunningText::query()->where('status',1)->get()->toArray();
        // dd($nextCaseStudy);
        
        return response()->json([
            'head' => $head,
            'featured_case' => false,
            'caseStudy' => [
                        'id' => $caseStudy->id,
                        'client' => new Client($caseStudy->client),
                        'campaign' => $caseStudy->campaign,
                        'title' => $caseStudy->title,
                        'description' => $caseStudy->description,
                        'body' => $caseStudy->body,
                        'banner' => $caseStudy->banner,
                        'featured_client' => $caseStudy->featured_client,
                        'slug' => $caseStudy->slug,
                        'video_link' => $caseStudy->video_link,
                        'video_type' => $caseStudy->video_type,
            ],
            'nextCaseStudy' => $nextCaseStudy,
            'runningText' => $runningText
        ], 200);
    }

    public function detail_featured($slug){
        $head['title'] = 'Case Studies';
        $head['sections']['paralax'] = [];

        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];
       
        foreach($head['sections']['paralax'] as $key => $value){
            $value = strtolower($value);
            $value = str_replace(' ','-',$value);
            $value = str_replace('&','and',$value);
            $head['slug_sections_paralax'][$key] = $value;
        }

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'case-studies';

        $caseStudy = CaseStudy::query()->where('status',1)->where('slug', $slug)->where('featured_client',1)->first();
        if($caseStudy == null){
            return response()->json([
                "message" => "Case studies not found"
            ], 404);
        }
        $others = CaseStudy::query()->inRandomOrder()->where('id_client', '!=', $caseStudy->id_client)->where('featured_client', 1)->groupBy('id_client')->limit(3)->get();
        foreach($others as $o){
            // dd();
            array_push($head['sections']['url'], $o->client->name);
            array_push($head['slug_sections_url'], $o->slug);
        }
        $nextCaseStudy = CaseStudy::query()->where('status',1)->where('featured_client',1)->where('id', '<' ,$caseStudy['id'])->max('id');
        if($nextCaseStudy != null){
            // dd($nextCaseStudy);
            $nextCaseStudy = CaseStudy::findOrFail($nextCaseStudy);
            $nextCaseStudy = [
                        'id' => $nextCaseStudy->id,
                        'client' => new Client($nextCaseStudy->client),
                        'banner' => $nextCaseStudy->banner,
                        'slug' => $nextCaseStudy->slug,
            ];
        }

        $runningText = RunningText::query()->where('status',1)->get()->toArray();
        // dd($nextCaseStudy);
        
        return response()->json([
            'head' => $head,
            'featured_case' => true,
            'caseStudy' => [
                        'id' => $caseStudy->id,
                        'client' => new Client($caseStudy->client),
                        'campaign' => $caseStudy->campaign,
                        'title' => $caseStudy->title,
                        'description' => $caseStudy->description,
                        'body' => $caseStudy->body,
                        'banner' => $caseStudy->banner,
                        'featured_client' => $caseStudy->featured_client,
                        'slug' => $caseStudy->slug,
                        'video_link' => $caseStudy->video_link,
                        'video_type' => $caseStudy->video_type,
            ],
            'nextCaseStudy' => $nextCaseStudy,
            'runningText' => $runningText
        ], 200);
    }
    
}

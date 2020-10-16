<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Client;
use App\Models\CaseStudy;
use App\Models\RunningText;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use App\Http\Resources\Client as ClientResource;

class ClientController extends Controller
{
    public function index(){
        $page = Page::query()->where('page_name','client_exp')->first();
        $head['title'] = $page->page_title;
        $head['description'] = $page->page_description;
        $head['sections']['paralax'] = [
            'Featured Case Studies',
            'All Clients'
        ];
        $head['sections']['url'] = [];
        $head['slug_sections_slug'] = [];
       
        foreach($head['sections']['paralax'] as $key => $value){
            $value = strtolower($value);
            $value = str_replace(' ','-',$value);
            $head['slug_sections_paralax'][$key] = $value;
        }

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'client-experience';

        $featuredClient = CaseStudy::query()->where('featured_client', 1)->orderBy('id', 'desc')->limit(2)->get()->transform(function ($case) {
                    return [
                        'id' => $case->id,
                        'client' => new ClientResource($case->client),
                        'campaign' => $case->campaign,
                        'title' => $case->title,
                        'description' => $case->description,
                        'body' => $case->body,
                        'banner' => $case->banner,
                        'featured_client' => $case->featured_client,
                        'slug' => $case->slug,
                        'video_link' => $case->video_link,
                        'video_type' => $case->video_type,
                    ];
                })->toArray();
        $clientList = Client::query()->orderBy('order', 'asc')->get()->toArray();
        $cl_new = [];
        foreach($clientList as $cl){
            $logo = json_decode($cl['logo']);
            if($logo != null){
                $cl['logo'] = $logo[0]->download_link;
            }
            $logoGrey = json_decode($cl['logo_grey']);
            if($logoGrey != null){
                $cl['logo_grey'] = $logoGrey[0]->download_link;
            }

            array_push($cl_new, $cl);
        }
        if(count($cl_new) == 16){
            $nextClientList = array_pop($cl_new);
        }else{
            $nextClientList = null;
        }

        $runningText = RunningText::query()->where('status',1)->get()->toArray();

        
        return response()->json([
            'head' => $head,
            'featuredClient' => $featuredClient,
            'client' => $cl_new,
            'client_next' => $nextClientList,
            'runningText' => $runningText,
            'old_years' => date('Y', time()) - date('Y', strtotime("01-01-2000")),
        ], 200);
    }

    public function load(Request $request){
        $clientList = Client::query()->orderBy('id', 'asc')->offset(Request::input('count'))->limit(16)->orderBy('order')->get()->toArray();
        if(count($clientList) == 16){
            $nextClientList = array_pop($clientList);
        }else{
            $nextClientList = null;
        }
        $data = new \stdClass();
        $data->clientList = $clientList;
        $data->nextClientList = $nextClientList;
        return response()->json($data, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Client;
use App\Models\Partner;
use App\Models\Service;
use App\Models\CaseStudy;
use App\Models\Knowledge;
use App\Models\RunningText;
use Inertia\Inertia;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Resources\Client as ClientResource;

class HomeController extends Controller
{
    public function index(){
        $page = Page::query()->where('page_name','home')->first();
        $head['title'] = $page->page_title;
        $head['description'] = $page->page_description;
        $head['sections']['paralax'] = [
            'Who And Why',
            'Case Studies',
            'Client Experience',
            'Services',
            'Partners',
            'Knowledge',
        ];
        $head['sections']['url'] = [];

        foreach($head['sections']['paralax'] as $key => $value){
            $value = strtolower($value);
            $value = str_replace(' ','-',$value);
            $head['slug_sections_paralax'][$key] = $value;
        }

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'home';

        $runningText = RunningText::query()->where('status',1)->get()->toArray();

        $caseStudy = CaseStudy::query()->orderBy('id','desc')->first();
        $caseStudy = [
            'id' => $caseStudy->id,
            'client' => new ClientResource($caseStudy->client),
            'campaign' => $caseStudy->campaign,
            'title' => $caseStudy->title,
            'description' => $caseStudy->description,
            'body' => $caseStudy->body,
            'banner' => $caseStudy->banner,
            'featured_client' => $caseStudy->featured_client,
            'slug' => $caseStudy->slug,
        ];

        // dd($caseStudy);
        
        // dd($caseStudy);

        $client = Client::query()->inRandomOrder()->where('show_menu',1)->limit(12)->get()->toArray();
        $cl_new = [];
        foreach($client as $cl){
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

        $service = Service::get()->transform(function ($service) {
                            return [
                                'title' => $service->title,
                                'description' => $service->description,
                                'image' => $service->image,
                                'title_slug' => Str::slug($service->title),
                            ];
                        })->toArray();

        $partner = Partner::get();

        $knowledge = Knowledge::query()->where('status',1)->orderBy('id','desc')->limit(5)->get()->transform(function ($knowledges) {
                            return [
                                'title' => $knowledges->title,
                                'category' => $knowledges->category,
                                'slug' => $knowledges->slug,
                                'banner' => $knowledges->banner,
                                'description' => $knowledges->description,
                                'est_time_read' => $knowledges->reading_time,
                                'ratings' => $knowledges->rating,
                            ];
                        })->toArray();

        // return response()->json([],200);

        return response()->json([
            'head' => $head,
            'runningText' => $runningText,
            'caseStudy' => $caseStudy,
            'client' => $cl_new,
            'service' => $service,
            'partner' => $partner,
            'knowledge' => $knowledge,
            'old_years' => date('Y', time()) - date('Y', strtotime("01-01-2000")),
        ], 200); // http response code 200 mean everything is OK.
        
    }
}

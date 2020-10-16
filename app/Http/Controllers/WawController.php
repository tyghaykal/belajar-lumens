<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\WhoAndWhy;
use App\Models\WawGallery;
use App\Models\RunningText;
use App\Models\BusinessUnit;
use Illuminate\Support\Arr;
use App\Helpers\ImageResize;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class WawController extends Controller
{
    //Who And Why CONTROLLER
    public function index(){
        $page = Page::query()->where('page_name','waw')->first();
        $head['title'] = $page->page_title;
        $head['description'] = $page->page_description;
        $head['sections']['paralax'] = [
            'Our Story',
            'Our Formula',
            'Our Methodology',
            'Units',
            'Our Family',
        ];
       
        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];
       
        foreach($head['sections']['paralax'] as $key => $value){
            $value = strtolower($value);
            $value = str_replace(' ','-',$value);
            $head['slug_sections_paralax'][$key] = $value;
        }

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'who-and-why';
        $next = false;
        $profiles = WhoAndWhy::query()->orderBy('order', 'asc')->take(6)->get()->toArray();
        $nextProfile = false;
        if(count($profiles) == 6){
            array_pop($profiles);
            $nextProfile = true;
        }
        $gallery = WawGallery::query()->take(11)->orderBy('id','desc')->get()->toArray();
        $nextGallery = false;
        if(count($gallery) == 11){
            array_pop($gallery);
            $nextGallery = true;
        }

        $profiles_ = new \stdClass();
        $profiles_->profiles = $profiles;
        $profiles_->gallery = $gallery; 
        $profiles_->nextProfile = $nextProfile; 
        $profiles_->nextGallery = $nextGallery; 

        $runningText = RunningText::query()->where('status',1)->get()->toArray();
        
        return response()->json([
            'head' => $head,
            'waw' => $profiles_,
            'next' => $next,
            'business_units' => BusinessUnit::get()->toArray(),
            'runningText' => $runningText,
            'old_years' => date('Y', time()) - date('Y', strtotime("01-01-2000")),
        ], 200);
    }

    public function loadGallery(Request $request){
        
        $next = false;
        // $profiles = WhoAndWhy::query()->orderBy('order', 'asc')->offset(1)->take(7)->get()->toArray();
        $gallery = WawGallery::query()->offset(Request::input('count'))->take(11)->get()->toArray();
        $next = false;
        if(count($gallery) == 11){
            array_pop($gallery);
            $next = true;
        }
        $response = new \stdClass();
        $response->status = 200;
        $response->gallery = $gallery;
        $response->nextGallery = $next; 
        return response()->json($response, 200);
    }

    public function loadProfile(Request $request){
        $next = false;
        // $profiles = WhoAndWhy::query()->orderBy('order', 'asc')->offset(1)->take(7)->get()->toArray();
        $profiles = WhoAndWhy::query()->orderBy('order', 'asc')->offset(Request::input('count'))->take(6)->get()->toArray();
        $next = false;
        if(count($profiles) == 6){
            array_pop($profiles);
            $next = true;
        }
        $response = new \stdClass();
        $response->status = 200;
        $response->profiles = $profiles;
        $response->nextProfile = $next; 
        return response()->json($response, 200);
    }
}

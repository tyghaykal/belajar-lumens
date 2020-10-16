<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Partner;
use App\Models\RunningText;
use Illuminate\Support\Arr;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class PartnerController extends Controller
{
    public function index(){
        $page = Page::query()->where('page_name','partners')->first();
        $head['title'] = $page->page_title;
        $head['description'] = $page->page_description;

        $partners =  Partner::query()->orderBy('order', 'asc')->get();
        $new_partners = [];
        foreach($partners as $cl){
            $logo = json_decode($cl->logo);
            if($logo != null){
                $cl->logo = $logo[0]->download_link;
            }
            array_push($new_partners, $cl);
        }
        // dd($new_partners);


        $head['sections']['paralax'] = $partners->pluck('name');

        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];
       
        foreach($head['sections']['paralax'] as $key => $value){
            $value = strtolower($value);
            $value = str_replace(' ','-',$value);
            $head['slug_sections_paralax'][$key] = $value;
        }

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'partners';
        $runningText = RunningText::query()->where('status',1)->get()->toArray();
        
        foreach($partners as $partner){
            $slug = strtolower($partner->name);
            $slug = str_replace(' ','-',$slug);
            $partner->slug_name = $slug;
        }

        return response()->json([
            'head' => $head,
            'runningText' => $runningText,
            'partners' => $new_partners
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Service;
use App\Models\RunningText;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;

class ServiceController extends Controller
{
    //Who And Why CONTROLLER
    public function index(){

        $services = Service::query()->orderBy('order', 'asc')->get()->transform(function ($service) {
                            return [
                                'title' => $service->title,
                                'description' => preg_replace('/(<[^>]+) style=".*?"/i', '$1', $service->description),
                                'image' => $service->image,
                                'title_slug' => Str::slug($service->title),
                            ];
                        })->toArray();
        foreach($services as $i=>$s){
            $desc = explode("<p>", $s['description']);
            array_shift($desc);
            foreach($desc as $key=>$d){
                if($key != 0){
                    $desc[$key] = "<p class='text-20'>".$desc[$key];
                }else{
                    $desc[$key] = "<p>".$desc[$key];
                }
            }
            // dd($desc);
            $services[$i]['description'] = implode('',$desc);
        }
        // dd($services);
        // die();

        $page = Page::query()->where('page_name','services')->first();
        $head['title'] = $page->page_title;
        $head['description'] = $page->page_description;
        
        // $head['sections']['paralax'] = Arr::pluck($services, 'title');
        $head['sections']['paralax'] = [];
        $head['sections']['url'] = [];
        $head['slug_sections_url'] = [];
        // $head['slug_sections_paralax'] = Arr::pluck($services, 'title_slug');       
        $head['slug_sections_paralax'] = [];

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'services';

        $runningText = RunningText::query()->where('status',1)->get()->toArray();
        
        return response()->json([
            'head' => $head,
            'runningText' => $runningText,
            'services' => $services
        ], 200);
    }
}

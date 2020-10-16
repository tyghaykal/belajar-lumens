<?php

namespace App\Http\Controllers;

use Browser;
use App\Models\Page;
use App\Models\Knowledge;
use App\Models\RunningText;
use App\Models\KnowledgeRating;
use App\Models\KnowledgeCategory;
use Illuminate\Support\Arr;
use TCG\Voyager\Facades\Voyager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Session\Session;

class KnowledgeController extends Controller
{
    protected $session;
    public function __construct(){
        $this->session = new Session();
    }
    public function index(){
        $page = Page::query()->where('page_name','knowledge_index')->first();
        // dd($page);
        $head['title'] = $page->page_title;
        $head['description'] = $page->page_description;
        
        $head['sections']['paralax'] = [];
        $head['sections']['url'] = [];

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'knowledges';

        $highlight = Knowledge::query()->where('status',1)->where('is_highlight', 1)->orderBy('id','desc')->limit(5)->get()->transform(function ($highlight) {
                            return [
                                'title' => $highlight->title,
                                'banner' => $highlight->banner,
                                'slug' => $highlight->slug,
                                'description' => $highlight->description,
                                'rating' => $highlight->rating
                            ];
                        })->toArray();
        $knowledges = Knowledge::query()->where('status',1)->orderBy('id','desc')->limit(15)->get()->transform(function ($knowledges) {
                            return [
                                'title' => $knowledges->title,
                                'category' => $knowledges->category,
                                'slug' => $knowledges->slug,
                                'banner' => $knowledges->banner,
                                'description' => $knowledges->description,
                                'est_time_read' => $knowledges->reading_time,
                                'ratings' => $knowledges->rating
                            ];
                        })->toArray();
        // dd($knowledges);
        if(count($knowledges) == 15){
            $nextKnowledges = array_pop($knowledges);
        }else{
            $nextKnowledges = null;
        }

        $runningText = RunningText::query()->where('status',1)->get()->toArray();

        return response()->json([
            'head' => $head,
            'highlight' => $highlight,
            'category' => KnowledgeCategory::query()->get()->toArray(),
            'knowledges' => $knowledges,
            'nextKnowledges' => $nextKnowledges,
            'runningText' => $runningText,
        ], 200);
    }

    public function load(Request $request){
        $search = empty(Request::input('search')) ? '' : Request::input('search');
        $sort = empty(Request::input('sort')) ? 'latest' : Request::input('sort');
        $category = empty(Request::input('filterCategory')) ? [] : Request::input('filterCategory');
        $newCategory = [];
        foreach($category as $key=>$c){
            if($c == true){
                array_push($newCategory,$key);
            }
        }
        $knowledges = Knowledge::query();
        if(count($newCategory) > 0){
            $knowledges->whereIn('id_category', $newCategory);
        }
        $knowledges->where('status',1);
        $knowledges->where('title', 'like', "%".$search."%");
        if($sort == 'latest'){
            $knowledges->orderBy('id','desc');
        }else if($sort == 'shortest'){
            $knowledges->orderBy('reading_time','asc');
        }else if($sort == 'rating'){
            $knowledges->orderBy('rating','desc');
        }
        $knowledges = $knowledges->offset(Request::input('count'))->limit(15)->get()->transform(function ($knowledges) {
                            return [
                                'title' => $knowledges->title,
                                'category' => $knowledges->category,
                                'slug' => $knowledges->slug,
                                'banner' => $knowledges->banner,
                                'description' => $knowledges->description,
                                'est_time_read' => $knowledges->reading_time,
                                'ratings' => $knowledges->rating
                            ];
                        })->toArray();
        if(count($knowledges) == 15){
            $nextKnowledges = array_pop($knowledges);
        }else{
            $nextKnowledges = null;
        }

        $data = new \stdClass();
        $data->knowledges = $knowledges;
        $data->nextKnowledges = $nextKnowledges;

        return response()->json($data, 200);
    }

    public function detail($slug,Request $request, HttpRequest $req){
        $head['title'] = 'Knowledge';
        $head['sections']['paralax'] = [];
        $head['sections']['url'] = [];

        $head['current_url'] = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $head['selected_menu'] = 'knowledges-detail';
        $knowledges = Knowledge::query()->where('status',1)->where('slug',$slug)->first();
        if($knowledges == null){
            return redirect()->route('knowledge.detail');
        }
        $nextKnowledge = Knowledge::query()->where('status',1)->where('id', '<' ,$knowledges['id'])->max('id');
        if($nextKnowledge != null){
            $nextKnowledge = Knowledge::findOrFail($nextKnowledge);
            $nextKnowledge = [
                        'title' => implode(' ', array_slice(str_word_count($nextKnowledge->title, 2), 0, 4))." ...",
                        'banner' => $nextKnowledge->banner,
                        'slug' => $nextKnowledge->slug,
            ];
        }

        $runningText = RunningText::query()->where('status',1)->get()->toArray();

        $user_rating = 0;
        // $this->getUid($request,$req);
        if($this->session->get('knowledge_rating_uid') != null){
            $user_rating = KnowledgeRating::query()->where('id_knowledge', $knowledges->id)->where('ip', $this->session->get('knowledge_rating_uid'))->first();
            $user_rating = $user_rating['rating'];
            
        }
        
        return response()->json([
            'head' => $head,
            'knowledge' => [
                        'id' => $knowledges->id,
                        'title' => $knowledges->title,
                        'category' => $knowledges->category,
                        'slug' => $knowledges->slug,
                        'banner' => $knowledges->banner,
                        'description' => $knowledges->description,
                        'meta_title' => $knowledges->meta_title,
                        'meta_description' => $knowledges->meta_description,
                        'description' => $knowledges->description,
                        'body' => $knowledges->body,
                        'est_time_read' => $knowledges->reading_time,
                        'ratings' => $knowledges->rating,
                        'user_rating' => $user_rating,
                    ],
            'nextKnowledge' => $nextKnowledge,
            'runningText' => $runningText,
                ],200);
    }

    public function set_rating(Request $request, HttpRequest $req){
        $rating = Request::input('rating');
        $id = Request::input('knowledge');
        $this->getUid($request,$req);
        $resp = new \stdClass();
        $resp->status = false;
        // dd($this->session->get('knowledge_rating_uid'));
        if ($this->session->get('knowledge_rating_uid') != null) {
            $date = date('Y-m-d H:i:s');
            // dd($req->session()->get('knowledge_rating_uid'));
            $was_rate = KnowledgeRating::query()->where('id_knowledge', $id)->where('ip', $this->session->get('knowledge_rating_uid'))->get();
            
            if(count($was_rate) > 0){
                $resp->status = KnowledgeRating::find($was_rate[0]['id'])->update([
                    "rating" => $rating,
                ]);
            }else{
                $resp->status = KnowledgeRating::insert([
                    "id_knowledge" => $id,
                    "ip" => $this->session->get('knowledge_rating_uid'),
                    "rating" => $rating,
                    "created_at" => $date,
                    "updated_at" => $date,
                ]);
            }
            $knowledge = Knowledge::query()->where('status',1)->where('id',$id)->first();
            $knowledge->update([
                'rating' => $this->ratings($knowledge->ratings)
            ]);
            $resp->ratings = $knowledge->rating;
        }
        return response()->json($resp, 200);
    }

    public function getUid($request,$req){
        
        $ip = Request::ip();
        $device = Browser::browserName()."~".Browser::platformName();
        $uid = $ip.'-'.$device;
        // dd($this->session->get('knowledge_rating_uid'));
        if ($this->session->get('knowledge_rating_uid') != null) {
            $value = $req->session()->get('knowledge_rating_uid');
        }else{
            $this->session->set('knowledge_rating_uid', $uid);
            $value = $uid;
        }
    }


    protected function ratings($ratings){
        if(count($ratings) > 0){
            $ratings = (array_sum(Arr::pluck($ratings,'rating'))/count($ratings));
            $ratings = ceil($ratings);
            // dd($ratings);
            if($ratings < 3){
                $ratings = 3;
            }
            return $ratings;
        }else{
            return 0;
        }
    }

}

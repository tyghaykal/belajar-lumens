<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Client extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // dd($this->name);
        $logo = json_decode($this->logo);
        if($logo != null){
            $this->logo = $logo[0]->download_link;
        }
        $logoGrey = json_decode($this->logo_grey);
        if($logoGrey != null){
            $this->logo_grey = $logoGrey[0]->download_link;
        }
        
        return [
            'name' => $this->name,
            'logo' => $this->logo,
            'logo_grey' => $this->logo_grey,
        ];
    }
}

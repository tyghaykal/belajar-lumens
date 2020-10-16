<?php

namespace App\Rules;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class TitleSlugCheck implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $model, $title, $type, $id;
    public function __construct($model, $title, $type, $id)
    {
        $this->model = $model;
        $this->title = $title;
        $this->type = $type;
        $this->id = $id;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $slug = Str::slug($this->title);
        $count = $this->model->where('slug','like','%'.$slug.'%')->count();
        $id = $this->model->where('slug','like','%'.$slug.'%')->get()->toArray();

        if($this->type == 'create' && $count > 0){
            return false;
        }else if($this->type == 'edit' && $count >= 1){
            $id = Arr::pluck($id, 'id');
            foreach($id as $i){
                
                if($i == $this->id && $count == 1){
                    return true;
                }
            }
            return false;
        }else{
            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Sudah ada case studies dengan title sejenis, slug tidak dapat dibuat.';
    }
}

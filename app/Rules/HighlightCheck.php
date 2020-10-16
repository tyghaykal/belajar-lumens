<?php

namespace App\Rules;

use App\Knowledge;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Contracts\Validation\Rule;

class HighlightCheck implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $id, $type;
    public function __construct($id, $type)
    {
        $this->id = $id;
        $this->type = $type;
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
        if($this->type == 'edit'){
            $count = Knowledge::query()->where('is_highlight',1)->count();
            $knowledge = Knowledge::find($this->id);
            // dd($count);
            if($count >= 5 && $knowledge['is_highlight'] == 0 && $value == 1){
                return false;
            }
            return true;
        }else if($this->type == 'create'){
            $count = Knowledge::query()->where('is_highlight',1)->count();
            if($count >= 5 && $value == 1){
                return false;
            }
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
        return 'Sudah terdapat 5 knowledge yang di highlight, harap ubah status knowledge lain jika ingin menjadikan knowledge ini menjadi highlight';
    }
}

<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Mst_part;

class PartNoUnique implements InvokableRule
{
    protected $part_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($partId)
    {
        $this->part_id = $partId;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $partNo = substr($value,0,5).''.substr($value,6,strlen($value));
        if($this->part_id==0){
            $query = Mst_part::where('part_number','=',$partNo)->first();
        }else{
            $query = Mst_part::where('id','<>',$this->part_id)
                ->where('part_number','=',$partNo)
                ->first();
        }
        if($query){
            $fail('Part Number '.$value.' already exist.');
        }
    }
}

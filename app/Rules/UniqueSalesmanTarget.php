<?php

namespace App\Rules;

use App\Models\Mst_salesman_target;
use App\Models\Mst_supplier;
use Illuminate\Contracts\Validation\Rule;

class UniqueSalesmanTarget implements Rule
{
    protected $targetYear;
    protected $branch_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($targetYear,$branch_id)
    {
        $this->targetYear = $targetYear;
        $this->branch_id = $branch_id;
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
        $q = Mst_salesman_target::where([
            'year' => $this->targetYear,
            'branch_id' => $this->branch_id,
            'active' => 'Y',
        ])
        ->first();
        if($q){return false;}else{return true;}
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Sales targets for the same year and branch already exist.';
    }
}

<?php

namespace App\Rules;

use App\Models\Mst_supplier;
use Illuminate\Contracts\Validation\Rule;

class UniqueSupplierCode implements Rule
{
    protected $supplierSlug;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($supplierSlug)
    {
        $this->supplierSlug = $supplierSlug;
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
        $q = Mst_supplier::where('slug','<>',$this->supplierSlug)
        ->where('supplier_code','=',$value)
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
        return 'Supplier code has been taken by another supplier.';
    }
}

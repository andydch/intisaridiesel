<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\InvokableRule;

class UserEmailRegUnique implements InvokableRule
{
    protected $existingEmail;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($existingEmail)
    {
        $this->existingEmail = $existingEmail;
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
        $qCheck = User::where('email','<>',$this->existingEmail)
        ->where([
            'email' => $value,
        ])
        ->first();
        if($qCheck){
            $fail('The unique username has already been taken.');
        }
    }
}

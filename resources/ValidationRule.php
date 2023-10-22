<?php

namespace Resources;

use Respect\Validation\ {
    Rules\Alnum,
    Rules\Alpha,
    Rules\Between,
    Rules\BoolType,
    Rules\Email,
    Rules\In,
    Rules\IntVal,
    Rules\Length,
    Rules\NotEmpty,
    Rules\NoWhitespace,
    Rules\Number,
    Rules\Optional,
    Rules\Phone,
    Rules\Regex,
    Rules\Url,
    Validatable
};

class ValidationRule
{

    protected $rule;

    public function getRule(): Validatable
    {
        return $this->rule;
    }

    protected function __construct(Validatable $rule)
    {
        $this->rule = $rule;
    }

    public static function integerValue()
    {
        return new static(new IntVal());
    }

    public static function number()
    {
        return new static(new Number());
    }

    public static function alphabet()
    {
        return new static(new Alpha());
    }

    public static function alphanumeric(string $additionalChars = null)
    {
        return empty($additionalChars) ? new static(new Alnum()) : new static(new Alnum($additionalChars));
    }

    public static function standardFilename()
    {
        $regex = "/^[\w,\s-]+\.[a-zA-Z0-9]{2,4}$/";
        return new static(new Regex($regex));
    }

    public static function phone()
    {
        return new static(new Phone());
    }

    public static function email()
    {
        return new static(new Email());
    }

    public static function regex($regex)
    {
        return new static(new Regex($regex));
    }

    public static function in($haystack, $identical = false)
    {
        return new static(new In($haystack, $identical));
    }

    public static function boolType()
    {
        return new static(new BoolType());
    }

    public static function notEmpty()
    {
        return new static(new NotEmpty());
    }

    public static function between($min, $max, $inclusive = true)
    {
        return new static(new Between($min, $max, $inclusive));
    }

    public static function length(?int $min, ?int $max, bool $inclusive = true)
    {
        return new static(new Length($min, $max, $inclusive));
    }
    
    public static function noWhitespace(){
        return new static(new NoWhitespace());
    }
    
    public static function url(){
        return new static(new Url());
    }
    
    public static function optional(ValidationRule $rule)
    {
        return new static(new Optional($rule->getRule()));
    }
    

}

<?php
namespace Resources;

use Respect\Validation\Validator;
use Resources\Exception\RegularException;

class ValidationService
{
    /**
     * 
     * @var Validator
     */
    protected $validator;
    
    public function __construct() {
        $this->validator = new Validator();
    }
    public static function build(){
        return new static();
    }
    
    public function addRule(ValidationRule $validationRule): ValidationService {
        $this->validator->addRule($validationRule->getRule());
        return $this;
    }
    
    public function optional(ValidationRule $validationRule): ValidationService {
        $this->validator->optional($validationRule->getRule());
        return $this;
    }
    
    public function execute($input, string $errorDetail): void {
        if (!$this->validator->validate($input)) {
            throw RegularException::badRequest($errorDetail);
        };
    }
}


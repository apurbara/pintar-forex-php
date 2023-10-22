<?php
namespace Resources;

use Respect\Validation\ {
    Rules\Url,
    Validator
};
use Tests\TestBase;

class ValidationRuleTest extends TestBase
{
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    function test_integerValue_setRuleAsIntValRule() {
        $this->assertInstanceOf('Respect\Validation\Rules\Intval', ValidationRule::integerValue()->getRule());
    }
    function test_number_setRuleAsNumber() {
        $this->assertInstanceOf('Respect\Validation\Rules\Number', ValidationRule::number()->getRule());
    }
    function test_alphabet_setRuleAsAlphabet() {
        $this->assertInstanceOf('Respect\Validation\Rules\Alpha', ValidationRule::alphabet()->getRule());
    }
    function test_alphanumeric_setRuleAsAlphanumeric() {
        $this->assertInstanceOf('Respect\Validation\Rules\Alnum', ValidationRule::alphanumeric('$@^')->getRule());
    }
    function test_standardFilename_setRuleAsRegex() {
        $this->assertInstanceOf('Respect\Validation\Rules\Regex', ValidationRule::standardFilename()->getRule());
    }
    function test_standardFilename_addSupportForFilenameRegex() {
        $validationRule = ValidationRule::standardFilename();
        $this->assertTrue((new Validator())->addRule($validationRule->getRule())->validate('valid filename.jpg'));
        $this->assertFalse((new Validator())->addRule($validationRule->getRule())->validate('invalid filename'));
    }
    function test_phone_setRuleAsPhone() {
        $this->assertInstanceOf('Respect\Validation\Rules\Phone', ValidationRule::phone()->getRule());
    }
    function test_email_setRuleAsEmail() {
        $this->assertInstanceOf('Respect\Validation\Rules\Email', ValidationRule::email()->getRule());
    }
    function test_regex_setRuleAsRegex() {
        $regex = "/^[1-9][0-9]?$/";
        $this->assertInstanceOf('Respect\Validation\Rules\Regex', ValidationRule::regex($regex)->getRule());
    }
    function test_regex_addSupportForRegex() {
        $regex = "/^[1-9][0-9]?$/";//reget for number from 1 - 99
        $rule = ValidationRule::regex($regex)->getRule();
        $validator = new Validator();
        $validator->addRule($rule);
        
        $this->assertTrue($validator->validate(12));
        $this->assertFalse($validator->validate(100));
    }
    function test_in_setRuleAsIn() {
        $haystack = ['value 1'];
        $this->assertInstanceOf('Respect\Validation\Rules\In', ValidationRule::in($haystack)->getRule());
    }
    function test_boolType_setRuleAsBoolType() {
        $this->assertInstanceOf('Respect\Validation\Rules\BoolType', ValidationRule::boolType()->getRule());
    }
    function test_notEmpty_setRuleAsNotEmpty() {
        $this->assertInstanceOf('Respect\Validation\Rules\NotEmpty', ValidationRule::notEmpty()->getRule());
    }
    function test_between_setRuleAsBetween() {
        $this->assertInstanceOf('Respect\Validation\Rules\Between', ValidationRule::between(1, 99)->getRule());
    }
    
    public function test_length_setRuleAsLenght()
    {
        $this->assertInstanceOf('\Respect\Validation\Rules\Length', ValidationRule::length(1, 64)->getRule());
    }
    
    public function test_noWhitespace_setRuleAsNoWhitespace()
    {
        $this->assertInstanceOf('\Respect\Validation\Rules\NoWhitespace', ValidationRule::noWhitespace()->getRule());
    }
    
    public function test_url_setRuleAsUrl()
    {
        $this->assertInstanceOf(Url::class, ValidationRule::url()->getRule());
    }
    
    public function test_optional_setRuleAsOptional()
    {
        $this->assertInstanceOf('\Respect\Validation\Rules\Optional', ValidationRule::optional(ValidationRule::alphabet())->getRule());
    }
}

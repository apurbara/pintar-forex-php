<?php
namespace Resources;

use Tests\TestBase;
use Respect\Validation\Validator;

class ValidationServiceTest extends TestBase
{
    protected $service, $validator;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TestableValidationService();
        $this->validator = new MockedValidator();
        $this->service->validator = $this->validator;
    }
    
    function test_construct() {
        $service = new TestableValidationService();
        $this->assertInstanceOf('Resources\ValidationService', $service);
    }
    function test_construct_setValidator() {
        $service = new TestableValidationService();
        $this->assertInstanceOf('Respect\Validation\Validator', $service->validator);
    }
    function test_addRule_addRuleToValidator() {
        $rule = ValidationRule::integerValue();
        $this->service->addRule($rule);
        $this->assertTrue($this->validator->addRuleCalled);
        $this->assertEquals($rule->getRule(), $this->validator->ruleArg);
    }
    function test_optional_addOptionalRuleToValidator() {
        $rule = ValidationRule::integerValue();
        $this->service->optional($rule);
        $this->assertTrue($this->validator->optionalCalled);
        $this->assertEquals($rule->getRule(), $this->validator->ruleArg);
    }
    
    function test_execute_validateSuccess_void() {
        $this->service->execute($input = 123, 'error details');
        $this->assertEquals($this->validator->inputArg, $input);
    }
    function test_execute_validateFail_throwEx() {
        $this->validator->validateResult = false;
        $errorDetail = "error detail";
        $operation = function () use($errorDetail) {
            $this->service->execute(123, $errorDetail);
        };
        $this->assertRegularExceptionThrowed($operation, "Bad Request", $errorDetail);
    }

}

class TestableValidationService extends ValidationService{
    public $validator;
}
class MockedValidator{
    public $addRuleCalled = false;
    public $ruleArg = null;
    function addRule($rule) {
        $this->addRuleCalled = true;
        $this->ruleArg = $rule;
    }
    public $optionalCalled = false;
    function optional($rule) {
        $this->optionalCalled = true;
        $this->ruleArg = $rule;
    }
    public $validateResult = true;
    public $inputArg = null;
    function validate($input) {
        $this->inputArg = $input;
        return $this->validateResult;
    }
}


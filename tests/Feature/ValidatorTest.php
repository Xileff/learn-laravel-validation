<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidatorValid()
    {
        $data = [
            'username' => 'admin',
            'password' => '12345'
        ];

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertNotNull($validator);
        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());
    }

    public function testValidatorInvalid()
    {
        $data = [
            'username' => '',
            'password' => ''
        ];

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertNotNull($validator);
        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        $message->get('username');
        $message->get('password');
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorException()
    {
        $data = [
            'username' => '',
            'password' => ''
        ];

        $rules = [
            'username' => 'required',
            'password' => 'required'
        ];

        $validator = Validator::make($data, $rules);

        try {
            $validator->validate();
            $this->fail('ValidationException not thrown');
        } catch (ValidationException $e) {
            $this->assertNotNull($e->validator);
            $message = $e->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }
}

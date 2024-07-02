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

    public function testValidatorMultipleRules()
    {
        $data = [
            'username' => '',
            'password' => ''
        ];

        $rules = [
            'username' => 'required|email|max:100',
            'password' => ['required', 'min:6', 'max:20']
        ];

        $validator = Validator::make($data, $rules);
        $this->assertNotNull($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorValidatedData()
    {
        $data = [
            'username' => 'felix@gmail.com',
            'password' => 'felix123',
            'admin' => true,
        ];

        $rules = [
            'username' => ['required', 'email', 'max:100'],
            'password' => ['required', 'min:6', 'max:20']
        ];

        $validator = Validator::make($data, $rules);
        $this->assertNotNull($validator);

        try {
            $valid = $validator->validate();
            Log::info(json_encode($valid, JSON_PRETTY_PRINT));
        } catch (ValidationException $e) {
            $this->assertNotNull($e->validator);
            $message = $e->validator->errors();
            Log::error($message->toJson(JSON_PRETTY_PRINT));
        }
    }
}

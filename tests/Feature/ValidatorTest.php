<?php

namespace Tests\Feature;

use App\Rules\RegistrationRule;
use App\Rules\Uppercase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as ValidationValidator;
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
        App::setLocale('id');
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

    public function testValidatorInlineMessage()
    {
        $data = [
            'username' => '',
            'password' => ''
        ];

        $rules = [
            'username' => 'required|email|max:100',
            'password' => ['required', 'min:6', 'max:20']
        ];

        $messages = [
            'required' => ':attribute wajib diisi',
            'email' => ':attribute wajib berupa email',
            'min' => ':attribute minimal :min karakter',
            'max' => ':attribute maksimal :max karakter',
        ];

        $validator = Validator::make($data, $rules, $messages);
        $this->assertNotNull($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorAdditionalValidation()
    {
        $data = [
            'username' => 'felix@email.com',
            'password' => 'felix@email.com'
        ];

        $rules = [
            'username' => 'required|email|max:100',
            'password' => ['required', 'min:6', 'max:20']
        ];

        $validator = Validator::make($data, $rules);
        $validator->after(function (\Illuminate\Validation\Validator $vld) {
            $data = $vld->getData();
            if ($data['username'] === $data['password']) {
                $vld->errors()->add("password", "Password tidak boleh sama dengan username");
            }
        });
        $this->assertNotNull($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorCustomRule()
    {
        $data = [
            'username' => 'felix@email.com',
            'password' => 'felix@email.com'
        ];

        $rules = [
            'username' => ['required', 'email', 'max:100', new Uppercase()],
            'password' => ['required', 'min:6', 'max:20', new RegistrationRule()]
        ];

        $validator = Validator::make($data, $rules);

        $this->assertNotNull($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorCustomFunctionRule()
    {
        $data = [
            'username' => 'felix@email.com',
            'password' => 'felix@email.com'
        ];

        $rules = [
            'username' => ['required', 'email', 'max:100', function ($attr, $value, \Closure $fail) {
                if (strtoupper($value) !== $value) {
                    $fail("The field $attr must be UPPERCASE.");
                }
            }],
            'password' => ['required', 'min:6', 'max:20', new RegistrationRule()]
        ];

        $validator = Validator::make($data, $rules);

        $this->assertNotNull($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorRuleClasses()
    {
        $data = [
            'username' => 'felix@email.com',
            'password' => 'felix@email.com'
        ];

        $rules = [
            'username' => ['required', new In('Felix', 'Xilef', 'GitHub')],
            'password' => ['required', Password::min(6)->letters()->numbers()->symbols()]
        ];

        $validator = Validator::make($data, $rules);

        $this->assertNotNull($validator);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
    public function testValidatorNestedArray()
    {
        $data = [
            'name' => [
                'first' => 'Felix',
                'last' => 'Xilef'
            ],
            'address' => [
                'street' => 'Jalan Belum Jadi',
                'city' => 'Jakarta',
                'country' => 'Indonesia'
            ]
        ];

        $rules = [
            'name.first' => ['required', 'max:100'],
            'name.last' => ['max:100'],
            'address.street' => ['max:200'],
            'address.city' => ['required', 'max:100'],
            'address.country' => ['required', 'max:100'],
        ];

        $validator = Validator::make($data, $rules);

        $this->assertNotNull($validator);

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }

    public function testValidatorNestedIndexedArray()
    {
        $data = [
            'name' => [
                'first' => 'Felix',
                'last' => 'Xilef'
            ],
            'address' => [
                [
                    'street' => 'Jalan Belum Jadi',
                    'city' => 'Jakarta',
                    'country' => 'Indonesia'
                ],
                [
                    'street' => 'Jalan Belum Jadi',
                    'city' => 'Jakarta',
                    'country' => 'Indonesia'
                ]
            ]
        ];

        $rules = [
            'name.first' => ['required', 'max:100'],
            'name.last' => ['max:100'],
            'address.*.street' => ['max:200'],
            'address.*.city' => ['required', 'max:100'],
            'address.*.country' => ['required', 'max:100'],
        ];

        $validator = Validator::make($data, $rules);

        $this->assertNotNull($validator);

        $this->assertTrue($validator->passes());
        $this->assertFalse($validator->fails());

        $message = $validator->getMessageBag();
        Log::info($message->toJson(JSON_PRETTY_PRINT));
    }
}

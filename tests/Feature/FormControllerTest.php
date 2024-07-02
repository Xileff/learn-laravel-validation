<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FormControllerTest extends TestCase
{
    public function testLoginFailed()
    {
        $response = $this->post('/form/login', [
            'username' => '',
            'password' => ''
        ]);

        $response->assertStatus(400);
    }

    public function testLoginSuccess()
    {
        $response = $this->post('/form/login', [
            'username' => 'felix',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }

    public function testFormFailed()
    {
        $response = $this->post('/form', [
            'username' => '',
            'password' => ''
        ]);

        $response->assertStatus(302);
    }

    public function testFormSuccess()
    {
        $response = $this->post('/form', [
            'username' => 'felix',
            'password' => 'felix'
        ]);

        $response->assertStatus(200);
    }
}

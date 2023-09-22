<?php

namespace Tests\Feature\Http\Controllers\Api\V1\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * send empty data to register route for check can be validate
     * @return void
     */
    public function test_register_can_be_validate(): void
    {
        $response = $this->postJson(route('register'),[]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_can_be_register(){
        $response = $this->postJson(route('register'),[
            'name'=>'rasool',
            'password'=>'password',
            'confirm_password'=>'password',
            'phone'=>'9357594939'
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertCount(1,User::where('phone','9357594939')->get());
    }
}

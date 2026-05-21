<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsWithJWT($user)
    {
        $token = JWTAuth::fromUser($user);

        return $this->actingAs($user)->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ]);
    }
}

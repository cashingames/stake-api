<?php

namespace Tests\Unit;

use App\Actions\GetActiveUsersDeviceTokensAction;
use App\Repositories\FcmRespository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GetActiveUsersDeviceTokenTest extends TestCase
{
    use RefreshDatabase, WithFaker;
  
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    public function test_that_only_active_device_tokens_are_gotten()
    {
        $fcmRespository = $this->createMock(FcmRespository::class);
        $fcmRespository->expects($this->once())
        ->method('getActiveUsersDeviceTokens')
        ->willReturn(["device_token" => "ggegegetg"]);

        $getActiveUsersDeviceTokensAction = new GetActiveUsersDeviceTokensAction($fcmRespository);
       $response = $getActiveUsersDeviceTokensAction->execute(now());
       $this->assertEquals($response["device_token"],  "ggegegetg");
    }
}

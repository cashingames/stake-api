<?php

namespace Tests\Feature;

use App\Services\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FeatureFlagTest extends TestCase
{
    /**
     * @expectedException App\Exceptions\UnknownFeatureException
     */
    public function test_throws_exception_when_accessing_unknown_flag(){

        $this->withoutExceptionHandling();
        $this->expectException(\App\Exceptions\UnknownFeatureException::class);
        FeatureFlag::isEnabled('in_app_chat');
        
    }

    public function test_can_enable_feature(){
        config(['features.notification_history.enabled' => false]);
        FeatureFlag::enable('notification_history');
        $this->assertTrue(config('features.notification_history.enabled'));
    }

    public function test_can_disable_feature(){
        config(['features.notification_history.enabled' => true]);
        FeatureFlag::disable('notification_history');
        $this->assertTrue(config(['features.notification_history.enabled']) == false);
    }

    public function test_can_fetch_feature_flags(){
        $response = $this->getJson("/api/v3/feature-flags")->assertOk();
    }
}

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
        config(['features.email_verification.enabled' => false]);
        FeatureFlag::enable('email_verification');
        $this->assertTrue(config('features.email_verification.enabled'));
    }

    public function test_can_disable_feature(){
        config(['features.email_verification.enabled' => true]);
        FeatureFlag::disable('email_verification');
        $this->assertTrue(config(['features.email_verification.enabled']) == false);
    }

    public function test_can_check_if_at_least_one_of_many_flags_enabled(){
        
        $flags = array_keys(FeatureFlag::$features);
        
        foreach ($flags as $key => $value) {
            FeatureFlag::disable($value);
        }

        FeatureFlag::enable($flags[0]);

        $this->assertTrue(FeatureFlag::isAnyEnabled($flags));
    }

    public function test_check_for_at_least_one_enabled_flag_returns_false_when_all_are_disabled(){
        $flags = array_keys(FeatureFlag::$features);

        foreach ($flags as $key => $value) {
            FeatureFlag::disable($value);
        }

        $this->assertFalse(FeatureFlag::isAnyEnabled($flags));
    }

    public function test_that_all_given_flags_must_be_enabled(){
        $flags = array_keys(FeatureFlag::$features);

        foreach ($flags as $key => $value) {
            FeatureFlag::enable($value);
        }

        $this->assertTrue(FeatureFlag::isAllEnabled($flags));

        FeatureFlag::disable($flags[0]);

        $this->assertFalse(FeatureFlag::isAllEnabled($flags));

    }

    public function test_can_fetch_feature_flags(){
        $response = $this->getJson("/api/v3/feature-flags")->assertOk();
    }
}

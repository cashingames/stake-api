<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Traits\Utils\ResolveGoogleCredentials;

class ResolveGoogleCredentialsTest extends TestCase
{
    use ResolveGoogleCredentials;

    public function test_that_google_credential_development_name_can_be_detected(){

        $credential = $this->detectGoogleCredentialName("development");

        $this->assertEquals($credential, 'google-credentials-dev.json');

    }

    public function test_that_google_credential_stake_development_name_can_be_detected(){

        $credential = $this->detectGoogleCredentialName("stake-development");

        $this->assertEquals($credential, 'google-credentials-stake-dev.json');

    }

    public function test_that_google_credential_stake_production_name_can_be_detected(){

        $credential = $this->detectGoogleCredentialName("stake-production");

        $this->assertEquals($credential, 'google-credentials-stake-prod.json');

    }

    public function test_that_google_credential_stake_testing_name_can_be_detected(){

        $credential = $this->detectGoogleCredentialName("stake-testing");

        $this->assertEquals($credential, 'google-credentials-stake-test.json');

    }

    public function test_that_google_credential_development_environment_can_assigned(){

        $this->detectGoogleCredentialEnvironment("development");

        $this->assertEquals(env('GOOGLE_CREDENTIALS_ENV'), 'development');

    }

    public function test_that_google_credential_stake_development_environment_can_assigned(){

        $this->detectGoogleCredentialEnvironment("stake-development");

        $this->assertEquals(env('GOOGLE_CREDENTIALS_ENV'), 'stake-development');

    }

    public function test_that_google_credential_stake_production_environment_can_assigned(){

        $this->detectGoogleCredentialEnvironment("stake-production");

        $this->assertEquals(env('GOOGLE_CREDENTIALS_ENV'), 'stake-production');

    }

    public function test_that_google_credential_stake_testing_environment_can_assigned(){

        $this->detectGoogleCredentialEnvironment("stake-testing");

        $this->assertEquals(env('GOOGLE_CREDENTIALS_ENV'), 'stake-testing');

    }

}

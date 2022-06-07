<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\Utils\DateUtils;
use Carbon\Carbon;

class DateUtilsTest extends TestCase
{   
    public $trait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trait = $this->getObjectForTrait(DateUtils::class);

    }

    public function test_that_date_can_be_converted_to_timezone() {
      
        $test = $this->trait->toTimeZone(Carbon::now(),'Africa/Lagos', 'Asia/Baghdad');

        $this->assertEquals(Carbon::parse($test)->timezoneName, 'Asia/Baghdad');
    }

    public function test_that_date_can_be_converted_to_UTC() {
      
        $test = $this->trait->toUtc(Carbon::now(),'Africa/Lagos');

        $this->assertEquals(Carbon::parse($test)->timezoneName, 'UTC');
    }

    public function test_that_date_can_be_converted_to_Nigerian_Timezone() {
       
        $test = $this->trait->toNigerianTimeZone(Carbon::now(),'America/Vancouver');

        $this->assertEquals(Carbon::parse($test)->timezoneName, 'Africa/Lagos');
    }

    public function test_that_date_can_be_converted_to_UTC_from_Nigerian_timezone() {
     
        $test = $this->trait->toUtcFromNigeriaTimeZone(Carbon::now('Africa/Lagos'));

        $this->assertEquals(Carbon::parse($test)->timezoneName, 'UTC');
    }

    public function test_that_date_can_be_converted_to_Nigerian_timezone_from_UTC() {
       
        $test = $this->trait->toNigeriaTimeZoneFromUtc(Carbon::now('UTC'));

        $this->assertEquals(Carbon::parse($test)->timezoneName, 'Africa/Lagos');
    }

    public function test_that_date_can_be_converted_to_timestamp() {
       
        $test = $this->trait->toTimestamp(Carbon::now());

        $this->assertEquals($test/1000, Carbon::now()->timestamp);
    }

}

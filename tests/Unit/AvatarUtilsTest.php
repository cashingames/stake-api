<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Traits\Utils\AvatarUtils;

class AvatarUtilsTest extends TestCase
{
    use AvatarUtils;
    public function test_that_avatar_url_returns_url()
    {
        config(['app.url' => 'https://cashingames.com']);
        
        $url = $this->getAvatarUrl("avatar/image");

        $this->assertEquals($url, "https://cashingames.com/avatar/image" );
    }

    public function test_that_avatar_url_returns_null_when_avatar_is_null()
    {
      
        $url = $this->getAvatarUrl(null);

        $this->assertNull($url);
    }
}

<?php

namespace App\Http\Controllers;

use App\Traits\Utils\DateUtils;
use Illuminate\Http\Request;

class PlayGroundController extends BaseController
{
    use DateUtils;
    /**
     * Single action playground
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $value = '2022-06-05 17:34:30';
        return $this->toUtcFromNigeriaTimeZone($value);
    }
}

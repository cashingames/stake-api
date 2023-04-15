<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeatureFlagController extends BaseController
{
    public function index(Request $request)
    {
        $features = config('features');
        return $this->sendResponse($features, "Features fetched successfully");
    }
}

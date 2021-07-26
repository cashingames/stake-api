<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Feedback;

class EnquiriesController extends BaseController
{
    //

    public function feedback(Request $request){
        $data = $request->validate([
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'email' => ['required','email', 'string'],
            'message' => ['required', 'string']
        ]);
    }
}

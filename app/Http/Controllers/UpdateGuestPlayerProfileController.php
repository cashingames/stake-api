<?php

namespace App\Http\Controllers;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateGuestPlayerProfileController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'username' => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required', 'string', 'max:255', 'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['required', 'string', 'min:8'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]); 
        $user->username = $data['username'];
        $user->email = $data['email'];
        $user->user_type = UserType::PERMANENT_PLAYER;
        $user->save();

        if ($data['password'] === $data['new_password']) {
            return $this->sendError("The new password must be different from the old password.", "The new password must be different from the old password.");
        }

        if (Hash::check($data['password'], $user->password)) {
            $user->update(['password' => bcrypt($data['new_password'])]);
            return $this->sendResponse("Password Changed!.", "Password Changed!.");
        }

    }
}

<?php
// app/Http/Responses/LoginResponse.php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        $redirectTo = match($user->role) {
            'admin' => route('admin.dashboard'),
            'hr' => route('hr.dashboard'),
            'employee' => route('employee.dashboard'),
            default => route('login'),
        };

        return redirect()->intended($redirectTo);
    }
}

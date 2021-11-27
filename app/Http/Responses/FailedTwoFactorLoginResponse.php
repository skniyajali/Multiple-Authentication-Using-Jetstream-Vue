<?php

namespace App\Http\Responses;

use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;

class FailedTwoFactorLoginResponse implements FailedTwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $message = __('The provided two factor authentication code was invalid.');

        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'code' => [$message],
            ]);
        }
        if(request()->is('admin/*')){
            return redirect()->route('admin.login')->withErrors(['email' => $message]);
        }else{
            return redirect()->route('login')->withErrors(['email' => $message]);
        }

    }
}

<?php

namespace App\Http\Controllers;

use App\AccountOauth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;
use Google\Auth\OAuth2;
use InvalidArgumentException;
use Str;

class GoogleAdAudienceController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToProvider()
    {
        $oauth2 = new OAuth2([
            'authorizationUri' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'tokenCredentialUri' => 'https://www.googleapis.com/oauth2/v4/token',
            'redirectUri' => config('services.google.redirect'),
            'clientId' => config('services.google.client_id'),
            'clientSecret' => config('services.google.client_secret'),
            'scope' => 'https://www.googleapis.com/auth/adwords'
        ]);

        // Create a 'state' token to prevent request forgery.
        // Store it in the session for later validation.
        $oauth2->setState(Str::random(40));
        session()->put('oauth2state', $oauth2->getState());

        // Redirect the user to the authorization URL.
        $config = [
            'access_type' => 'offline',
            "prompt" => "consent select_account"
        ];

        // put in session for later use in redirect controller method
        session()->put('oauth2', $oauth2);

        return redirect($oauth2->buildFullAuthorizationUri($config));
    }

    /**
     * Obtain the user information from Google.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Throwable
     */
    public function handleProviderCallback(Request $request)
    {
        // throw error if request oauth2 state mismatch with session's state
        $state = session()->pull('oauth2state');
        throw_unless(
            !is_null($state) && $state !== '' && $state === $request->state,
            InvalidArgumentException::class
        );

        $oauth2 = session()->pull('oauth2');
        $oauth2->setCode($request->query('code'));
        $authToken = $oauth2->fetchAuthToken();

        $accessToken = $authToken['access_token'];
        $refreshToken = $authToken['refresh_token']; // important
        $expiresIn = $authToken['expires_in'];

        AccountOauth::updateOrCreate(
            [
                'account_id' => Auth::user()->currentAccountId,
                'social_media_provider_id' => 1 // 1 => google, refer to social_media_providers table
            ],
            [
                'token' => $accessToken,
                'refresh_token' => $refreshToken,  // not always provided
                'expires_in' => $expiresIn, // to be removed maybe
            ]
        );

        return redirect('/integration/setting');
    }
}

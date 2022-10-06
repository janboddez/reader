<?php

namespace App\Http\Controllers;

use App\IndieAuthClient;
use App\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        IndieAuthClient::$clientID = config('app.url');
        IndieAuthClient::$redirectURL = route('login_callback');
    }

    public function login()
    {
        return view('login', ['title' => config('app.name')]);
    }

    public function logout()
    {
        session()->flush();

        return redirect('/');
    }

    public function start(Request $request)
    {
        if (! $request->has('url')) {
            session(['auth_error' => 'invalid url']);
            session(['auth_error_description' => 'The URL you entered was not valid']);

            return redirect()->route('login');
        }

        $url = $request->input('url');

        $scope = 'create update read follow channels';
        list($authorizationURL, $error) = IndieAuthClient::begin($url, $scope);

        // If the scheme was added automatically, and if we got an ssl error, try again with http
        if ($request->has('auto-scheme')) {
            if ($error && $error['error'] === 'ssl_cert_error') {
                $url = str_replace('https://', 'http://', $url);
                list($authorizationURL, $error) = IndieAuthClient::begin($url, $scope);
            }
        }

        if ($error) {
            session(['auth_error' => $error['error']]);
            session(['auth_error_description' => $error['error_description']]);

            return redirect()->route('login');
        }

        if ($request->has('remember') && $request->input('remember') === '1') {
            session(['remember' => true]);
        }

        return redirect($authorizationURL);
    }

    public function callback(Request $request)
    {
        list($token, $error) = IndieAuthClient::complete($request->all());

        if ($error) {
            session(['auth_error' => $error['error']]);
            session(['auth_error_description' => $error['error_description']]);

            return redirect()->route('login');
        }

        $microsub = IndieAuthClient::discoverMicrosubEndpoint($token['me']);

        if (! $microsub) {
            session(['auth_error' => 'missing_endpoint']);
            session(['auth_error_description' => "We didn't find a Microsub endpoint at your website"]);

            return redirect()->route('login');
        }

        session(['token' => $token]);
        session(['microsub' => $microsub]);

        $micropub = IndieAuthClient::discoverMicropubEndpoint($token['me']);

        if ($micropub) {
            $config = get_micropub_config($micropub, $token);
            session(['micropub' => $config]);
        }

        if (session()->has('remember') && session('remember')) {
            $user = User::where('microsub', $microsub)
                ->first();

            if (! $user) {
                $user = new User([
                    'url' => $token['me'],
                    'microsub' => $microsub,
                ]);

                if ($micropub) {
                    $user->micropub = $micropub;
                }

                $user->save();
            }
        }

        // Redirect to homepage.
        return redirect('/');
    }
}

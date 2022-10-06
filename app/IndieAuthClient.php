<?php

namespace App;

use IndieAuth\Client;

class IndieAuthClient extends Client
{
    public static function begin($url, $scope = false)
    {
        if (! isset(self::$clientID) || ! isset(self::$redirectURL)) {
            return [false, [
                'error' => 'not_configured',
                'error_description' => 'Before you can begin, you need to configure the clientID and redirectURL of the IndieAuth client',
            ]];
        }

        $url = self::normalizeMeURL($url);
        $url = self::resolveMeURL($url);

        if (! $url) {
            return [false, [
                'error' => 'error_fetching_url',
                'error_description' => 'There was an error fetching the profile URL when checking for redirects.',
            ]];
        }

        $authorizationEndpoint = self::discoverAuthorizationEndpoint($url);

        if (! $authorizationEndpoint) {
            return [false, [
                'error' => 'missing_authorization_endpoint',
                'error_description' => 'Could not find your authorization endpoint',
            ]];
        }

        if ($scope) {
            $tokenEndpoint = self::discoverTokenEndpoint($url);

            if (! $tokenEndpoint) {
                return [false, [
                    'error' => 'missing_token_endpoint',
                    'error_description' => 'Could not find your token endpoint',
                ]];
            }
        }

        $state = self::generateStateParameter();

        session(['indieauth_url' => $url]);
        session(['indieauth_state' => $state]);
        session(['indieauth_authorization_endpoint' => $authorizationEndpoint]);

        if ($scope) {
            session(['indieauth_token_endpoint' => $tokenEndpoint]);
        }

        $authorizationURL = self::buildAuthorizationURL(
            $authorizationEndpoint,
            $url,
            self::$redirectURL,
            self::$clientID,
            $state,
            $scope
        );

        return [$authorizationURL, false];
    }

    public static function complete($params)
    {
        $requiredSessionKeys = ['indieauth_url', 'indieauth_state', 'indieauth_authorization_endpoint'];

        foreach ($requiredSessionKeys as $key) {
            if (! session()->has($key)) {
                return [false, [
                    'error' => 'invalid_session',
                    'error_description' => 'The session was missing data. Ensure that you are initializing the session before using this library',
                ]];
            }
        }

        if (isset($params['error'])) {
            return [false, [
                'error' => $params['error'],
                'error_description' => (isset($params['error_description']) ? $params['error_description'] : ''),
            ]];
        }

        if (! isset($params['code'])) {
            return [false, [
                'error' => 'invalid_response',
                'error_description' => 'The response from the authorization server did not return an authorization code or error information',
            ]];
        }

        if (! isset($params['state'])) {
            return [false, [
                'error' => 'missing_state',
                'error_description' => 'The authorization server did not return the state parameter',
            ]];
        }

        if ($params['state'] !== session('indieauth_state')) {
            return [false, [
                'error' => 'invalid_state',
                'error_description' => 'The authorization server returned an invalid state parameter',
            ]];
        }

        if (session()->has('indieauth_token_endpoint')) {
            $verify = self::getAccessToken(
                session('indieauth_token_endpoint'),
                $params['code'],
                session('indieauth_url'),
                self::$redirectURL,
                self::$clientID
            );
        } else {
            $verify = self::verifyIndieAuthCode(
                session('indieauth_authorization_endpoint'),
                $params['code'],
                null,
                self::$redirectURL,
                self::$clientID
            );
        }

        $expectedURL = session('indieauth_url');

        session()->forget('indieauth_url');
        session()->forget('indieauth_state');
        session()->forget('indieauth_authorization_endpoint');
        session()->forget('indieauth_token_endpoint');

        if (! isset($verify['me'])) {
            return [false, [
                'error' => 'indieauth_error',
                'error_description' => 'The authorization code was not able to be verified',
            ]];
        }

        if (parse_url($verify['me'], PHP_URL_HOST) !== parse_url($expectedURL, PHP_URL_HOST)) {
            return [false, [
                'error' => 'invalid user',
                'error_description' => 'The domain for the user returned did not match the domain of the user initially signing in',
            ]];
        }

        $verify['me'] = self::normalizeMeURL($verify['me']);

        return [$verify, false];
    }
}

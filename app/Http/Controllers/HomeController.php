<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        if (! logged_in()) {
            return redirect()->route('login');
        }

        if (! session()->has('channels')) {
            $this->reloadChannels();
        }

        if (! session()->has('channels') || ! is_array(session('channels'))) {
            // No channels found.
            abort(404);
        }

        // Load the first channel.
        $channel = session('channels')[0];

        return redirect(route('channel', $channel['uid']));
    }

    public function settings()
    {
        if (! logged_in()) {
            return redirect()->route('login');
        }

        $user = User::where('microsub', session('microsub'))
            ->first();

        if (empty($user)) {
            abort(404);
        }

        if ($user->settings) {
            $settings = json_decode($user->settings, true);
        }

        return view('settings', [
            'title' => __('Settings'),
            'settings' => $settings ?? [],
        ]);
    }

    public function store(Request $request)
    {
        if (! logged_in()) {
            return redirect()->route('login');
        }

        $user = User::where('microsub', session('microsub'))
            ->first();

        if (empty($user)) {
            abort(404);
        }

        $settings = [];

        if ($user->settings) {
            $settings = json_decode($user->settings, true);
        }

        if ($request->has('custom_css')) {
            // To do: validate.
            $settings['custom_css'] = strip_tags($request->input('custom_css'));
        }

        $user->settings = json_encode($settings);
        $user->save();
        $request->session()->flash('success', __('Saved!'));

        return redirect(route('settings'));
    }

    /**
     * Below: API stuff. (To do: move to its own controller.).
     */
    public function markRead(Request $request)
    {
        if (! logged_in()) {
            return response()->json('', 401);
        }

        if (! $request->has('channel') || ! $request->has('entry')) {
            return response()->json('', 400);
        }

        microsub_post(
            session('microsub'),
            session('token')['access_token'],
            'timeline', [
                'method' => 'mark_read',
                'channel' => $request->input('channel'),
                'entry' => $request->input('entry'),
            ]
        );

        return $this->reload();
    }

    public function markAllRead(Request $request)
    {
        if (! logged_in()) {
            return response()->json('', 401);
        }

        if (! $request->has('channel') || ! $request->has('entry')) {
            return response()->json('', 400);
        }

        microsub_post(
            session('microsub'),
            session('token')['access_token'],
            'timeline', [
                'method' => 'mark_read',
                'channel' => $request->input('channel'),
                'entry' => $request->input('entry'),
            ]
        );

        return $this->reload();
    }

    public function markUnread(Request $request)
    {
        if (! logged_in()) {
            return response()->json('', 401);
        }

        if (! $request->has('channel') || ! $request->has('entry')) {
            return response()->json('', 400);
        }

        microsub_post(
            session('microsub'),
            session('token')['access_token'],
            'timeline', [
                'method' => 'mark_unread',
                'channel' => $request->input('channel'),
                'entry' => $request->input('entry'),
            ]
        );

        return $this->reload();
    }

    public function remove(Request $request)
    {
        if (! logged_in()) {
            return response()->json('', 401);
        }

        if (! $request->has('channel') || ! $request->has('entry')) {
            return response()->json('', 400);
        }

        $response = microsub_post(
            session('microsub'),
            session('token')['access_token'],
            'timeline',
            [
                'channel' => $request->input('channel'),
                'method' => 'remove',
                'entry' => $request->input('entry'),
            ]
        );

        return response()->json([
                'entry' => $request->input('entry'),
                'response' => $response,
            ]);
    }

    public function unfollow(Request $request)
    {
        if (! logged_in()) {
            return response()->json('', 401);
        }

        if (! $request->has('channel') || ! $request->has('source')) {
            abort(400);
        }

        microsub_post(
            session('microsub'),
            session('token')['access_token'],
            'unfollow', [
                'channel' => $request->input('channel'),
                // Note that, for now, we're sending our own source ID rather
                // than a URL.
                'url' => $request->input('source'),
            ]
        );

        return $this->reload();
    }

    public function fetchOriginal(Request $request)
    {
        if (! logged_in()) {
            return response()->json('', 401);
        }

        if (! $request->has('channel') || ! $request->has('entry')) {
            return response()->json('', 400);
        }

        microsub_post(
            session('microsub'),
            session('token')['access_token'],
            'timeline', [
                'method' => 'fetch_original',
                'channel' => $request->input('channel'),
                'entry' => $request->input('entry'),
            ]
        );

        return $this->reload();
    }

    public function micropub(Request $request)
    {
        if (! logged_in()) {
            return response()->json('', 401);
        }

        $request->merge(['h' => 'entry']);

        $response = micropub_post_form(
            session('micropub')['endpoint'],
            session('token')['access_token'],
            $request->all()
        );

        $location = false;

        if (isset($response['headers']['Location'])) {
            $location = $response['headers']['Location'];
        }

        return response()->json([
            'location' => $location,
            'response' => $response,
        ]);
    }

    public function reload()
    {
        $response = $this->reloadChannels();

        if (! isset($response['channels'])) {
            return response()->json('', 500);
        }

        // Seems to return a response code of 200, also when the back end reports, e.g., a 502.
        return response()->json($response);
    }

    private function reloadChannels()
    {
        $token = session('token');

        if (empty($token['access_token'])) {
            return response()->json('', 403);
        }

        $response = microsub_get(
            session('microsub'),
            $token['access_token'],
            'channels'
        );

        if (isset($response['code']) && $response['code'] === 200) {
            $response = json_decode($response['body'], true);

            session(['channels' => $response['channels']]);
            session(['channels_timestamp' => time()]);
        } else {
            dd($response);
        }

        return $response;
    }
}

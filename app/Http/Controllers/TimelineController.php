<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function show(Request $request, string $uid, int $source = null, int $entry = null)
    {
        if (! logged_in()) {
            return redirect()->route('login');
        }

        $channel = null;

        foreach (session('channels') as $ch) {
            if ($ch['uid'] === $uid) {
                $channel = $ch;
                break;
            }
        }

        if (! isset($channel)) {
            abort(404);
        }

        // Array that holds query params as they'll be sent to the back end.
        $q = ['channel' => $uid];

        if ($request->has('before')) {
            $q['before'] = $request->query('before');
        }

        if ($request->has('after')) {
            $q['after'] = $request->query('after');
        }

        if ($source) {
            $q['source'] = $source;
        }

        if ($request->has('unread')) {
            $q['is_read'] = 'false';
        }

        $data = microsub_get(
            session('microsub'),
            session('token')['access_token'],
            'timeline',
            $q
        );
        $data = json_decode($data['body'], true);

        $entries = $data['items'] ?? [];

        if (isset($data['source'])) {
            $source = $data['source'];

            if ($entry) {
                foreach ($entries as $item) {
                    if ((int) $item['_id'] === $entry) {
                        $entries = [$item];
                    }
                }
            }
        }

        $paging = [];

        if ($entry === null) {
            $paging = $data['paging'] ?? [];
        }

        $destination = false;
        $responses_enabled = false;
        $micropub = null;

        if (session()->has('micropub')) {
            $micropub = session('micropub');
        }

        if (! empty($micropub['config']['destination'])) {
            foreach ($micropub['config']['destination'] as $dest) {
                // Enable the selected destination if the channel specifies one
                if (! empty($channel['destination']) && $dest['uid'] === $channel['destination']) {
                    $destination = $dest;
                    $responses_enabled = true;
                }
            }

            // If the channel doesn't specify one, use the first in the list
            if (! $destination) {
                $destination = $micropub['config']['destination'][0];
                $responses_enabled = true;
            }
        } else {
            // Enable responses if no destinations are configured or channel destination is not "none"
            $responses_enabled = (! isset($channel['destination']) || $channel['destination'] !== 'none');
        }

        try {
            $user = User::where('microsub', session('microsub'))
                ->first();
        } catch (\Throwable $t) {
            //
        }

        $title = request()->route()->getName() === 'entry' ? $entries[0]['name'] ?? $entries[0]['url'] ?? null : null;
        $title = $title ? __(':title – :source', ['title' => $title, 'source' => $source['name']]) : $source['name'] ?? $channel['name'] ?? config('app.name');

        return view('timeline', [
            'title' => $title,
            'channel' => $channel,
            'selected' => $channel['uid'],
            'source' => $source,
            'entries' => $entries,
            'paging' => $paging,
            'destination' => $destination,
            'responses_enabled' => $responses_enabled,
            'show_unread' => $request->has('unread'),
            'settings' => (! empty($user->settings) ? json_decode($user->settings, true) : null),
        ]);
    }
}

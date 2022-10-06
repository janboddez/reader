<div id="sidebar">
    <button id="menu-trigger">
        <i class="fas fa-bars" aria-hidden="true"></i> <span class="sr-only">Menu</span>
    </button>

    <nav>
        <ul id="channels" class="channels">
        @if (session()->has('channels'))
            @foreach (session('channels') as $channel)
                <li data-channel-uid="{{ $channel['uid'] }}" {!! (isset($selected) && $selected === $channel['uid'] ? 'class="selected"' : '') !!}>
                    <a href="{{ route('channel', urlencode($channel['uid'])) }}">
                        {{ $channel['name'] }}

                        @if (isset($channel['unread']))
                            <span class="tag is-hidden">{!! is_bool($channel['unread']) ? '&nbsp;' : htmlentities($channel['unread']) !!}</span>
                        @endif
                    </a>
                </li>
            @endforeach
        @endif

            @if (! empty($settings))
                <li class="settings {{ (request()->is('settings') ? 'selected' : '') }}"><a href="{{ route('settings') }}">{{ __('Settings') }}</a></li>
            @endif

            <li class="logout"><a href="{{ route('logout') }}" data-no-instant>{{ __('Log Out') }}</a></li>
        </ul>
    </nav>
</div>

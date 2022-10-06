<div class="header-wrapper">
    <div class="u-author h-card">
        @if (! empty($entry['author']['photo']))
            @if (isset($entry['_channel']) && isset($entry['_source']))
                <a href="/channel/{{ $entry['_channel'] }}/{{ $entry['_source'] }}"><img src="{{ proxy_image($entry['author']['photo'], '90x90') }}" class="u-photo" width="90" height="90" loading="lazy"></a>
            @elseif (isset($channel['uid']) && isset($entry['_source']))
                <a href="/channel/{{ $channel['uid'] }}/{{ $entry['_source'] }}"><img src="{{ proxy_image($entry['author']['photo'], '90x90') }}" class="u-photo" width="90" height="90" loading="lazy"></a>
            @elseif (! empty($entry['author']['url']))
                <a href="{{ $entry['author']['url'] }}"><img src="{{ proxy_image($entry['author']['photo'], '90x90') }}" class="u-photo" width="90" height="90" loading="lazy"></a>

                @if (! empty($entry['_author']['photo']))
                    <img src="{{ proxy_image($entry['_author']['photo'], '90x90') }}" class="reposter u-photo" width="90" height="90" loading="lazy">
                @endif
            @endif
        @elseif (isset($entry['_channel']) && isset($entry['_source']))
            <a href="/channel/{{ $entry['_channel'] }}/{{ $entry['_source'] }}"><img src="/images/no-profile-photo.png" class="u-photo" width="90" height="90" loading="lazy"></a>
        @elseif (isset($channel['uid']) && isset($entry['_source']))
            <a href="/channel/{{ $channel['uid'] }}/{{ $entry['_source'] }}"><img src="/images/no-profile-photo.png" class="u-photo" width="90" height="90" loading="lazy"></a>
        @else
            <img src="/images/no-profile-photo.png" class="u-photo" width="90" height="90" loading="lazy">

            @if (! empty($entry['_author']['photo']))
                <img src="{{ proxy_image($entry['_author']['photo'], '90x90') }}" class="reposter u-photo" width="90" height="90" loading="lazy">
            @endif
        @endif

        @if (! empty($entry['author']['name']) || ! empty($entry['author']['url']))
            <div class="author-name">
                @if (! empty($entry['author']['url']))
                    @if (! empty($entry['author']['name']))
                        @if (isset($entry['_source']) && isset($entry['_channel']))
                            <a href="/channel/{{ $entry['_channel'] }}/{{ $entry['_source'] }}" class="p-name">{{ $entry['author']['name'] }}</a>
                        @else
                            <a href="{{ $entry['author']['url'] }}" class="p-name">{{ $entry['author']['name'] }}</a>
                        @endif
                    @endif

                    <a href="{{ $entry['author']['url'] }}" class="u-url">{{ $entry['author']['url'] }}</a>
                @elseif (! empty($entry['author']['name']))
                    <span class="p-name">{{ $entry['author']['name'] }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="datetime">
        @if (! empty($entry['published']))
            @if (isset($entry['_channel']) && isset($entry['_source']) && isset($entry['_id']))
                {{-- Link to the individual entry rather than the original URL. --}}
                <a href="/channel/{{ $entry['_channel'] }}/{{ $entry['_source'] }}/{{ $entry['_id'] }}" class="u-url u-uid">
                    <time class="dt-published" datetime="{{ date('c', strtotime($entry['published'])) }}" title="{{ date('F j, Y g:i A P', strtotime($entry['published'])) }}">
                        {{ display_date('F j, Y g:i A', $entry['published']) }}
                    </time>
                </a>
            @elseif (! empty($entry['url']))
                <a href="{{ $entry['url'] }}" class="u-url u-uid">
                    <time class="dt-published" datetime="{{ date('c', strtotime($entry['published'])) }}" title="{{ date('F j, Y g:i A P', strtotime($entry['published'])) }}">
                        {{ display_date('F j, Y g:i A', $entry['published']) }}
                    </time>
                </a>
			@else
                <time class="dt-published" datetime="{{ date('c', strtotime($entry['published'])) }}" title="{{ date('F j, Y g:i A P', strtotime($entry['published'])) }}">
                    {{ display_date('F j, Y g:i A', $entry['published']) }}
                </time>
            @endif
        @elseif (! empty($entry['url']))
            <a href="{{ $entry['url'] }}" class="u-url u-uid">{{ __('Permalink') }}</a>
        @endif
    </div>
</div>

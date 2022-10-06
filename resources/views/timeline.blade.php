@extends('layouts.app')

@section('content')
    <main id="content" class="h-feed">
        @forelse ($entries as $i => $entry)
            @php
                $collapse = false;

                if ((! empty($entry['summary']) && mb_strlen(strip_tags($entry['summary'])) > 500) ||
                    (! empty($entry['content']['text']) && mb_strlen(strip_tags($entry['content']['text'])) > 500) ||
                    (! empty($entry['content']['html']) && mb_strlen(strip_tags($entry['content']['html'])) > 500)) {
                    $collapse = true;
                }

				if (request()->route()->getName() === 'entry') {
				    // Never collapse items on single-entry pages.
					$collapse = false;
				}
            @endphp

            <article class="h-entry {{ ($entry['post-type'] ?? '') }} {{ ($collapse ? 'collapse is-collapsed' : '') }} {{ (! empty($entry['_is_read']) ? 'read' : 'unread') }}"
                data-entry="{{ $i }}" data-entry-id="{{ $entry['_id'] }}"
                {!! (! empty($entry['_is_read']) ? 'data-is-read="1"' : 'data-is-read="0"') !!}
                {!! (! empty($entry['_channel']) ? 'data-channel-uid="'.$entry['_channel'].'"' : '') !!}
                {!! (! empty($entry['_source']) ? 'data-source-id="'.$entry['_source'].'"' : '') !!}>

                @if (! empty($entry['like-of']))

                    <header class="entry-header">
                        <div class="context">
                            @foreach ((array) $entry['like-of'] as $r)
                                <div class="like-of"><i class="fa fa-star"></i> <a href="{{ $r }}" class="u-like-of">{{ $r }}</a></div>
                                @break
                            @endforeach
                        </div>

                        @include('partials.author')
                    </header>

                    {{-- @include('partials.meta') --}}

                @else

                    <header class="entry-header">
                        @if (! empty($entry['in-reply-to']))
                            <div class="context">
                                @foreach ((array) $entry['in-reply-to'] as $r)
                                    <div class="in-reply-to"><i class="fa fa-reply"></i> <a href="{{ $r }}" class="u-in-reply-to">{{ $r }}</a></div>
                                    @break
                                @endforeach
                            </div>
                        @elseif (! empty($entry['bookmark-of']))
                            <div class="context">
                                @foreach ((array) $entry['bookmark-of'] as $r)
                                    <div class="bookmark-of"><i class="fa fa-bookmark"></i> <a href="{{ $r }}" class="u-bookmark-of">{{ $r }}</a></div>
                                    @break
                                @endforeach
                            </div>
                        @elseif (! empty($entry['repost-of']))
                            @php
                                $entry['repost-of'] = (array) $entry['repost-of'];
                            @endphp
                            <div class="context">
                                <div class="repost-of"><i class="fa fa-retweet"></i> <a href="{{ $entry['repost-of'][0] }}" class="u-repost-of">{{ $entry['repost-of'][0] }}</a></div>
                            </div>

                            @if (isset($entry['refs'][$entry['repost-of'][0]]))
                                {{-- Show the original, reposted entry instead --}}
                                @php
                                    $_id = $entry['_id'];
                                    $author = $entry['author'] ?? null;
                                    $published = $entry['published'] ?? null;
                                    $url = $entry['url'] ?? null;

                                    $entry = $entry['refs'][$entry['repost-of'][0]];
                                    $entry['published'] = $published;
                                    $entry['url'] = $url;
                                    $entry['_id'] = $_id;
                                    $entry['_author'] = $author;
                                @endphp
                            @endif
                        @endif

                        @if (isset($entry['type']) && $entry['type'] === 'feed' && isset($entry['items'][0]) && count($entry['items']) === 1)
                            {{-- We've somehow encountered a single-entry feed, let's show the entry --}}
                            @php
                                $_id = $entry['_id'];
                                $published = $entry['published'] ?? null;
                                $url = $entry['url'] ?? null;
                                $entrySource = $entry['_source'] ?? null;

                                $entry = $entry['items'][0];
                                $entry['published'] = $published;
                                $entry['url'] = $url;
                                $entry['_id'] = $_id;
                                $entry['_source'] = $entrySource;
                            @endphp
                        @endif

                        @include('partials.author')

                        @if (! empty($entry['name']))
                            @if (! empty($entry['url']))
                                <h2><a href="{{ $entry['url'] }}" class="u-url u-uid"><span class="p-name">{{ html_entity_decode($entry['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span></a></h2>
                            @else
                                <h2><span class="p-name">{{ html_entity_decode($entry['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span></h2>
                            @endif
                        @elseif (! empty($entry['checkin']['name']))
                            <h2>
                                @if (! empty($entry['checkin']['url']))
                                    <a href="{{ $entry['checkin']['url'] }}" class="u-url u-uid">{{ $entry['checkin']['name'] }} <i class="fa fa-map-marker-alt"></i></a>
                                @else
                                    {{ $entry['checkin']['name'] }} <i class="fa fa-map-marker-alt"></i>
                                @endif
                            </h2>
                        @endif
                    </header>

                @endif

                @if ($collapse)
                    <div class="read-more"><button>{{ __('Expand') }}</button></div>
                @endif

                @if (! empty($entry['rsvp']))
                    <div class="entry-content text">
                        <div class="p-content"><p>{{ __('RSVP’d: :rsvp.', ['rsvp' => $entry['rsvp']]) }}</p></div>
                    </div>
                @endif

                @if (! empty($entry['checkin']['latitude']) && ! empty($entry['checkin']['longitude']))
                    <div class="entry-content p-checkin">
                        <p class="map"><img src="{!! proxy_image('https://atlas.p3k.io/map/img?marker[]=lat:'.(float) $entry['checkin']['latitude'].';lng:'.(float) $entry['checkin']['longitude'].';icon:small-blue-cutout&basemap=gray&width=568&height=240&zoom=16') !!}"></p>
                    </div>
                @elseif (! empty($entry['content']['html']))
                    <div class="entry-content html">
                        <div class="e-content">{!! make_clickable(wpautop(proxy_images(minimize(p3k\HTML::sanitize($entry['content']['html'])), (! empty($entry['url']) ? $entry['url'] : '')), false)) !!}</div>
                    </div>
                @elseif (! empty($entry['content']['text']))
                    <div class="entry-content text">
                        <div class="p-content">{!! make_clickable(p3k\HTML::sanitize($entry['content']['text'], ['baseURL' => (isset($entry['url']) ? parse_url($entry['url'], PHP_URL_SCHEME).'://'.parse_url($entry['url'], PHP_URL_HOST).'/' : false)])) !!}</div>
                    </div>
                @endif

                @if (empty($entry['checkin']) && empty($entry['content']) && empty($entry['audio'])
                    && empty($entry['video']) && empty($entry['photo']) && ! empty($entry['summary']))
                    <div class="entry-content">
                        <div class="p-summary">{!! make_clickable(p3k\HTML::sanitize($entry['summary'], ['baseURL' => (isset($entry['url']) ? parse_url($entry['url'], PHP_URL_SCHEME).'://'.parse_url($entry['url'], PHP_URL_HOST).'/' : false)]), ENT_QUOTES | ENT_HTML5, 'UTF-8') !!}</div>
                    </div>
                @endif

                @include('partials.photo-and-video')

                @include('partials.meta')

                @include('partials.actions')

                <pre style="display: none;" class="source">{{ json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </article>
        @empty
            <section>
                <header class="entry-header">
                    <h2>{{ __('All Done') }}</h2>
                </header>
                <div class="entry-content">
                    <p>{{ __("It seems you're all caught up!") }}</p>
                </div>
            </section>
        @endforelse

        @if (! empty($paging['after']))
            <nav class="pagination">
                <a class="button" href="?after={{ $paging['after'] . ($show_unread ? '&unread' : '') }}" data-instant>{{ __('Older Entries') }}</a>
            </nav>
        @endif
    </main>

    <input type="hidden" id="last-id" value="{{ $entries[0]['_id'] ?? '' }}">
    <input type="hidden" id="channel-uid" value="{{ $channel['uid'] }}">
@endsection

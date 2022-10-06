<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" href="/images/newspaper.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/all.min.css">
    <link rel="stylesheet" href="/css/fonts.css">
    <style>
    textarea {
        min-height: 5rem;
        resize: vertical;
    }

    html {
        overflow-x: hidden;
        overflow-y: scroll;
    }

    body {
        background: #fff;
        color: #222;
        font-size: 87.5%;
        margin: 0;
        padding: 0;
    }

    @media only screen and (min-width: 75rem) {
        body {
            font-size: 100%;
        }
    }

    body,
    button,
    input,
    select,
    textarea {
        font-family: "Inter", sans-serif;
        line-height: 1.5;
    }

    button,
    input,
    select,
    textarea {
        margin: 0;
        font-size: inherit;
    }

    .sr-only {
        clip: rect(1px,1px,1px,1px);
        position: absolute !important;
        height: 1px;
        width: 1px;
        overflow: hidden;
    }

    a.sr-only:focus {
        left: 0.25rem;
        top: 0.25rem;
        display: block;
        width: auto;
        height: auto;
        clip: auto !important;
        border: 0.0625rem solid currentColor;
        background: #fff;
        text-decoration: none;
        padding: 0.75rem 1rem;
        z-index: 1000;
    }

    .is-hidden {
        display: none !important;
    }

    a:link,
    a:visited {
        color: #1228a6;
    }

    a:hover,
    a:active,
    a:focus {
        color: #608ba6;
    }

    button,
    [type="button"],
    [type="reset"],
    [type="submit"],
    .button:link,
    .button:visited {
        -webkit-appearance: button;
        display: inline-block;
        border: 0.0625rem solid #989898;
        border-radius: 0.25rem;
        background: linear-gradient(#f8f8f8, #eee);
        color: #686868;
        text-shadow: 0 1px 1px white;
        padding: 0.25rem 0.375rem;
        box-sizing: border-box;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
    }

    button:hover,
    button:active,
    button:focus,
    .button:hover,
    .button:active,
    .button:focus {
        color: #222;
    }

    ul {
        padding-left: 1.5rem;
    }

    #page {
        margin: 0 auto;
        width: 100%;
        max-width: 40em;
        padding: 0 1rem;
        box-sizing: border-box;
    }

    #sidebar {
        background-color: #1228a6;
        color: #fff;
        width: 16rem;
        height: 100vh;
        position: fixed;
        top: 0;
        bottom: 0;
        left: -16rem;
        z-index: 15;
        overflow-x: hidden;
    }

    #sidebar.open {
        left: 0;
    }

    @media only screen and (min-width: 75rem) {
        #sidebar {
            left: 0;
        }
    }

    #page > header {
        margin: 1.25rem 3.5rem 1.75rem 1.25rem;
    }

    #page > header h1 {
        margin: 0;
    }

    #page > header h1 a {
        text-decoration: none;
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .main-navigation ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .main-navigation li,
    .main-navigation a {
        display: inline-block;
    }

    .main-navigation li:not(:first-child)::before {
        content: " / ";
    }

    #menu-trigger {
        position: fixed;
        z-index: 10;
        right: 1.125rem;
        top: 1.125rem;
        width: 48px;
        height: 48px;
        background: white;
        font-size: 1.25rem;
        border: none;
        border-radius: 50%;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        padding: 14px;
    }

    #menu-trigger i {
        display: block;
        position: relative;
        top: 0.0625rem;
    }

    @media only screen and (min-width: 75rem) {
        #menu-trigger {
            display: none;
        }
    }

    .channels {
        list-style: none;
        margin: 1.25rem auto;
        padding: 1rem;
        box-sizing: border-box;
    }

    .channels li:not(:last-child) {
        margin-bottom: 0.25rem;
    }

    .channels a {
        display: block;
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        color: rgba(255, 255, 255, 0.75);
        text-decoration: none;
    }

    .channels a:hover,
    .channels .selected a {
        background-color: rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .channels .logout a,
    .channels .settings a {
        color: rgba(255, 255, 255, 0.5);
    }

    .channels .logout a:hover,
    .channels .settings a:hover,
    .channels .settings.selected a {
        color: rgba(255, 255, 255, 0.75);
    }

    .channels .tag {
        background: #209cee;
        color: #fff;
        border-radius: 50%;
        font-size: 0.75rem;
        float: right;
        width: 1.5em;
        height: 1.5em;
        line-height: 1.5em;
        text-align: center;
        position: relative;
        top: 0.125rem;
    }

    @media (min-width: 1200px) {
        .channels .tag {
            top: 0.1875rem;
        }
    }

    main {
        margin-bottom: 1.75rem;
    }

    main > article,
    main > section {
        background: #fff;
        overflow-wrap: break-word;
        word-wrap: break-word;
        padding: 1.25rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.15);
    }

    main > .message {
        padding: 1.1875rem;
        margin-bottom: 2rem;
        border: 0.0625rem solid #ccc;
        border-radius: 0.75rem;
    }

    main > section h2:first-of-type {
        margin-top: 0.5rem;
    }

    .h-entry {
        position: relative;
    }

    .h-entry:last-child {
        margin-bottom: 0;
    }

    .collapse {
        padding-bottom: 4.5rem;
    }

    .is-collapsed {
        max-height: 12.75rem;
        height: auto;
        overflow: hidden;
    }

    .is-collapsed::after {
        content: "";
        background: linear-gradient(rgba(255, 255, 255, 0) 0%,
                                    rgba(255, 255, 255, 1) 65%);
        position: absolute;
        left: 0;
        right: 0;
        bottom:0;
        padding-top: 10.5rem;
    }

    .read-more {
        position: absolute;
        bottom: 1.25rem;
        left: 1.25rem;
        right: 1.25rem;
        z-index: 5;
    }

    .entry-header {
        margin-bottom: 1.125rem;
    }

    .context {
        background: #f8f8f8;
        margin: -1.25rem -1.25rem 1.25rem;
        padding: 0.875rem 1.25rem;
    }

    .context .fa {
        color: #777;
        margin-right: 0.125rem;
        position: relative;
        top: 0.0625rem;
    }

    .context > :first-child,
    .u-author.h-card .author-name .p-name,
    .u-author.h-card .author-name .u-url {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .header-wrapper {
        display: flex;
        justify-content: space-between;
    }

    .u-author.h-card {
        display: flex;
        min-width: 0;
    }

    .u-author.h-card > :first-child {
        flex-shrink: 0;
    }

    .u-author.h-card img {
        /* It's safe to force a 1:1 aspect ratio here, as these are always
        cropped by our proxy. */
        width: 3rem;
        height: 3rem;
        border-radius: 50%;
        display: block;
        margin-bottom: 0;
    }

    .u-author.h-card img.reposter {
        width: 1.5rem;
        height: 1.5rem;
        position: relative;
        left: -3.33rem;
        top: -.33rem;
        margin-right: -1.5rem;
        z-index: 1;
    }

    .u-author.h-card .author-name {
        min-width: 0;
    }

    .u-author.h-card .author-name:not(:first-child) {
        margin-left: 0.75rem;
    }

    .u-author.h-card .author-name .p-name,
    .u-author.h-card .author-name .u-url {
        display: block;
    }

    .datetime {
        flex-shrink: 0;
        margin-left: 1rem;
    }

    .datetime > a {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 10em;
    }

    .entry-header h2 {
        margin: 0.75rem 0 0;
    }

    .entry-content {
        font-family: 'Merriweather';
        line-height: 1.67;
    }

    blockquote {
        background: #f8f8f8;
        border-left: 0.25rem solid #e8e8e8;
        margin: 1.25rem 0;
        padding: 1.25rem;
    }

    blockquote > :first-child {
        margin-top: 0;
    }

    blockquote > :last-child {
        margin-bottom: 0;
    }

    blockquote > blockquote {
        margin: 0;
        padding: 0;
        border: none;
    }

    .e-content .h-card.p-author .u-photo {
        border-radius: 0.25rem;
        display: inline-block;
        margin: 0 0.25rem 0 0;
        position: relative;
        top: -0.125rem;
    }

    .e-content .h-card.p-author .u-photo + .u-photo {
        display: none;
    }

    img,
    video {
        max-width: 100%;
        height: auto;
    }

    video {
        display:block;
        margin: 0 auto;
    }

    .entry-content img,
    .entry-content video {
        vertical-align: middle;
    }

    /* WordPress emoji. */
    .entry-content img[src*="images/core/emoji"] {
        max-height: 1.25rem;
        vertical-align: baseline;
        position: relative;
        top: 0.25rem;
    }

    .photos {
        margin-top: 1rem;
    }

    .photos > :not(.multi-photo) img:not(:last-child) {
        margin-bottom: 1rem;
    }

    .multi-photo {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-gap: 0.75rem;
        gap: 0.75rem;
    }

    .multi-photo img {
        margin: 0;
    }

    pre {
        overflow-x: scroll;
    }

    pre,
    code {
        font-family: monospace, monospace;
    }

    .read-more button,
    .pagination .button {
        display: block;
        text-align: center;
    }

    .read-more button {
        width: 100%;
    }

    main > .pagination:first-child {
        margin-bottom: 2.25rem;
    }

    .entry-meta {
        margin-top: 1rem;
    }

    .actions {
        margin-top: 1rem;
    }

    .categories {
        margin-bottom: 1rem;
        color: #686868;
    }

    .action-buttons,
    .action-buttons ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .action-buttons {
        display: flex;
        justify-content: space-around;
    }

    .action-buttons ul {
        display: none;
    }

    .action-buttons li:last-child {
        position: relative;
    }

    .action-buttons button {
        background: none;
        color: #989898;
        border: none;
        border-radius: 50%;
        width: 2em;
        line-height: 2em;
        text-align: center;
        padding: 0;
        font-weight: normal;
    }

    .dropdown-menu {
        display: block;
        position: absolute;
        width: 14rem;
        left: -0.75rem;
        top: 2.25rem;
        background: #fff;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.2);
        z-index: 20;
        padding: 0.25rem 0;
    }

    .dropdown-menu button {
        width: 100%;
        padding: 0.325rem 0.75rem;
        text-align: left;
    }

    .dropdown-menu i.fa {
        margin-right: 0.125rem;
    }

    .action-buttons button:hover,
    .action-buttons .is-active i.fa {
        color: #686868;
    }

    .action-buttons .is-loading i.fa {
        color: #209cee;
    }

    .new-bookmark,
    .new-reply {
        display: none;
        margin-top: 1.25rem;
    }

    .new-bookmark textarea,
    .new-reply textarea {
        display: block;
        width: 100%;
        padding: 0.5rem;
        box-sizing: border-box;
    }

    .new-bookmark div,
    .new-reply div {
        margin-top: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .syndicate-to {
        display: block;
    }

    .new-bookmark button,
    .new-reply button {
        margin-top: 0.25rem;
    }

    .new-bookmark button.is-loading,
    .new-reply button.is-loading {
        color: transparent;
        text-shadow: none;
        position: relative;
    }

    .new-bookmark button.is-loading::after,
    .new-reply button.is-loading::after {
        position: absolute;
        left: calc(50% - (1.125rem / 2));
        top: calc(50% - (1.125rem / 2));
        display: block;
        content: "";
        border: 0.125rem solid #dbdbdb;
        border-radius: 50%;
        border-right-color: transparent;
        border-top-color: transparent;
        height: 1em;
        width: 1em;
        -webkit-animation: spinAround 1s infinite linear;
                animation: spinAround 1s infinite linear;
    }

    @media (min-width: 1200px) {
        .new-bookmark button.is-loading::after,
        .new-reply button.is-loading::after {
            left: calc(50% - (1.25rem / 2));
            top: calc(50% - (1.25rem / 2));
        }
    }

    .h-entry.unread {
        box-shadow: 0 1px 5px rgba(32, 156, 238, 0.5);
    }

    @keyframes spinAround {
        from {
            transform:rotate(0)
        }
        to {
            transform:rotate(359deg)
        }
    }

    /* Button we're styling as a link, as it appears in the top menu. */
    #mark-all-read {
        border: none;
        background: none;
        text-decoration: underline;
        padding: 0;
        display: inline;
        color: #1228a6;
        font-weight: normal;
    }

    #mark-all-read:active,
    #mark-all-read:hover,
    #mark-all-read:focus {
        color: #608ba6;
        -moz-user-select: text;
    }

    .field textarea {
        width: 100%;
        box-sizing: border-box;
        font-family: monospace, monospace;
    }

    .field button[type="submit"] {
        margin: 0.75rem 0 0;
        float: right;
    }

    .field::after {
        content: "";
        display: table;
        clear: both;
    }
    </style>

    @if (! empty($settings['custom_css']))
        <style>{!! $settings['custom_css'] !!}</style>
    @endif
</head>
<body>
    <div id="page">
        <header>
            @if (request()->route()->getName() === 'entry')
                <h1><a href="{{ preg_replace('~/(\d+)$~', '', url()->current()) }}">{{ $title ?? config('app.name') }}</a></h1>
			@else
                <h1><a href="{{ url()->current() }}">{{ $title ?? config('app.name') }}</a></h1>
			@endif

            @if (request()->route()->getName() === 'entry')
                <nav class="main-navigation">
                    <ul>
                        <li><button id="mark-all-read">{{ __('Mark Read') }}</button></li>
                    </ul>
                </nav>
            @elseif (request()->is('channel*'))
                <nav class="main-navigation">
                    <ul>
                        @if (empty($channel['uid']) || (! empty($channel['uid']) && 'unread' !== $channel['uid']))
                            <li><a href="?unread" data-instant>{{ __('Unread Only') }}</a></li>
                        @endif

                        <li><button id="mark-all-read">{{ __('Mark All Read') }}</button></li>
                    </ul>
                </nav>
            @endif
        </header>

        @include('partials/sidebar')

        @section('content')
            <main id="content">
                <h2>{{ __('Not Found') }}</h2>
                <p>{{ __("It seems we couldn't find what you were looking for.") }}</p>
            </main>
        @show

        <footer></footer>
    </div>

    <script src="/js/jquery-3.5.1.min.js"></script>
    <script>
    $('#menu-trigger').click(function(e) {
        e.stopPropagation();

        if (! $('#sidebar').hasClass('open')) {
            showSidebar();
        } else {
            hideSidebar();
        }
    });

    $('html').click(function() {
        if ($('#sidebar').hasClass('open')) {
            hideSidebar();
        }

        $('.dropdown-trigger').removeClass('is-active');
        $('.dropdown-menu').hide();
    });

    $('#sidebar').click(function(e) {
        e.stopPropagation();
    });

    $('.dropdown-trigger, .dropdown-menu').click(function(e) {
        e.stopPropagation();
    });

    function showSidebar() {
        $('#sidebar').addClass('open');
        $('#menu-trigger i').removeClass('fa-bars').addClass('fa-times');
    }

    function hideSidebar() {
        $('#sidebar').removeClass('open');
        $('#menu-trigger i').removeClass('fa-times').addClass('fa-bars');
    }

    $('.logout a').click(function() {
        return confirm('{{ __('Are you sure you want to log out?') }}');
    });

    $(document).keydown(function(e) {
        // Escape.
        if (e.keyCode === 27) {
            if ($('#sidebar').hasClass('open')) {
                // Hide sidebar.
                hideSidebar();
            }

            // Hide all action menus.
            $('.dropdown-trigger').removeClass('is-active');
            $('.dropdown-menu').hide();
        }
    });

    $(document).keyup(function(e) {
        if ($(e.target).closest('input, textarea')[0]) {
            return false;
        }

        // Period.
        if (e.keyCode === 190) {
            $('#menu-trigger').focus();
            $('#menu-trigger').click();
        }
    });
    </script>

    <script>
    var lastReloadTimestamp = {{ (session()->has('channels_timestamp') ? session('channels_timestamp') : time()) }};

    function reloadChannels() {
        $.post('/channels/reload?format=json', function(response) {
            updateChannelList(response.channels);
        });
    }

    var tmrAjax;

    function timers() {
        tmrAjax = setInterval(function(){
            // Every 5 seconds, check how long it's been since the last channel reload,
            // and reload the channel list if it's been > 1 minute.
            var diff = parseInt(Date.now()/1000) - lastReloadTimestamp;

            if (diff > 60) {
                reloadChannels();
            }
        }, 5000);
    }

    timers();

    $(window).blur(function () {
        // Stop the timer above when the window (or tab) loses focus. Should save
        // some server resources.
        clearInterval(tmrAjax);
    }).focus(timers); // Start it again when the window regains focus.

    // Reload channels immediately after page load, once.
    reloadChannels();

    function updateChannelList(channels) {
        channels.forEach(function(ch) {
            lastReloadTimestamp = parseInt(Date.now()/1000);

            if (ch.unread && ch.unread > 0) {
                $('.channels li[data-channel-uid="' + ch.uid + '"] .tag').removeClass('is-hidden').text(typeof ch.unread == 'number' ? ch.unread : '');
            } else {
                $('.channels li[data-channel-uid="' + ch.uid + '"] .tag').addClass('is-hidden').text('');
            }
        });
    }

    $('.h-entry').click(function() {
        var entry = $(this);

        if (entry.data('is-read') == 0) {
            // Make it appear read.
            entry.data('is-read', 1);
            entry.removeClass('unread').addClass('read');

            $.post('/microsub/mark-read', {
                channel: $('#channel-uid').val(),
                entry: entry.data('entry-id')
            }, function(response) {
                updateChannelList(response.channels);
            });
        }
    });

    $('.entry-header .h-card').click(function(e) {
        e.stopPropagation();
    });

    $('#mark-all-read').click(function() {
        var entryIds = [];

        $('.h-entry').each(function() {
            var entry = $(this);

            if (entry.data('is-read') == 0) {
                // Push items _currently_ unread onto pile.
                entryIds.push(entry.data('entry-id'));

                // Make them appear read.
                entry.data('is-read', 1);
                entry.removeClass('unread').addClass('read');
            }
        });

        if (entryIds.length > 0) {
            $.post('/microsub/mark-all-read', {
                channel: $('#channel-uid').val(),
                entry: entryIds
            }, function(response) {
                updateChannelList(response.channels);
            });
        }
    });

    function addDestination(params) {
        if ($("#destination-uid").val()) {
            params['mp-destination'] = $('#destination-uid').val();
        }

        return params;
    }

    function addResponseUrl(i, url) {
        $('.entry[data-entry="' + i + '"] .action-responses').append('<div><a href="' + url + '">' + url + '</a></div>');
    }

    $('.collapse').each(function() {
        var entry = $(this);

        // Prevent tabbing to non-header links, and preformatted text and buttons.
        entry.find('> :not(.entry-header) a, pre, > :not(.read-more) button').prop('tabindex', '-1');
    });

    $('.read-more button').click(function(e) {
        var entry = $(this).closest('.h-entry');

        if (entry.hasClass('is-collapsed')) {
            // Was collapsed.
            entry.removeClass('is-collapsed');
            $(this).text('{{ __('Collapse') }}');

            entry.find('> :not(.entry-header) a, pre, > :not(.read-more) button').prop('tabindex', 'none');
            // Move the Read More button down, so that a user tabbing through the page
            // encounters it at the end of the entry.
            entry.find('.read-more').appendTo(entry);
        } else {
            // Was expanded.
            e.stopPropagation(); // Do _not_ mark as read on collapse.

            hideSidebar();
            entry.find('.new-reply').hide();
            entry.find('.new-bookmark').hide();

            entry.addClass('is-collapsed');
            $(this).text('{{ __('Expand') }}');

            entry.find('> :not(.entry-header) a, pre, > :not(.read-more) button').prop('tabindex', '-1');
            // Move the Read More button up, so that a user tabbing through the page
            // encounters it before any of the entry body links (which we've just
            // disabled).
            entry.find('.read-more').insertAfter(entry.find('.entry-header'));
        }
    });

    $('.action-buttons .dropdown-trigger').click(function() {
        var btn = $(this);
        var menu = btn.parent().find('.dropdown-menu');

        if (menu.is(':hidden')) {
            btn.closest('.action-buttons').find('[data-action="reply"], [data-action="bookmark"]').removeClass('is-active');
            btn.closest('.actions').find('.new-reply, .new-bookmark').hide();

            btn.addClass('is-active');

            if ($(window).width() < 768) {
                menu.css('left', '-10rem');
            } else {
                menu.css('left', '-0.75rem');
            }

            menu.show();
        } else {
            btn.removeClass('is-active');
            menu.hide();
        }
    });

    $('.action-buttons button[data-action]').click(function() {
        var btn = $(this);

        switch (btn.data('action')) {
            case 'favorite':
                if (! confirm('{{ __('Favorite this?') }}')) {
                    return false;
                }

                btn.addClass('is-loading');

                $.post('/micropub', addDestination({
                    'like-of': btn.closest('.actions').data('url')
                }), function(response) {
                    btn.removeClass('is-loading');

                    if (response.location) {
                        addResponseUrl(btn.closest('.h-entry').data('entry'), response.location);
                    }
                });
                break;

            case 'repost':
                if (! confirm('{{ __('Repost this?') }}')) {
                    return false;
                }

                btn.addClass('is-loading');

                $.post('/micropub', addDestination({
                    'repost-of': btn.closest('.actions').data('url')
                }), function(response) {
                    btn.removeClass('is-loading');

                    if (response.location) {
                        addResponseUrl(btn.closest('.h-entry').data('entry'), response.location);
                    }
                });
                break;

            case 'reply':
                var newReply = btn.closest('.actions').find('.new-reply');

                if (newReply.is(':hidden')) {
                    btn.closest('.action-buttons').find('[data-action="bookmark"], .dropdown-trigger').removeClass('is-active');
                    btn.closest('.actions').find('.new-bookmark').hide();
                    btn.closest('.action-buttons').find('.dropdown-menu').hide();

                    btn.addClass('is-active');
                    newReply.show();
                    newReply.find('textarea').focus();
                } else {
                    btn.removeClass('is-active');
                    newReply.hide();
                }
                break;

            case 'bookmark':
                var newBookmark = btn.closest('.actions').find('.new-bookmark');

                if (newBookmark.is(':hidden')) {
                    btn.closest('.action-buttons').find('[data-action="reply"], .dropdown-trigger').removeClass('is-active');
                    btn.closest('.actions').find('.new-reply').hide();
                    btn.closest('.action-buttons').find('.dropdown-menu').hide();

                    btn.addClass('is-active');
                    newBookmark.show();
                    newBookmark.find('textarea').focus();
                } else {
                    btn.removeClass('is-active');
                    newBookmark.hide();
                }
                break;

            case 'remove':
                btn.addClass('is-active');

                $.post('/microsub/remove', {
                    channel: btn.closest('.h-entry').data('channel-uid') || $('#channel-uid').val(),
                    entry: btn.closest('.h-entry').data('entry-id')
                }, function(response) {
                    btn.removeClass('is-active');
                    $('.h-entry[data-entry-id="' + response.entry + '"]').remove();
                });
                break;

            case 'debug':
                var source = btn.closest('.h-entry').find('.source');

                if (source.is(':hidden')) {
                    source.show();
                    btn.addClass('is-active');
                } else {
                    source.hide();
                    btn.removeClass('is-active');
                }

                break;

            case 'mark-unread':
                var entry = btn.closest('.h-entry');

                // Make it look like it worked immediately
                entry.data('is-read', 0);
                entry.removeClass('read').addClass('unread');

                btn.closest('.action-buttons').find('.dropdown-trigger').removeClass('is-active');
                btn.closest('.action-buttons').find('.dropdown-menu').hide();

                $.post('/microsub/mark-unread', {
                    channel: btn.closest('.h-entry').data('channel-uid') || $('#channel-uid').val(),
                    entry: entry.data('entry-id')
                }, function(response) {
                    updateChannelList(response.channels);
                });
                break;

            case 'unfollow':
                if (! confirm('{{ __('Unfollow this source?') }}')) {
                    return false;
                }

                var sourceId = btn.closest('.h-entry').data('source-id');

                $.post('/microsub/unfollow', {
                    channel: btn.closest('.h-entry').data('channel-uid') || $('#channel-uid').val(),
                    source: sourceId
                }, function(response) {
                    //
                });

                break;

            case 'fetch-original':
                $.post('/microsub/fetch-original', {
                    channel: btn.closest('.h-entry').data('channel-uid') || $('#channel-uid').val(),
                    entry: btn.closest('.h-entry').data('entry-id')
                }, function(response) {
                    //
                });
                break;

            default:
                console.log('Unknown action');
        }
    });

    $('.new-reply button').click(function() {
        var btn = $(this);
        var reply = btn.closest('.new-reply');

        if ('' === reply.find('textarea').val()) {
            return false;
        }

        var args = {
            'in-reply-to': btn.closest('.actions').data('url'),
            'content': reply.find('textarea').val()
        };

        var targets = [];

        btn.parent().find('[name^="syndicate_to"]').each(function() {
            if ($(this).is(':checked')) {
                targets.push($(this).val());
            }
        });

        if (targets.length !== 0) {
            $.extend(args, {
                'mp-syndicate-to': targets
            });
        }

        btn.addClass('is-loading');

        $.post('/micropub', addDestination(args), function(response) {
            btn.removeClass('is-loading');

            if (response.location) {
                btn.removeClass('is-danger');
                reply.find('textarea').val('');
                reply.hide();
                btn.closest('.actions').find('[data-action="reply"]').removeClass('is-active');
                addResponseUrl(btn.closest('.h-entry').data('entry'), response.location);
            } else {
                btn.addClass('is-danger');
            }
        });
    });

    $('.new-bookmark button').click(function() {
        var btn = $(this);
        var bookmark = btn.closest('.new-bookmark');

        if ('' === bookmark.find('textarea').val()) {
            return false;
        }

        var args = {
            'bookmark-of': btn.closest('.actions').data('url'),
            'content': bookmark.find('textarea').val()
        };

        var targets = [];

        btn.parent().find('[name^="syndicate_to"]').each(function() {
            if ($(this).is(':checked')) {
                targets.push($(this).val());
            }
        });

        if (targets.length !== 0) {
            $.extend(args, {
                'mp-syndicate-to': targets
            });
        }

        btn.addClass('is-loading');

        $.post('/micropub', addDestination(args), function(response) {
            btn.removeClass('is-loading');

            if (response.location) {
                btn.removeClass('is-danger');
                bookmark.find('textarea').val('');
                bookmark.hide();
                btn.closest('.actions').find('[data-action="bookmark"]').removeClass('is-active');
                addResponseUrl(btn.closest('.h-entry').data('entry'), response.location);
            } else {
                btn.addClass('is-danger');
            }
        });
    });

    $(document).keyup(function(e) {
        if ($(e.target).closest('input, textarea')[0]) {
            return;
        }

        // Capital A.
        if (e.shiftKey && e.keyCode === 65) {
            $('#mark-all-read').click();
        }
    });
    </script>

    <script src="/js/instantpage-5.1.0.js" type="module"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" href="/images/newspaper.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/fonts.css">

    <style>
    html,
    body {
        height: 100%;
    }

    body {
        background: #fff;
        color: #222;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    h1 {
        text-align: center;
    }

    body,
    button,
    input,
    select,
    textarea {
        font-family: sans-serif;
        line-height: 1.5;
    }

	#page {
		text-align: center;
	}

	.field:not(:first-child) {
		margin-top: 0.75rem;
	}

	[type="checkbox"] {
		position: relative;
		top: 0.125rem;
	}

    button,
    input,
    select,
    textarea {
        font-size: inherit;
    }

    button,
    [type="button"],
    [type="reset"],
    [type="submit"] {
        -webkit-appearance: button;
        display: inline-block;
        background: #f8f8f8;
        color: #686868;
        border: 0.0625rem solid #989898;
        border-radius: 0.25rem;
        padding: 0.25rem 0.375rem;
        box-sizing: border-box;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
    }

    button:hover,
    button:active,
    button:focus {
        color: #222;
    }
    </style>
</head>
<body>
    <div id="page">
        <h1>{{ __('Sign in to :app_name', ['app_name' => config('app.name')]) }}</h1>

        @if (session('auth_error'))
            <div class="notification is-danger">
                <strong>{{ session('auth_error') }}</strong>
                <p>{{ session('auth_error_description') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            {{ csrf_field() }}
            <input id="url" type="url" name="url" value="{{ session('auth_url') }}" placeholder="https://example.com" required autofocus>
            <button type="submit">Log In</button>

            <div class="field">
                <label><input id="remember" type="checkbox" name="remember" value="1"> {{ __('Create account (100% optional)') }}</label>
            </div>

        </form>
    </div>
</body>
</html>

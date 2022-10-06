@extends('layouts.app')

@section('content')
    <main id="content">
        @if (session()->has('success'))
            <div class="message success">
                {{ session('success') }}
            </div>
        @endif

        <section>
            <form action="{{ route('settings') }}" method="POST">
                @csrf
                <div class="field">
                    <h2><label for="custom_css">{{ __('Custom CSS') }}</label></h2>
                    <textarea id="custom_css" name="custom_css" rows="10">{{ $settings['custom_css'] ?? '' }}</textarea>
                </div>
                <div class="field">
                    <button type="submit">{{ __('Save Settings') }}</button>
                </div>
            </form>
        </section>
    </main>
@endsection

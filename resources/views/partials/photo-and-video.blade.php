@if (! empty($entry['video']) && is_array($entry['video']))
    <div class="videos">
        @foreach ($entry['video'] as $i => $video)
            <video src="{{ proxy_image($video) }}" class="u-video" controls {{ (isset($entry['photo'][$i]) ? 'poster="' . $entry['photo'][$i] . '"' : '') }}>
        @endforeach
    </div>
@elseif (! empty($entry['photo']))
    <div class="photos">
        @if (is_array($entry['photo']))
            @if (count($entry['photo']) > 1)
                <div class="multi-photo photos-<?= count($entry['photo']) ?>">
                    @foreach ($entry['photo'] as $photo)
                        <img src="{{ proxy_image($photo) }}" class="u-photo" loading="lazy">
                    @endforeach
                </div>
            @else
                <img src="{{ proxy_image($entry['photo'][0]) }}" class="u-photo" loading="lazy">
            @endif
        @else
            <img src="{{ proxy_image($entry['photo']) }}" class="u-photo" loading="lazy">
        @endif
    </div>
@endif

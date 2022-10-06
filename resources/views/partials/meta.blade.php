<footer class="entry-meta">
    @if (! empty($entry['category']) && is_array($entry['category']))
        <div class="categories">
            @foreach ($entry['category'] as $tag)
                @if (preg_match('~https?://~', $tag))
                    <span class="category"><a href="{{ $tag }}" class="u-category">{{ $tag }}</a></span>
                @else
                    <span class="category">#<span class="p-category">{{ trim($tag,'#') }}</span></span>
                @endif
            @endforeach
        </div>
    @endif
</footer>

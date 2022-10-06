<div class="actions" data-url="{{ $entry['url'] ?? '' }}">
	<ul class="action-buttons">
		@if (isset($entry['url']) && $responses_enabled)
			@if (supports_post_type('reply'))
				<li><button data-action="reply"><i class="fa fa-reply"></i></button></li>
			@endif

			@if (supports_post_type('repost'))
				<li><button data-action="repost"><i class="fa fa-retweet"></i></button></li>
			@endif

			@if (supports_post_type('like'))
				<li><button data-action="favorite"><i class="fa fa-star"></i></button></li>
			@endif

			@if (supports_post_type('bookmark'))
				<li><button data-action="bookmark"><i class="fa fa-bookmark"></i></button></li>
			@endif
		@endif

		<li>
			<button class="dropdown-trigger"><i class="fa fa-ellipsis-h"></i></button>

			<ul class="dropdown-menu">
				<li><button data-action="remove"><i class="fa fa-times-circle"></i> {{ __('Remove from Channel') }}</button></li>
				<li><button data-action="debug"><i class="fa fa-bug"></i> {{ __('Debug') }}</button></li>
				<li><button data-action="mark-unread"><i class="fa fa-eye"></i> {{ __('Mark Unread') }}</button></li>

				@if (! empty($entry['_source']))
				    <li><button data-action="unfollow">{{ __('Unfollow this Source') }}</button></li>
    				<li><button data-action="fetch-original">{{ __('Fetch original content') }}</button></li>
				@endif
			</ul>
		</li>
	</ul>

	@if (isset($entry['url']) && $responses_enabled)
		@if (session()->has('micropub'))
			@php $micropub = session('micropub') @endphp
		@endif

		<div class="new-reply">
			<textarea rows="5"></textarea>

			<div>
				<footer>
					@if (! empty($micropub['config']['syndicate-to']))
						@foreach ($micropub['config']['syndicate-to'] as $target)
							<label class="syndicate-to"><input type="checkbox" name="syndicate_to[]" value="{{ $target['uid'] }}"> {{ ($target['name'] ?: $target['uid']) }}</label>
						@endforeach
					@endif
				</footer>

				<button>{{ __('Reply') }}</button>
			</div>
		</div>

		<div class="new-bookmark">
			<textarea rows="5"></textarea>

			<div>
				<footer>
					@if (! empty($micropub['config']['syndicate-to']))
						@foreach ($micropub['config']['syndicate-to'] as $target)
							<label class="syndicate-to"><input type="checkbox" name="syndicate_to[]" value="{{ $target['uid'] }}"> {{ ($target['name'] ?: $target['uid']) }}</label>
						@endforeach
					@endif
				</footer>

				<button>{{ __('Bookmark') }}</button>
			</div>
		</div>
	@endif
</div>

<?php

use Illuminate\Support\Arr;
use zz\Html\HTMLMinify;

function minimize($html)
{
    return HTMLMinify::minify($html);
}

function display_date($format, $date)
{
    try {
        $d = new \DateTime($date);
        $d->setTimezone(new \DateTimeZone(config('app.timezone')));

        return time_elapsed($d->format($format));
    } catch (\Exception $e) {
        return date($format, strtotime($date));
    }
}

function time_elapsed($datetime, $full = false)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);

    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = [
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    ];

    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k.' '.$v.($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (! $full) {
        $string = array_slice($string, 0, 1);
    }

    return $string ? implode(', ', $string) /*. ' ago'*/ : 'just now';
}

function supports_post_type($type)
{
    if (! session()->has('micropub')) {
        return true;
    }

    $micropub = session('micropub');

    if (! isset($micropub['config']['post-types'])) {
        return true;
    }

    foreach ($micropub['config']['post-types'] as $t) {
        if (! is_array($t)) {
            return false;
        }

        if (! isset($t['type'])) {
            return false;
        }

        if ($type === $t['type']) {
            return true;
        }
    }

    return false;
}

function get_micropub_config($endpoint, $token)
{
    $response = micropub_get($endpoint, $token['access_token'], ['q' => 'config']);
    $config = [];

    if ($response['code'] === 200) {
        $c = json_decode($response['body'], true);

        if ($c) {
            $config = $c;
        }
    }

    session(['micropub' => [
        'endpoint' => $endpoint,
        'config' => $config,
    ]]);

    return session('micropub');
}

function micropub_get($endpoint, $token, $params = [])
{
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer '.$token,
    ];

    return http_client()->get(
        $endpoint.'?'.Arr::query($params),
        $headers
    );
}

function microsub_get($endpoint, $token, $action, $params = [])
{
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer '.$token,
    ];

    $params['action'] = $action;

    return http_client()->get(
        $endpoint.'?'.Arr::query($params),
        $headers
    );
}

function microsub_post($endpoint, $token, $action, $params = [])
{
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer '.$token,
    ];

    $params['action'] = $action;

    return http_client()->post(
        $endpoint,
        Arr::query($params),
        $headers
    );
}

function micropub_post_form($endpoint, $token, $params = [])
{
    $headers = [
        'Accept: application/json',
        'Authorization: Bearer '.$token,
    ];

    return http_client()->post(
        $endpoint,
        Arr::query($params),
        $headers
    );
}

function http_client()
{
    static $http;

    if (! isset($http)) {
        $http = new \p3k\HTTP('');
    }

    $http->set_timeout(20);

    return $http;
}

function logged_in()
{
    if (! session()->has('token')) {
        return false;
    }

    $token = session('token')['access_token'];

    if (isset($token)) {
        return true;
    }

    return false;
}

function proxy_image($url, string $size = ''): string
{
    // If `$url` is an array, process only its first element.
    $url = (array) $url;
    $url = reset($url);

    if (config('imageproxy.secret_key', '') === '') {
        return $url;
    }

    // $url = str_replace('%2F', '/', $url);
    // $url = str_replace('%2B', '+', $url);
    $url = str_replace('/./', '/', $url);
    $hash = hash_hmac('sha1', $url, config('imageproxy.secret_key', ''));

    $url = route('imageproxy', [$hash, ($size !== '' ? "$size/" : '').$url]).((substr($url, -1) === '/') ? '/' : ''); // Laravel's `route()` strips trailing slashes, so re-add a trailing slash if there was one.

    return $url;
}

function proxy_images($html, $url = '')
{
    if (preg_match_all('~<img (?:.*?)src="([^"]+?)"(?:[^>]*?)>~', $html, $matches)) {
        $appUrl = rtrim(config('app.url'), '/');

        for ($i = 0; $i < count($matches[0]); ++$i) {
            $match = $matches[1][$i];

            if (stripos($match, $appUrl) === 0) {
                // Exclude images of our own. Or processing images twice.
                continue;
            }

            $absolute = $match;

            // if (! empty($url)) {
            //     $absolute = make_absolute_url($match, $url);
            // }

            // $absolute = rawurldecode($absolute);
            // $absolute = preg_replace_callback('/[\ "<>`\\x{0080}-\\x{FFFF}]+/u', function ($match) {
            //     return rawurlencode($match[0]);
            // }, $absolute);

            if (strpos($matches[0][$i], 'loading="') === false) {
                // The original image tag does not yet contain a `loading` attribute.
                $lazy = preg_replace('~\s?/?>~', ' loading="lazy">', $matches[0][$i]);

                // Add in the lazily loaded version. (All occurrences, in case there's multiple.)
                $html = str_replace($matches[0][$i], $lazy, $html);
            }

            // Replace the original URL with the proxy one. (Again, everywhere.)
            $html = str_replace('src="'.$match, 'src="'.htmlspecialchars(proxy_image(str_replace('&amp;', '&', $absolute))), $html);
        }
    }

    // Replace duplicate image tags separated by an (ending) HTML tag with a
    // single occurrence of said tag. ("Fixes" duplicate images that are the
    // result of a JavaScript-based lazy loading technique in combination with
    // `noscript` tags.
    $html = preg_replace('~(<img[^>]+>)(<[^>]+>)\1~', '$1$2', $html);
    $html = preg_replace('~(<img[^>]+>)\s?\1~', '$1', $html); // Or by whitespace.

    // Strip empty blockquotes. (Don't ask.)
    $html = preg_replace('~<blockquote[^>]*></blockquote>~', '', $html);

    return $html;
}

/**
 * WordPress' `wpautop()` and helper functions, (nearly) literally lifted from
 * WordPress 4.9.8.
 */

/**
 * Replaces double line-breaks with paragraph elements.
 *
 * A group of regex replaces used to identify text formatted with newlines and
 * replace double line-breaks with HTML paragraph tags. The remaining line-breaks
 * after conversion become <<br />> tags, unless $br is set to '0' or 'false'.
 *
 * @since 0.71
 *
 * @param string $pee the text which has to be formatted
 * @param bool   $br  Optional. If set, this will convert all remaining line-breaks
 *                    after paragraphing. Default true.
 *
 * @return string text which has been converted into correct paragraph tags
 */
function wpautop($pee, $br = true)
{
    $pre_tags = [];

    if (trim($pee) === '') {
        return '';
    }

    // Just to make things a little easier, pad the end.
    $pee = $pee."\n";

    /*
     * Pre tags shouldn't be touched by autop.
     * Replace pre tags with placeholders and bring them back after autop.
     */
    if (strpos($pee, '<pre') !== false) {
        $pee_parts = explode('</pre>', $pee);
        $last_pee = array_pop($pee_parts);
        $pee = '';
        $i = 0;

        foreach ($pee_parts as $pee_part) {
            $start = strpos($pee_part, '<pre');

            // Malformed html?
            if ($start === false) {
                $pee .= $pee_part;
                continue;
            }

            $name = "<pre wp-pre-tag-$i></pre>";
            $pre_tags[$name] = substr($pee_part, $start).'</pre>';

            $pee .= substr($pee_part, 0, $start).$name;
            ++$i;
        }

        $pee .= $last_pee;
    }
    // Change multiple <br>s into two line breaks, which will turn into paragraphs.
    $pee = preg_replace('|<br\s*/?>\s*<br\s*/?>|', "\n\n", $pee);

    $allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';

    // Add a double line break above block-level opening tags.
    $pee = preg_replace('!(<'.$allblocks.'[\s/>])!', "\n\n$1", $pee);

    // Add a double line break below block-level closing tags.
    $pee = preg_replace('!(</'.$allblocks.'>)!', "$1\n\n", $pee);

    // Standardize newline characters to "\n".
    $pee = str_replace(["\r\n", "\r"], "\n", $pee);

    // Find newlines in all elements and add placeholders.
    $pee = wp_replace_in_html_tags($pee, ["\n" => ' <!-- wpnl --> ']);

    // Collapse line breaks before and after <option> elements so they don't get autop'd.
    if (strpos($pee, '<option') !== false) {
        $pee = preg_replace('|\s*<option|', '<option', $pee);
        $pee = preg_replace('|</option>\s*|', '</option>', $pee);
    }

    /*
     * Collapse line breaks inside <object> elements, before <param> and <embed> elements
     * so they don't get autop'd.
     */
    if (strpos($pee, '</object>') !== false) {
        $pee = preg_replace('|(<object[^>]*>)\s*|', '$1', $pee);
        $pee = preg_replace('|\s*</object>|', '</object>', $pee);
        $pee = preg_replace('%\s*(</?(?:param|embed)[^>]*>)\s*%', '$1', $pee);
    }

    /*
     * Collapse line breaks inside <audio> and <video> elements,
     * before and after <source> and <track> elements.
     */
    if (strpos($pee, '<source') !== false || strpos($pee, '<track') !== false) {
        $pee = preg_replace('%([<\[](?:audio|video)[^>\]]*[>\]])\s*%', '$1', $pee);
        $pee = preg_replace('%\s*([<\[]/(?:audio|video)[>\]])%', '$1', $pee);
        $pee = preg_replace('%\s*(<(?:source|track)[^>]*>)\s*%', '$1', $pee);
    }

    // Collapse line breaks before and after <figcaption> elements.
    if (strpos($pee, '<figcaption') !== false) {
        $pee = preg_replace('|\s*(<figcaption[^>]*>)|', '$1', $pee);
        $pee = preg_replace('|</figcaption>\s*|', '</figcaption>', $pee);
    }

    // Remove more than two contiguous line breaks.
    $pee = preg_replace("/\n\n+/", "\n\n", $pee);

    // Split up the contents into an array of strings, separated by double line breaks.
    $pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);

    // Reset $pee prior to rebuilding.
    $pee = '';

    // Rebuild the content as a string, wrapping every bit with a <p>.
    foreach ($pees as $tinkle) {
        $pee .= '<p>'.trim($tinkle, "\n")."</p>\n";
    }

    // Under certain strange conditions it could create a P of entirely whitespace.
    $pee = preg_replace('|<p>\s*</p>|', '', $pee);

    // Add a closing <p> inside <div>, <address>, or <form> tag if missing.
    $pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', '<p>$1</p></$2>', $pee);

    // If an opening or closing block element tag is wrapped in a <p>, unwrap it.
    $pee = preg_replace('!<p>\s*(</?'.$allblocks.'[^>]*>)\s*</p>!', '$1', $pee);

    // In some cases <li> may get wrapped in <p>, fix them.
    $pee = preg_replace('|<p>(<li.+?)</p>|', '$1', $pee);

    // If a <blockquote> is wrapped with a <p>, move it inside the <blockquote>.
    $pee = preg_replace('|<p><blockquote([^>]*)>|i', '<blockquote$1><p>', $pee);
    $pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);

    // If an opening or closing block element tag is preceded by an opening <p> tag, remove it.
    $pee = preg_replace('!<p>\s*(</?'.$allblocks.'[^>]*>)!', '$1', $pee);

    // If an opening or closing block element tag is followed by a closing <p> tag, remove it.
    $pee = preg_replace('!(</?'.$allblocks.'[^>]*>)\s*</p>!', '$1', $pee);

    // Optionally insert line breaks.
    if ($br) {
        // Replace newlines that shouldn't be touched with a placeholder.
        $pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee);

        // Normalize <br>
        $pee = str_replace(['<br>', '<br/>'], '<br />', $pee);

        // Replace any new line characters that aren't preceded by a <br /> with a <br />.
        $pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee);

        // Replace newline placeholders with newlines.
        $pee = str_replace('<WPPreserveNewline />', "\n", $pee);
    }

    // If a <br /> tag is after an opening or closing block tag, remove it.
    $pee = preg_replace('!(</?'.$allblocks.'[^>]*>)\s*<br />!', '$1', $pee);

    // If a <br /> tag is before a subset of opening or closing block tags, remove it.
    $pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
    $pee = preg_replace("|\n</p>$|", '</p>', $pee);

    // Replace placeholder <pre> tags with their original content.
    if (! empty($pre_tags)) {
        $pee = str_replace(array_keys($pre_tags), array_values($pre_tags), $pee);
    }

    // Restore newlines in all elements.
    if (strpos($pee, '<!-- wpnl -->') !== false) {
        $pee = str_replace([' <!-- wpnl --> ', '<!-- wpnl -->'], "\n", $pee);
    }

    return $pee;
}

/**
 * Replace characters or phrases within HTML elements only.
 *
 * @since 4.2.3
 *
 * @param string $haystack      the text which has to be formatted
 * @param array  $replace_pairs In the form array('from' => 'to', ...).
 *
 * @return string the formatted text
 */
function wp_replace_in_html_tags($haystack, $replace_pairs)
{
    // Find all elements.
    $textarr = wp_html_split($haystack);
    $changed = false;

    // Optimize when searching for one item.
    if (count($replace_pairs) === 1) {
        // Extract $needle and $replace.
        foreach ($replace_pairs as $needle => $replace); // phpcs:ignore Generic.ControlStructures.InlineControlStructure.NotAllowed

        // Loop through delimiters (elements) only.
        for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
            if (strpos($textarr[$i], $needle) !== false) {
                $textarr[$i] = str_replace($needle, $replace, $textarr[$i]);
                $changed = true;
            }
        }
    } else {
        // Extract all $needles.
        $needles = array_keys($replace_pairs);

        // Loop through delimiters (elements) only.
        for ($i = 1, $c = count($textarr); $i < $c; $i += 2) {
            foreach ($needles as $needle) {
                if (strpos($textarr[$i], $needle) !== false) {
                    $textarr[$i] = strtr($textarr[$i], $replace_pairs);
                    $changed = true;
                    // After one strtr() break out of the foreach loop and look at next element.
                    break;
                }
            }
        }
    }

    if ($changed) {
        $haystack = implode($textarr);
    }

    return $haystack;
}

/**
 * Separate HTML elements and comments from the text.
 *
 * @since 4.2.4
 *
 * @param string $input the text which has to be formatted
 *
 * @return array the formatted text
 */
function wp_html_split($input)
{
    return preg_split(get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE);
}

/**
 * Retrieve the regular expression for an HTML element.
 *
 * @since 4.4.0
 *
 * @staticvar string $regex
 *
 * @return string The regular expression
 */
function get_html_split_regex()
{
    static $regex;

    if (! isset($regex)) {
        $comments =
              '!'           // Start of comment, after the <.
            .'(?:'         // Unroll the loop: Consume everything until --> is found.
            .'-(?!->)' // Dash not followed by end of comment.
            .'[^\-]*+' // Consume non-dashes.
            .')*+'         // Loop possessively.
            .'(?:-->)?';   // End of comment. If not found, match all input.

        $cdata =
              '!\[CDATA\['  // Start of comment, after the <.
            .'[^\]]*+'     // Consume non-].
            .'(?:'         // Unroll the loop: Consume everything until ]]> is found.
            .'](?!]>)' // One ] not followed by end of comment.
            .'[^\]]*+' // Consume non-].
            .')*+'         // Loop possessively.
            .'(?:]]>)?';   // End of comment. If not found, match all input.

        $escaped =
              '(?='           // Is the element escaped?
            .'!--'
            .'|'
            .'!\[CDATA\['
            .')'
            .'(?(?=!-)'      // If yes, which type?
            .$comments
            .'|'
            .$cdata
            .')';

        $regex =
              '/('              // Capture the entire match.
            .'<'           // Find start of element.
            .'(?'          // Conditional expression follows.
            .$escaped  // Find end of escaped element.
            .'|'           // ... else ...
            .'[^>]*>?' // Find end of normal element.
            .')'
            .')/';
    }

    return $regex;
}

/**
 * Newline preservation help function for wpautop.
 *
 * @since 3.1.0
 *
 * @param array $matches preg_replace_callback matches array
 *
 * @return string
 */
function _autop_newline_preservation_helper($matches)
{
    return str_replace("\n", '<WPPreserveNewline />', $matches[0]);
}

/**
 * WordPress' `make_clickable()` and helper functions, (nearly) literally lifted from
 * WordPress 5.0.2. Calls to esc_url() were removed, so better trust that HTML!
 */

/**
 * Callback to convert URI match to HTML A element.
 *
 * This function was backported from 2.5.0 to 2.3.2. Regex callback for make_clickable().
 *
 * @since 2.3.2
 *
 * @param array $matches single Regex Match
 *
 * @return string HTML A element with URI address
 */
function _make_url_clickable_cb($matches)
{
    $url = $matches[2];

    if ($matches[3] == ')' && strpos($url, '(')) {
        // If the trailing character is a closing parethesis, and the URL has an opening parenthesis in it, add the closing parenthesis to the URL.
        // Then we can let the parenthesis balancer do its thing below.
        $url .= $matches[3];
        $suffix = '';
    } else {
        $suffix = $matches[3];
    }

    // Include parentheses in the URL only if paired
    while (substr_count($url, '(') < substr_count($url, ')')) {
        $suffix = strrchr($url, ')').$suffix;
        $url = substr($url, 0, strrpos($url, ')'));
    }

    if (empty($url)) {
        return $matches[0];
    }

    return $matches[1]."<a href=\"$url\" rel=\"nofollow\">$url</a>".$suffix;
}

/**
 * Callback to convert URL match to HTML A element.
 *
 * This function was backported from 2.5.0 to 2.3.2. Regex callback for make_clickable().
 *
 * @since 2.3.2
 *
 * @param array $matches single Regex Match
 *
 * @return string HTML A element with URL address
 */
function _make_web_ftp_clickable_cb($matches)
{
    $ret = '';
    $dest = $matches[2];
    $dest = 'http://'.$dest;

    // removed trailing [.,;:)] from URL
    if (in_array(substr($dest, -1), ['.', ',', ';', ':', ')']) === true) {
        $ret = substr($dest, -1);
        $dest = substr($dest, 0, strlen($dest) - 1);
    }

    if (empty($dest)) {
        return $matches[0];
    }

    return $matches[1]."<a href=\"$dest\" rel=\"nofollow\">$dest</a>$ret";
}

/**
 * Callback to convert email address match to HTML A element.
 *
 * This function was backported from 2.5.0 to 2.3.2. Regex callback for make_clickable().
 *
 * @since 2.3.2
 *
 * @param array $matches single Regex Match
 *
 * @return string HTML A element with email address
 */
function _make_email_clickable_cb($matches)
{
    $email = $matches[2].'@'.$matches[3];

    return $matches[1]."<a href=\"mailto:$email\">$email</a>";
}

/**
 * Convert plaintext URI to HTML links.
 *
 * Converts URI, www and ftp, and email addresses. Finishes by fixing links
 * within links.
 *
 * @since 0.71
 *
 * @param string $text content to convert URIs
 *
 * @return string content with converted URIs
 */
function make_clickable($text)
{
    $r = '';
    $textarr = preg_split('/(<[^<>]+>)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE); // split out HTML tags
    $nested_code_pre = 0; // Keep track of how many levels link is nested inside <pre> or <code>
    foreach ($textarr as $piece) {
        if (preg_match('|^<code[\s>]|i', $piece) || preg_match('|^<pre[\s>]|i', $piece) || preg_match('|^<script[\s>]|i', $piece) || preg_match('|^<style[\s>]|i', $piece)) {
            ++$nested_code_pre;
        } elseif ($nested_code_pre && (strtolower($piece) === '</code>' || strtolower($piece) === '</pre>' || strtolower($piece) === '</script>' || strtolower($piece) === '</style>')) {
            --$nested_code_pre;
        }

        if ($nested_code_pre || empty($piece) || ($piece[0] === '<' && ! preg_match('|^<\s*[\w]{1,20}+://|', $piece))) {
            $r .= $piece;
            continue;
        }

        // Long strings might contain expensive edge cases ...
        if (strlen($piece) > 10000) {
            // ... break it up
            foreach (_split_str_by_whitespace($piece, 2100) as $chunk) { // 2100: Extra room for scheme and leading and trailing paretheses
                if (strlen($chunk) > 2101) {
                    $r .= $chunk; // Too big, no whitespace: bail.
                } else {
                    $r .= make_clickable($chunk);
                }
            }
        } else {
            $ret = " $piece "; // Pad with whitespace to simplify the regexes

            $url_clickable = '~
                ([\\s(<.,;:!?])                                # 1: Leading whitespace, or punctuation
                (                                              # 2: URL
                    [\\w]{1,20}+://                            # Scheme and hier-part prefix
                    (?=\S{1,2000}\s)                           # Limit to URLs less than about 2000 characters long
                    [\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]*+     # Non-punctuation URL character
                    (?:                                        # Unroll the Loop: Only allow puctuation URL character if followed by a non-punctuation URL character
                        [\'.,;:!?)]                            # Punctuation URL character
                        [\\w\\x80-\\xff#%\\~/@\\[\\]*(+=&$-]++ # Non-punctuation URL character
                    )*
                )
                (\)?)                                          # 3: Trailing closing parenthesis (for parethesis balancing post processing)
            ~xS'; // The regex is a non-anchored pattern and does not have a single fixed starting character.
            // Tell PCRE to spend more time optimizing since, when used on a page load, it will probably be used several times.

            $ret = preg_replace_callback($url_clickable, '_make_url_clickable_cb', $ret);

            $ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]+)#is', '_make_web_ftp_clickable_cb', $ret);
            $ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);

            $ret = substr($ret, 1, -1); // Remove our whitespace padding.
            $r .= $ret;
        }
    }

    // Cleanup of accidental links within links
    return preg_replace('#(<a([ \r\n\t]+[^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i', '$1$3</a>', $r);
}

/**
 * Breaks a string into chunks by splitting at whitespace characters.
 * The length of each returned chunk is as close to the specified length goal as possible,
 * with the caveat that each chunk includes its trailing delimiter.
 * Chunks longer than the goal are guaranteed to not have any inner whitespace.
 *
 * Joining the returned chunks with empty delimiters reconstructs the input string losslessly.
 *
 * Input string must have no null characters (or eventual transformations on output chunks must not care about null characters)
 *
 *     _split_str_by_whitespace( "1234 67890 1234 67890a cd 1234   890 123456789 1234567890a    45678   1 3 5 7 90 ", 10 ) ==
 *     array (
 *         0 => '1234 67890 ',  // 11 characters: Perfect split.
 *         1 => '1234 ',        //  5 characters: '1234 67890a' was too long.
 *         2 => '67890a cd ',   // 10 characters: '67890a cd 1234' was too long.
 *         3 => '1234   890 ',  // 11 characters: Perfect split.
 *         4 => '123456789 ',   // 10 characters: '123456789 1234567890a' was too long.
 *         5 => '1234567890a ', // 12 characters: Too long, but no inner whitespace on which to split.
 *         6 => '   45678   ',  // 11 characters: Perfect split.
 *         7 => '1 3 5 7 90 ',  // 11 characters: End of $string.
 *     );
 *
 * @since 3.4.0
 * @access private
 *
 * @param string $string The string to split.
 * @param int    $goal   The desired chunk length.
 * @return array Numeric array of chunks.
 */
function _split_str_by_whitespace( $string, $goal ) {
    $chunks = array();

    $string_nullspace = strtr( $string, "\r\n\t\v\f ", "\000\000\000\000\000\000" );

    while ( $goal < strlen( $string_nullspace ) ) {
        $pos = strrpos( substr( $string_nullspace, 0, $goal + 1 ), "\000" );

        if ( false === $pos ) {
            $pos = strpos( $string_nullspace, "\000", $goal + 1 );
            if ( false === $pos ) {
                break;
            }
        }

        $chunks[]         = substr( $string, 0, $pos + 1 );
        $string           = substr( $string, $pos + 1 );
        $string_nullspace = substr( $string_nullspace, $pos + 1 );
    }

    if ( $string ) {
        $chunks[] = $string;
    }

    return $chunks;
}

function make_absolute_url($maybe_relative_path, $url)
{
    if (empty($url)) {
        return $maybe_relative_path;
    }

    $url_parts = parse_url($url);
    if (! $url_parts) {
        return $maybe_relative_path;
    }

    $relative_url_parts = parse_url($maybe_relative_path);
    if (! $relative_url_parts) {
        return $maybe_relative_path;
    }

    // Check for a scheme on the 'relative' URL.
    if (! empty($relative_url_parts['scheme'])) {
        return $maybe_relative_path;
    }

    $absolute_path = $url_parts['scheme'].'://';

    // Schemeless URLs will make it this far, so we check for a host in the relative URL
    // and convert it to a protocol-URL.
    if (isset($relative_url_parts['host'])) {
        $absolute_path .= $relative_url_parts['host'];
        if (isset($relative_url_parts['port'])) {
            $absolute_path .= ':'.$relative_url_parts['port'];
        }
    } else {
        $absolute_path .= $url_parts['host'];
        if (isset($url_parts['port'])) {
            $absolute_path .= ':'.$url_parts['port'];
        }
    }

    // Start off with the absolute URL path.
    $path = ! empty($url_parts['path']) ? $url_parts['path'] : '/';

    // If it's a root-relative path, then great.
    if (! empty($relative_url_parts['path']) && $relative_url_parts['path'][0] == '/') {
        $path = $relative_url_parts['path'];

    // Else it's a relative path.
    } elseif (! empty($relative_url_parts['path'])) {
        // Strip off any file components from the absolute path.
        $path = substr($path, 0, strrpos($path, '/') + 1);

        // Build the new path.
        $path .= $relative_url_parts['path'];

        // Strip all /path/../ out of the path.
        while (strpos($path, '../') > 1) {
            $path = preg_replace('![^/]+/\.\./!', '', $path);
        }

        // Strip any final leading ../ from the path.
        $path = preg_replace('!^/(\.\./)+!', '', $path);
    }

    // Add the query string.
    if (! empty($relative_url_parts['query'])) {
        $path .= '?'.$relative_url_parts['query'];
    }

    return $absolute_path.'/'.ltrim($path, '/');
}

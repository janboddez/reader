<?php

namespace janboddez\ImageProxy;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Imagick;

/**
 * Camo-like image proxy, in pure PHP.
 */
class ImageProxyController
{
    /**
     * Get requested image.
     *
     * @param string $hash
     * @param string $url
     *
     * @return Illuminate\Http\Response|null
     */
    public function proxy(Request $request, $hash, $url)
    {
        // Use `$_SERVER` rather than `$url` or any of Laravel's URL functions
        // to avoid any processing that could lead to checksum errors. To do:
        // actually drop these vars.
        $path = ltrim(str_replace('imageproxy', '', $_SERVER['REQUEST_URI']), '/');

        // Drop the query string, if any.
        // $path = strtok($path, '?');
        // $queryString = strtok('?');

        $path = explode('/', $path);
        // First item's the checksum.
        $hash = array_shift($path);

        if (isset($path[0]) && preg_match('~^\d+x\d+~', $path[0], $matches)) {
            // New first item's a set of dimensions.
            list($width, $height) = explode('x', array_shift($path));
        }

        // Whatever's left would have to be the URL.
        $url = implode('/', $path); // ~~Any trailing slashes would by now be removed.~~
        \Log::debug($url);

        // Re-add trailing slash if there was one. Note: if a `.htaccess` rule
        // or whatever strips trailing slashes before requests hit PHP, checksum
        // validation may still fail (but then the server's wrongly set up).
        //$url .= ((substr($_SERVER['REQUEST_URI'], -1) === '/') ? '/' : '');

        // if ($queryString) {
        //  // Reappend the query string, if there was one. Again, refrain from
        //  // using Laravel's helpers, which reorder params and such.
        //     $url .= '?'.$queryString;
        // }

        if (! $this->verifyUrl($url, $hash)) {
            \Log::debug('Checksum verification failed for '.$url);
            // Invalid URL, hash, or both.
            abort(400);
        }

        if ($request->headers->has('if-modified-since') || $request->headers->has('if-none-match')) {
            // It would seem the client already has the requested item. To do:
            // also return the other headers a client would typically expect.
            // (Seems to work, though.)
            return response('', 304);
        }

        $headers = array_filter([
            // 'Accept' => 'image/*', // Some sources unfortunately can't handle this.
            'Accept-Encoding' => $request->header('accept-encoding', null),
            'Connection' => 'close',
            'Content-Security-Policy' => "default-src 'none'; img-src data:; style-src 'unsafe-inline'",
            'User-Agent' => config('imageproxy.user_agent', $request->header('user-agent', null)),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'deny',
            'X-XSS-Protection' => '1; mode=block',
        ], function ($v) {
            return ! empty($v);
        });

        if (empty($width) || empty($height) || ! class_exists(Imagick::class)) {
            // Just passing the requested image along.
            try {
                $stream = fopen($url, 'r', false, $this->createStreamContext($headers));
            } catch (\Exception $e) {
                Log::debug("Failed to open the image at $url: ".$e->getMessage());
                abort(500);
            }

            // Newly received headers.
            list($status, $headers) = $this->getHttpHeaders($stream);

            $headers = array_combine(
                array_map('strtolower', array_keys($headers)),
                $headers
            );

            // Final response headers.
            $headers = array_filter($headers, function ($k) {
                return in_array($k, [
                        'content-type',
                        'etag',
                        'expires',
                        'last-modified',
                        'content-encoding',
                    ], true);
            }, ARRAY_FILTER_USE_KEY);

            if (empty($headers['content-type']) || ! preg_match('~^(image|video)/.+$~i', $headers['content-type'])) {
                \Log::debug('Not an image? ('.$url.')');
                abort(400);
            }

            if (! in_array($status, [200, 201, 202, 301, 302, 307], true)) {
                // Return an empty response.
                fclose($stream);

                return response('', $status, $headers);
            }

            $headers['Cache-Control'] = 'public, max-age=31536000';

            // Use a Laravel way of attaching headers to a response, rather than
            // multiple direct `header()` calls.
            return response()->stream(function () use ($stream) {
                fpassthru($stream);
                fclose($stream);
            }, 200, $headers);
        } else {
            // Resize, and cache, the image (like in `storage/app/imageproxy`).
            // Don't bother with file extensions.
            $file = 'imageproxy/'.base64_encode($url)."_{$width}x{$height}";

            if (Storage::exists($file)) {
                // Return existing image. Uses `fpassthru()` under the hood and
                // is thus not all that different from the code above.
                return Storage::response($file);
            }

            // Set up Imagick.
            $im = new Imagick();
            $im->setBackgroundColor(new \ImagickPixel('transparent'));

            try {
                // Read remote image.
                $handle = fopen(
                    $url,
                    'rb',
                    false,
                    $this->createStreamContext($headers)
                );
                $im->readImageFile($handle);
            } catch (\Exception $e) {
                // Something went wrong.
                Log::debug("Failed to read the image at $url: ".$e->getMessage());
                abort(500);
            }

            // Resize and crop.
            $im->cropThumbnailImage((int) $width, (int) $height);
            $im->setImagePage(0, 0, 0, 0);
            $im->setImageCompressionQuality(82);

            // Store to disk.
            Storage::put($file, $im->getImageBlob());

            // Return newly stored image.
            return Storage::response($file);
        }
    }

    /**
     * Verify URL.
     *
     * @param string $url
     * @param string $hash
     *
     * @return bool
     */
    protected function verifyUrl($url, $hash)
    {
        if (strpos($url, 'http') !== 0 || filter_var($url, FILTER_VALIDATE_URL) === false) {
            // Invalid (for this purpose) URL.
            return false;
        }

        if ($hash === hash_hmac('sha1', $url, config('imageproxy.secret_key', ''))) {
            return true;
        }

        // Try again swapping some encoded entities (which ones?) for their actual counterparts.
        if ($hash === hash_hmac('sha1', str_replace(['%5B', '%5D'], ['[', ']'], $url), config('imageproxy.secret_key', ''))) {
            return true;
        }

        // Either the URL's malformed, or the hash is invalid.
        return false;
    }

    /**
     * Create a stream context.
     *
     * @param array $headers
     *
     * @return resource
     */
    protected function createStreamContext($headers)
    {
        $headers = [
            'http' => [
                'header' => array_map(
                    function ($k, $v) {
                        return $k.': '.$v;
                    },
                    array_keys($headers),
                    $headers
                ),
                'follow_location' => true,
                'ignore_errors' => true, // "Allow" HTTP errors.
            ],
            'ssl' => [
                'verify_peer' => false, // Work around possible SSL errors.
                'verify_peer_name' => false,
            ],
        ];

        return stream_context_create($headers);
    }

    /**
     * Get HTTP headers.
     *
     * @param resource $stream
     *
     * @return array
     */
    protected function getHttpHeaders($stream)
    {
        $metadata = stream_get_meta_data($stream);

        $status = $metadata['wrapper_data'][0];
        $status = (int) explode(' ', $status)[1];

        $headers = [];

        foreach ($metadata['wrapper_data'] as $line) {
            $row = explode(': ', $line);

            if (count($row) > 1) {
                $headers[array_shift($row)] = implode(': ', $row);
            }
        }

        return [$status, $headers];
    }

    protected function getMimeTypeFromStream($stream)
    {
    }
}

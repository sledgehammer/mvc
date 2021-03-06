<?php

namespace Sledgehammer\Mvc\Document;

use Exception;
use Sledgehammer\Core\Base;
use Sledgehammer\Mvc\Component;
use Sledgehammer\Mvc\Component\HttpError;

/**
 * Serve the http headers and contents of a remote url as a local file.
 * Bypasses CORS restrictions on remote api's.
 */
class Proxy extends Base implements Component
{
    private $error = false;
    private $headers = [];
    private $contents;

    public function __construct($url)
    {
        if (substr($url, 0, 7) !== 'http://' && substr($url, 0, 8) !== 'https://') {
            throw new Exception('Not a valid url');
        }
        $this->contents = file_get_contents($url);
        if ($this->contents !== false) {
            // Parse the headers that came with the file_get_contents() call.
            foreach ($http_response_header as $header) {
                if (preg_match('/^HTTP\/1.[01](.+)$/', $header, $match)) {
                    $this->headers = []; // drop (redirect) headers
                    $this->headers['Status'] = ltrim($match[1]);
                    continue;
                }
                $pos = strpos($header, ':');
                $name = substr($header, 0, $pos);
                $this->headers[$name] = ltrim(substr($header, $pos + 1));
            }
            if (count($this->headers) === 0) {
                throw new Exception('No HTTP headers');
            }
        } else { // An error occurred
            if (isset($http_response_header)) {
                // Forward the http error status
                foreach ($http_response_header as $header) {
                    if (preg_match('/^HTTP\/1.[01](.+)$/', $header, $match)) {
                        $status = intval($match[1]);
                        if ($status >= 400) { // Skip 30x redirects
                            $this->error = new HttpError($status);
                        }
                    }
                }
            }
            if ($this->error === false) {
                $this->error = new HttpError(500);
            }
        }
    }

    public function render()
    {
        if ($this->error) {
            $this->error->render();

            return;
        }
        echo $this->contents;
    }

    public function getHeaders()
    {
        if ($this->error) {
            return $this->error->getHeaders();
        }

        return [
            'http' => $this->headers,
        ];
    }

    public function isDocument()
    {
        return $this->error === false;
    }
}

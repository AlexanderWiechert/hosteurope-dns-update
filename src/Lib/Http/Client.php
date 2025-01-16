<?php

namespace Lib\Http;

use Lib\Http\Cache\Cache;
use Lib\Http\Model\Response;

class Client
{

    /** @var string */
    protected $url;

    /** @var Cache */
    protected $cacheHandler;

    /** @var resource */
    protected $resource;

    /** @var array */
    protected $header;

    /** @var Response */
    protected $response;

    /** @var boolean */
    protected $isDebug;

    /**
     * Client constructor.
     * @param Response|null $response
     * @param boolean $debug
     */
    public function __construct(Response $response = null, $debug = false)
    {
        $this->response = $response === null ? new Response() : $response;
        $this->isDebug  = $debug;
    }

    /**
     * @param string  $url
     * @return Response|null|string
     * @throws \Exception
     */
    public function send($url)
    {
        $this->url = $url;

        if ($this->cacheHandler instanceof Cache && $this->cacheHandler->getActive()) {
            $this->cacheHandler->validateDir();
            return new Response($this->cacheHandler->get($this->url));
        }

        if ($this->isDebug) {
            print "CACHE MISS: " . explode('&kdnummer', $url)[0] . "\n";
        }

        $this->resource = fopen($this->url, 'r');
        $this->response = new Response(stream_get_contents($this->resource));

        if (!$this->response->getHeader()) {
            $this->response->setHeader($this->parseHeaders(stream_get_meta_data($this->resource)['wrapper_data']));
        }

        return $this->getResponse();
    }

    /**
     * @param Cache $cacheHandler
     */
    public function setCacheHandler($cacheHandler)
    {
        $this->cacheHandler = $cacheHandler;
    }

    /**
     * @return Cache
     */
    public function getCacheHandler()
    {
        return $this->cacheHandler;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $headers
     * @return array
     */
    protected function parseHeaders($headers)
    {
        $result = [];

        foreach ($headers as $header)
        {

            if (strpos($header, 'HTTP/') !== false) {
                $result['protocol'] = explode(' ', $header)[0];
                $result['code']     = (int)explode(' ', $header)[1];
                $result['status']   = (string)explode(' ', $header)[2];
            }

            if (strpos($header, 'Content-Type') !== false){
                $result['contentType'] = str_replace('Content-Type:', '', $header);
            }

            if (strpos($header, 'Cache-Control') === 0) {
                $result['cacheControl'] = str_replace('Cache-Control:', '', $header);
            }

            if (strpos($header, 'Set-Cookie') === 0) {
                $result['cookie'] = str_replace('Set-Cookie:', '', $header);
            }

        }

        return $result;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->resource !== NULL) {
            fclose($this->resource);
        }
    }

}
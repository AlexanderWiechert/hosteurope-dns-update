<?php

namespace Lib\Http\Model;

class Response
{

    /** @var array */
    protected $header;

    /** @var string */
    protected $content;

    public function __construct($content = '')
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param array $header
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

}
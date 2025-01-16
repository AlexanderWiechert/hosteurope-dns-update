<?php

namespace App\Model;

/**
 * Class Record
 */
class Record
{

    /** @var string */
    protected $domain = '';

    /** @var string */
    protected $host = '';

    /** @var string */
    protected $recordType = 'A';

    /** @var string */
    protected $pointer = '';

    /** @var string */
    protected $additional = '';

    /** @var boolean */
    protected $forceUpdate = false;

    /** @var boolean */
    protected $delete = false;

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $recordType
     */
    public function setRecordType($recordType)
    {
        $this->recordType = $recordType;
    }

    /**
     * @return string
     */
    public function getRecordType()
    {
        return $this->recordType;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /** @param string $pointer */
    public function setPointer($pointer)
    {
        $this->pointer = $pointer;
    }

    /** @return string */
    public function getPointer()
    {
        return $this->pointer;
    }

    /**
     * @param string $additional
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return string
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @param boolean $force
     */
    public function setForceUpdate($force)
    {
        $this->forceUpdate = $force;
    }

    /**
     * @return boolean
     */
    public function getForceUpdate()
    {
        return $this->forceUpdate;
    }

    /**
     * @param boolean $delete
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;
    }

    /** @return boolean */
    public function getDelete()
    {
        return $this->delete;
    }
}
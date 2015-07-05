<?php
namespace Soliant\SimpleFM\Result;

abstract class AbstractResult
{
    protected $debugUrl;
    protected $errorCode;
    protected $errorMessage;
    protected $errorType;

    public function __construct($debugUrl, $errorCode, $errorMessage, $errorType)
    {
        // debugUrl is formatted in AbstractLoader
        $this->debugUrl = $debugUrl;
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
        $this->errorType = $errorType;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        $array['url'] = $this->getDebugUrl();
        $array['errorCode'] = $this->getErrorCode();
        $array['errorMessage'] = $this->getErrorMessage();
        $array['errorType'] = $this->getErrorType();
        return $array;
    }

    /**
     * @return mixed
     */
    public function getDebugUrl()
    {
        return $this->debugUrl;
    }

    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     */
    public function getErrorType()
    {
        return $this->errorType;
    }
}

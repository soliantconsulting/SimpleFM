<?php
namespace Soliant\SimpleFM\Result;

abstract class AbstractResult
{
    protected $url;
    protected $error;
    protected $errorText;
    protected $errorType;

    public function __construct($url, $error, $errorText, $errorType)
    {
        $this->url = $url;
        $this->error = $error;
        $this->errorText = $errorText;
        $this->errorType = $errorType;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = [];
        $array['url'] = $this->getUrl();
        $array['errorText'] = $this->getErrorText();
        $array['errorType'] = $this->getErrorType();
        return $array;
    }

    public function toArrayLc()
    {
        $arrayLc = [];
        $array = $this->toArray();
        foreach ($array as $key => $value) {
            $arrayLc[strtolower($key)] = $value;
        }
        return $arrayLc;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getErrorText()
    {
        return $this->errorText;
    }

    /**
     * @return mixed
     */
    public function getErrorType()
    {
        return $this->errorType;
    }
}

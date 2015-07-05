<?php
namespace Soliant\SimpleFM\Result;

class FmResultSet extends AbstractResult
{
    protected $debugUrl;
    protected $errorCode;
    protected $errorMessage;
    protected $errorType;
    protected $count;
    protected $fetchSize;
    protected $rows;

    public function __construct(
        $debugUrl,
        $errorCode,
        $errorMessage,
        $errorType,
        $count = null,
        $fetchSize = null,
        array $rows = []
    ) {
        parent::__construct($debugUrl, $errorCode, $errorMessage, $errorType);
        $this->count = $count;
        $this->fetchSize = $fetchSize;
        $this->rows = $rows;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return mixed
     */
    public function getFetchSize()
    {
        return $this->fetchSize;
    }

    /**
     * @return array
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['count'] = (int)$this->getCount();
        $array['fetchSize'] = (int)$this->getFetchSize();
        $array['rows'] = $this->getRows();
        return $array;
    }
}

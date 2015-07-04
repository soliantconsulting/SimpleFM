<?php
namespace Soliant\SimpleFM\Result;

class FmLayout extends AbstractResult
{
    protected $product;
    protected $layout;
    protected $valueLists;

    public function __construct(
        $debugUrl,
        $errorCode,
        $errorMessage,
        $errorType,
        array $product = [],
        array $layout = [],
        array $valueLists = []
    ) {
        parent::__construct($debugUrl, $errorCode, $errorMessage, $errorType);
        $this->product = $product;
        $this->layout = $layout;
        $this->valueLists = $valueLists;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return mixed
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return mixed
     */
    public function getValueLists()
    {
        return $this->valueLists;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();
        $array['product'] = $this->getProduct();
        $array['layout'] = $this->getLayout();
        $array['valueLists'] = $this->getValueLists();
        return $array;
    }
}

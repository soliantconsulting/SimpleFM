<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Exception;

class InvalidConnectionParametersException extends RuntimeException implements ExceptionInterface
{

    /**
     * @var int
     */
    protected $parameters;

    /**
     * @param string $message
     * @param int $parameters
     */
    public function __construct($message, $parameters)
    {
        parent::__construct($message);
        $this->parameters = $parameters;
    }

}

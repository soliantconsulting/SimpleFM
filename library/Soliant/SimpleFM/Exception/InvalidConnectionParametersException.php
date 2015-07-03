<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Exception;

class InvalidConnectionParametersException extends RuntimeException implements ExceptionInterface
{

    /**
     * @var mixed
     */
    protected $hostConnection;

    /**
     * @return mixed
     */
    public function getHostConnection()
    {
        return $this->hostConnection;
    }

    /**
     * @param string $message
     * @param int $hostConnection
     */
    public function __construct($message, $hostConnection)
    {
        parent::__construct($message);
        $this->hostConnection = $hostConnection;
    }
}

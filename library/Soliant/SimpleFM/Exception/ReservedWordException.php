<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\Exception;

class ReservedWordException extends RuntimeException implements ExceptionInterface
{

    /**
     * @var string
     */
    protected $reservedWord;

    /**
     * @param string $message
     * @param string $reservedWord
     */
    public function __construct($message, $reservedWord)
    {
        parent::__construct($message);
        $this->reservedWord = $reservedWord;
    }

}

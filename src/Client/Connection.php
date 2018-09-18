<?php
declare(strict_types = 1);

namespace Soliant\SimpleFM\Client;

use DateTimeZone;

final class Connection
{
    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $database;

    /**
     * @var DateTimeZone
     */
    private $timeZone;

    public function __construct(
        string $baseUri,
        string $username,
        string $password,
        string $database,
        ?DateTimeZone $timeZone = null
    ) {
        $this->baseUri = rtrim($baseUri, '/');
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->timeZone = $timeZone ?: new DateTimeZone(date_default_timezone_get());
    }

    public function getBaseUri() : string
    {
        return $this->baseUri;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function getPassword() : string
    {
        return $this->password;
    }

    public function getDatabase() : string
    {
        return $this->database;
    }

    public function getTimeZone() : DateTimeZone
    {
        return $this->timeZone;
    }
}

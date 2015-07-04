<?php
namespace SoliantTest\SimpleFM;

use Soliant\SimpleFM\HostConnection;
use Soliant\SimpleFM\Exception\InvalidConnectionParametersException;
use Soliant\SimpleFM\Exception\ReservedWordException;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{
    private $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->connection = new HostConnection(
            'hostName',
            'dbName',
            'userName',
            'password'
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Soliant\SimpleFM\Exception\InvalidConnectionParametersException::__construct
     * @covers Soliant\SimpleFM\Exception\InvalidConnectionParametersException::getHostConnection
     * @covers Soliant\SimpleFM\Exception\ReservedWordException::__construct
     * @covers Soliant\SimpleFM\Exception\ReservedWordException::getReservedWord
     */
    public function testCustomExceptions()
    {
        $exception = new InvalidConnectionParametersException('error message', $this->connection);
        $this->assertInstanceOf(HostConnection::class, $exception->getHostConnection());

        $exception = new ReservedWordException('error message', 'reservedWord');
        $this->assertEquals('reservedWord', $exception->getReservedWord());
    }
}

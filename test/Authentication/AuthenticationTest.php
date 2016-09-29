<?php
declare(strict_types = 1);

namespace SoliantTest\SimpleFM\Authentication;

use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Soliant\SimpleFM\Authentication\Authenticator;
use Soliant\SimpleFM\Authentication\BlockCipherIdentityHandler;
use Soliant\SimpleFM\Authentication\Exception\InvalidResultException;
use Soliant\SimpleFM\Authentication\Identity;
use Soliant\SimpleFM\Authentication\Result;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClientInterface;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\Exception\InvalidResponseException;
use Zend\Crypt\BlockCipher;

final class AuthenticationTest extends TestCase
{
    public function testAuthenticatorSuccess()
    {
        $username = 'foo';
        $password = 'bar';
        $identity = $this->createIdentity($username, $password);
        $command = $this->createCommand('foo');
        $resultSetClientDouble = $this->createResultSetClientProphecy($command, [$username, $password]);
        $resultSetClientDouble->execute(
            $command->withCredentials($username, $password)
        )->willReturn([[]]);
        $authenticator = $this->createAuthenticator($username, $password, $resultSetClientDouble->reveal());

        $this->assertEquals($identity, $authenticator->authenticate('foo', 'bar')->getIdentity());
    }

    public function testAuthenticatorGenericFail()
    {
        $username = 'foo';
        $password = 'bar';
        $identity = $this->createIdentity($username, $password);
        $command = $this->createCommand('bad');

        $resultSetClientDouble = $this->createResultSetClientProphecy($command, ['bad', $password, $username]);
        $resultSetClientDouble->execute(
            $command->withCredentials('bad', 'bar')
        )->willThrow(InvalidResponseException::class);

        $this->expectException(InvalidResponseException::class);
        $authenticator = $this->createAuthenticator('bad', $password, $resultSetClientDouble->reveal());

        $this->assertNotEquals($identity, $authenticator->authenticate('bad', 'bar')->getIdentity());
    }

    public function testAuthenticator401NotFound()
    {
        $username = 'foo';
        $password = 'bar';
        $identity = $this->createIdentity($username, $password);
        $command = $this->createCommand('bad');
        $resultSetClientDouble = $this->createResultSetClientProphecy($command, ['bad', $password, $username]);

        $response401 = $this->prophesize(ResponseInterface::class);
        $response401->getStatusCode()->willReturn(401);
        $response401->getReasonPhrase()->willReturn('Not Found');

        $resultSetClientDouble->execute(
            $command->withCredentials('bad', 'bar')
        )->willThrow(InvalidResponseException::fromUnsuccessfulResponse($response401->reveal()));

        $authenticator = $this->createAuthenticator('bad', $password, $resultSetClientDouble->reveal());

        $this->assertFalse($authenticator->authenticate('bad', 'bar')->isSuccess());
    }

    public function testAuthenticatorEmptyResultFail()
    {
        $username = 'foo';
        $password = 'bar';
        $identity = $this->createIdentity($username, $password);
        $command = $this->createCommand('bad');

        $resultSetClientDouble = $this->createResultSetClientProphecy($command, ['bad', $password, $username]);
        $resultSetClientDouble->execute(
            $command->withCredentials('bad', 'bar')
        )->willReturn([]);

        $this->expectException(InvalidResultException::class);
        $authenticator = $this->createAuthenticator('bad', $password, $resultSetClientDouble->reveal());

        $this->assertNotEquals($identity, $authenticator->authenticate('bad', 'bar')->getIdentity());
    }

    public function testIdentity()
    {
        $blockCipher = $this->prophesize(BlockCipher::class);
        $blockCipher->encrypt('bar')->willReturn('encryptedPassword');
        $blockCipher->decrypt('encryptedPassword')->willReturn('bar');
        $blockCipherIdentityHandler = new BlockCipherIdentityHandler($blockCipher->reveal());
        $identity = $blockCipherIdentityHandler->createIdentity('foo', 'bar');

        $this->assertEquals('encryptedPassword', $identity->getEncryptedPassword());
        $this->assertEquals('bar', $blockCipherIdentityHandler->decryptPassword($identity));
    }

    public function testResult()
    {
        $identity = new Identity('foo', 'bar');
        $result = Result::fromIdentity($identity);
        $falseResult = Result::fromInvalidCredentials();

        $this->assertTrue($result->isSuccess());
        $this->assertEquals($identity, $result->getIdentity());
        $this->assertFalse($falseResult->isSuccess());
    }

    private function createResultSetClientProphecy(Command $command, array $quoteStrings) : ObjectProphecy
    {
        $resultSetClient = $this->prophesize(ResultSetClientInterface::class);
        foreach ($quoteStrings as $string) {
            $resultSetClient->quoteString($string)->willReturn($string);
        }
        return $resultSetClient;
    }

    private function createAuthenticator(
        string $username,
        string $password,
        ResultSetClientInterface $resultSetClient
    ) : Authenticator {
        return new Authenticator(
            $resultSetClient,
            $this->createBlockCipherIdentityHandler($password),
            'layout',
            'account'
        );
    }

    private function createIdentity(string $username, string $password) : Identity
    {
        return $this->createBlockCipherIdentityHandler($password)->createIdentity($username, $password);
    }

    private function createBlockCipherIdentityHandler(string $password) : BlockCipherIdentityHandler
    {
        $blockCipher = $this->prophesize(BlockCipher::class);
        $blockCipher->encrypt($password)->willReturn('encryptedPassword');
        $blockCipher->decrypt('encryptedPassword')->willReturn($password);
        return new BlockCipherIdentityHandler($blockCipher->reveal());
    }

    private function createCommand(string $username) : Command
    {
        return new Command('layout', ['account' => '==' . $username, '-find' => null]);
    }
}

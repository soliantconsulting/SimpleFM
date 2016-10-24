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
use Soliant\SimpleFM\Authentication\IdentityHandlerInterface;
use Soliant\SimpleFM\Client\ResultSet\ResultSetClientInterface;
use Soliant\SimpleFM\Connection\Command;
use Soliant\SimpleFM\Connection\Exception\InvalidResponseException;
use Zend\Crypt\BlockCipher;

final class AuthenticationTest extends TestCase
{
    public function testAuthenticatorSuccess()
    {
        $identity = new Identity('foo', 'bar');
        $resultSetClient = $this->createResultSetClientProphecy();
        $resultSetClient->execute(
            $this->createCommand('foo')->withIdentity($identity)
        )->willReturn([[]]);

        $authenticator = $this->createAuthenticator($resultSetClient->reveal(), $identity);

        $this->assertSame($identity, $authenticator->authenticate('foo', 'bar')->getIdentity());
    }

    public function testAuthenticatorGenericFail()
    {
        $identity = new Identity('foo', 'bar');

        $resultSetClient = $this->createResultSetClientProphecy();
        $resultSetClient->execute(
            $this->createCommand('foo')->withIdentity($identity)
        )->willThrow(InvalidResponseException::class);

        $authenticator = $this->createAuthenticator($resultSetClient->reveal(), $identity);

        $this->expectException(InvalidResponseException::class);
        $authenticator->authenticate('foo', 'bar')->getIdentity();
    }

    public function testAuthenticator401NotFound()
    {
        $identity = new Identity('foo', 'bar');

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(401);
        $response->getReasonPhrase()->willReturn('Not Found');

        $resultSetClient = $this->createResultSetClientProphecy();
        $resultSetClient->execute(
            $this->createCommand('foo')->withIdentity($identity)
        )->willThrow(InvalidResponseException::fromUnsuccessfulResponse($response->reveal()));

        $authenticator = $this->createAuthenticator($resultSetClient->reveal(), $identity);
        $this->assertFalse($authenticator->authenticate('foo', 'bar')->isSuccess());
    }

    public function testAuthenticatorEmptyResultFail()
    {
        $identity = new Identity('foo', 'bar');
        $resultSetClient = $this->createResultSetClientProphecy();
        $resultSetClient->execute(
            $this->createCommand('foo')->withIdentity($identity)
        )->willReturn([]);

        $authenticator = $this->createAuthenticator($resultSetClient->reveal(), $identity);

        $this->expectException(InvalidResultException::class);
        $authenticator->authenticate('foo', 'bar')->getIdentity();
    }

    private function createResultSetClientProphecy() : ObjectProphecy
    {
        $resultSetClient = $this->prophesize(ResultSetClientInterface::class);
        $resultSetClient->quoteString(\Prophecy\Argument::any())->will(function (array $parameters) : string {
            return $parameters[0];
        });

        return $resultSetClient;
    }

    private function createAuthenticator(ResultSetClientInterface $resultSetClient, Identity $identity) : Authenticator
    {
        $identityHandler = $this->prophesize(IdentityHandlerInterface::class);
        $identityHandler->createIdentity('foo', 'bar')->willReturn($identity);

        return new Authenticator(
            $resultSetClient,
            $identityHandler->reveal(),
            'layout',
            'account'
        );
    }

    private function createCommand(string $username) : Command
    {
        return new Command('layout', ['account' => '==' . $username, '-find' => null]);
    }
}

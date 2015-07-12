<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Authentication\Adapter;

use Soliant\SimpleFM\ZF2\Authentication\Mapper\Identity;
use Soliant\SimpleFM\Adapter;
use Soliant\SimpleFM\Result\FmResultSet;
use Zend\Authentication\Result;

/**
 * Class SimpleFM
 * @package Soliant\SimpleFM\ZF2\Authentication\Adapter
 */
class SimpleFM implements \Zend\Authentication\Adapter\AdapterInterface
{

    /**
     * @var array
     */
    protected $credentials;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var boolean
     */
    protected $rememberMe = false;

    /**
     * Adapter to be used for login validation
     *
     * @var string
     */
    protected $simpleFmValidateAdapter;

    /**
     * App encryption key used to encrypt the password if persisted in the session identity
     *
     * @var string
     */
    protected $encryptionKey;

    /**
     *  App username from the app config
     *
     * @var string
     */
    protected $appUsername;

    /**
     * Admin password from the app config
     *
     * @var string
     */
    protected $appPassword;

    /**
     * The FileMaker identity layout name for the authenticate request
     *
     * @var string
     */
    protected $identityLayout;

    /**
     * The FileMaker identity username field name for the authenticate request
     *
     * @var string
     */
    protected $accountNameField;

    /**
     * @param  array $config Configuration settings:
     *    'validateSimpleFmAdapter' => Soliant\SimpleFM\Adapter
     *    'encryptionKey'           => string Example: '56cb36c21eb9a29c1317092b973a5f9cba393a367de783af45a2799f7302c',
     *    'appUsername'             => string Example: 'webSystem'
     *    'appPassword'             => string Example: '317akx1gr43m4pd'
     *    'identityLayout'          => string Example: 'gateway_User'
     *    'accountNameField'        => string Example: 'AccountName'
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public function __construct(array $config, Adapter $simpleFmValidateAdapter)
    {
        $this->simpleFmValidateAdapter = $simpleFmValidateAdapter;

        if (empty($config['encryptionKey'])) {
            // If encryptionKey is not set, Identity will not keep the password
            $this->encryptionKey = null;
        } else {
            // If encryptionKey is set, Identity will encrypt password
            $this->encryptionKey = $config['encryptionKey'];
        }

        if (!empty($config['appUsername'])) {
            $this->setUsername($config['appUsername']);
        }

        if (!empty($config['appPassword'])) {
            $this->setPassword($config['appPassword']);
        }

        if (empty($config['identityLayout'])) {
            throw new Exception\InvalidArgumentException('Config key \'identityLayout\' is required');
        }
        $this->identityLayout = $config['identityLayout'];

        if (empty($config['accountNameField'])) {
            throw new Exception\InvalidArgumentException('Config key \'accountNameField\' is required');
        }
        $this->accountNameField = $config['accountNameField'];
    }

    /**
     * @var string $username
     * @return SimpleFM
     */
    public function setUsername($username)
    {
        $this->username = $username;
        $this->credentials['username'] = $username;
        return $this;
    }

    /**
     * @var string $password
     * @return SimpleFM
     */
    public function setPassword($password)
    {
        $this->password = $password;
        $this->credentials['password'] = $password;
        return $this;
    }

    /**
     * @var boolean $rememberMe
     * @return SimpleFM
     */
    public function setRememberMe($rememberMe)
    {
        $this->rememberMe = (boolean)$rememberMe;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNameField()
    {
        return $this->accountNameField;
    }

    /**
     * @return \Zend\Authentication\Result
     */
    public function authenticate()
    {
        $this->simpleFmValidateAdapter->setLayoutName($this->identityLayout);
        $this->simpleFmValidateAdapter
            ->getHostConnection()
            ->setUserName($this->credentials['username'])
            ->setPassword($this->credentials['password']);

        $command = [
            $this->accountNameField => "==" . self::escapeStringForFileMakerSearch($this->username),
            '-find' => null,
        ];
        $this->simpleFmValidateAdapter->setCommandArray($command);

        $sfmResult = $this->simpleFmValidateAdapter->execute();

        return $this->handleAuthenticateResult($sfmResult);
    }

    /**
     * @param FmResultSet $sfmResult
     * @return Result
     */
    protected function handleAuthenticateResult(FmResultSet $sfmResult)
    {
        if ($sfmResult->getErrorType() === 'FileMaker') {
            return $this->handleErrorTypeFileMaker($sfmResult);
        }

        if ($sfmResult->getErrorType() === 'HTTP') {
            return $this->handleErrorTypeHttp($sfmResult);
        }

        if ($sfmResult->getErrorType() === 'PHP') {
            return $this->handleErrorTypePhp($sfmResult);
        }

        return $this->handleUnexpectedError($sfmResult);
    }

    /**
     * @param FmResultSet $sfmResult
     * @return Result
     */
    protected function handleErrorTypeFileMaker(FmResultSet $sfmResult)
    {
        if ($sfmResult->getErrorCode() == 0) {
            $identity = new Identity(
                $this->username,
                $this->password,
                $this->rememberMe,
                $this->encryptionKey,
                $sfmResult->getRows()[0]
            );
            $identity->setIsLoggedIn(true);
            return new Result(
                Result::SUCCESS,
                $identity
            );
        }
        return $this->handleUnexpectedError($sfmResult);
    }

    /**
     * @param FmResultSet $sfmResult
     * @return Result
     */
    protected function handleErrorTypeHttp(FmResultSet $sfmResult)
    {
        // Return null identity plus reason as message array for HTTP 401
        if ($sfmResult->getErrorCode() == 401) {
            $identity = null;
            return new Result(
                Result::FAILURE,
                $identity,
                [
                    'reason' => 'Username and/or password not valid',
                    'sfm_auth_response' => $sfmResult
                ]
            );
        }
        return $this->handleUnexpectedError($sfmResult);
    }

    /**
     * @param FmResultSet $sfmResult
     * @return Result
     */
    protected function handleErrorTypePhp(FmResultSet $sfmResult)
    {
        // there most likely was a error connecting to the host
        $identity = null;
        return new Result(
            Result::FAILURE,
            $identity,
            [
                'reason' => 'There was a system error trying to make the request. Please try again later.',
                'sfm_auth_response' => $sfmResult
            ]
        );
    }

    /**
     * @param FmResultSet $sfmResult
     * @return Result
     */
    protected function handleUnexpectedError(FmResultSet $sfmResult)
    {
        // Return empty identity plus reason as message array for every other result status
        $identity = null;
        return new Result(
            Result::FAILURE,
            $identity,
            [
                'reason' =>
                    $sfmResult->getErrorType() . ' error ' .
                    $sfmResult->getErrorCode() . ': ' .
                    $sfmResult->getErrorMessage(),
                'sfm_auth_response' => $sfmResult
            ]
        );
    }

    /**
     * @param $string
     * @return string
     */
    public static function escapeStringForFileMakerSearch($string)
    {
        return str_replace('@', '\@', $string);
    }
}

<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Authentication\Adapter;


use Soliant\SimpleFM\ZF2\Authentication\Mapper\Identity;
use Soliant\SimpleFM\Adapter;
use Zend\Authentication\Result;

class Auth implements \Zend\Authentication\Adapter\AdapterInterface
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
     * URI of user login form
     *
     * @var string
     */
    protected $loginUrl;

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
     * Constructor
     *
     * @param  array $config Configuration settings:
     *    'loginUrl'                => string Example: '/login'
     *    'validateSimpleFmAdapter' => Soliant\SimpleFM\Adapter
     *    'encryptionKey'           => string Example: '56cb36c21eb9a29c1317092b973a5f9cba393a367de783af45a2799f7302c',
     *    'appUsername'             => string Example: 'webSystem'
     *    'appPassword'             => string Example: '317akx1gr43m4pd'
     * @throws Soliant\SimpleFM\ZF2\Authentication\Adapter\InvalidArgumentException
     * @return void
     */
    public function __construct(array $config, Adapter $simpleFmValidateAdapter)
    {
        $this->simpleFmValidateAdapter = $simpleFmValidateAdapter;

        if (empty($config['loginUrl'])) {
            throw new Exception\InvalidArgumentException('Config key \'loginUrl\' is required');
        }
        $this->loginUrl = $config['loginUrl'];

        if (empty($config['encryptionKey'])) {
            throw new Exception\InvalidArgumentException('Config key \'encryptionKey\' is required');
        }
        $this->encryptionKey = $config['encryptionKey'];

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
     * @return Soliant\SimpleFM\ZF2\Authentication\Adapter\Auth
     */
    public function setUsername($username){
        $this->username = $username;
        $this->credentials['username'] = $username;
        return $this;
    }
    
    /**
     * @return Soliant\SimpleFM\ZF2\Authentication\Adapter\Auth
     */
    public function setPassword($password){
        $this->password = $password;
        $this->credentials['password'] = $password;
        return $this;
    }
    

    /**
     * @return Zend\Authentication\Result
     */
    public function authenticate()
    {

        $this->simpleFmValidateAdapter->setLayoutname($this->identityLayout);
        $this->simpleFmValidateAdapter->setCredentials($this->credentials);
        
        $command = array(
                    $this->accountNameField => $this->username,
                    '-find' => NULL,
                );
        $this->simpleFmValidateAdapter->setCommandarray($command);
        
        $result = $this->simpleFmValidateAdapter->execute();
        
        // Based on the status, return auth result
        switch ($result['error']) {
            case '0':
                // Return result as identity only for status '0'
                $identity = new Identity($this->username, $this->password, $this->encryptionKey, $result['rows'][0]);
                $identity->setIsLoggedIn(TRUE);
                return new Result(
                    Result::SUCCESS,
                    $identity
                );
            default:
                // Return empty identity and result as message array for every other result status
                $identity = new Identity($this->username, $this->password, $this->encryptionKey);
                $identity->setIsLoggedIn(FALSE);
                return new Result(
                    Result::FAILURE,
                    array(),
                    array('reason' => 'Auth request failed','sfm_auth_response' => $result)
                );
        }
    }

}

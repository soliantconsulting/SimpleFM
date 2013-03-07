<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2013 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Authentication\Mapper;

use Zend\Crypt\BlockCipher;
use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("User")
 */
class Identity
{
    protected $isLoggedIn = FALSE;
    
    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Username:"})
     */
    public $username;
    
    /**
     * @Annotation\Type("Zend\Form\Element\Password")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Password:"})
     */
    public $password;
    
    /**
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Remember Me ?:"})
     */
    public $rememberme;
    
    /**
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Submit"})
     */
    public $submit;

    public function __construct($username=NULL, $password=NULL, $encryptionKey=NULL, array $simpleFMAdapterRow=NULL){
        
        $this->setUsername($username);
        
        if (!empty($password)){
            if (empty($encryptionKey)) {
                throw new Exception\InvalidArgumentException('The you must provide an encryptionKey with the password.');
            }
            $this->setPassword($password, $encryptionKey);
        }
        
        if (!empty($simpleFMAdapterRow)){ 
            foreach ($simpleFMAdapterRow as $field => $value){
                $this->$field = $value;
            }
        }
    }

    public function isLoggedIn(){
        return $this->isLoggedIn;
    }

    public function setIsLoggedIn($value){
        $this->isLoggedIn = $value;
        return $this;
    }
    
	/**
     * @return the $username
     */
    public function getUsername ()
    {
        return $this->username;
    }

	/**
     * @param field_type $username
     */
    public function setUsername ($username)
    {
        $this->username = $username;
        return $this;
    }

	/**
     * @return the $password
     */
    public function getPassword ($encryptionKey)
    {
        if (empty($encryptionKey)) {
            throw new Exception\InvalidArgumentException('The encryptionKey must not be empty');
        }
        
        $blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
        $blockCipher->setKey($encryptionKey);
        return $blockCipher->decrypt($this->password);
    }

	/**
     * @param string $password
     */
    public function setPassword ($password, $encryptionKey)
    {
        
        /**
         * Password is encrypted so that the identity object is never at rest
         * (e.g. in the session file or database) with a password in clear text.
         */
        if (!is_string($password)) {
            throw new Exception\InvalidArgumentException('The password must be a string');
        }
        if (empty($encryptionKey)) {
            throw new Exception\InvalidArgumentException('The encryptionKey must not be empty');
        }
        
        $blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
        $blockCipher->setKey($encryptionKey);
        $this->password = $blockCipher->encrypt($password);
        
        $this;
    }


}

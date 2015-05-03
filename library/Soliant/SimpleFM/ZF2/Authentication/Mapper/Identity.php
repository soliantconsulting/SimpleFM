<?php
/**
 * This source file is subject to the MIT license that is bundled with this package in the file LICENSE.txt.
 *
 * @package   Soliant\SimpleFM\ZF2
 * @copyright Copyright (c) 2007-2015 Soliant Consulting, Inc. (http://www.soliantconsulting.com)
 * @author    jsmall@soliantconsulting.com
 */

namespace Soliant\SimpleFM\ZF2\Authentication\Mapper;

use Zend\Crypt\BlockCipher;
use Zend\Form\Annotation;

/**
 * @Annotation\Hydrator("Zend\Stdlib\Hydrator\ObjectProperty")
 * @Annotation\Name("login_form")
 */
class Identity
{
    /**
     * @Annotation\Exclude
     * @var bool
     */
    protected $isLoggedIn = false;

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Username:"})
     * @var string
     */
    public $username;

    /**
     * @Annotation\Type("Zend\Form\Element\Password")
     * @Annotation\Required({"required":"true" })
     * @Annotation\Filter({"name":"StripTags"})
     * @Annotation\Options({"label":"Password:"})
     * @var string
     */
    protected $password;

    /**
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Remember Me:"})
     * @var boolean
     */
    protected $rememberme;

    /**
     * This property allows the AnnotationBuilder can add the submit button for us. It is not used by this class.
     * @Annotation\Type("Zend\Form\Element\Submit")
     * @Annotation\Attributes({"value":"Submit"})
     */
    protected $submit;

    /**
     * @param string|null $username
     * @param string|null $password
     * @param string|null $encryptionKey
     * @param array $simpleFMAdapterRow
     * @param boolean $rememberMe
     * @return void
     */
    public function __construct(
        $username = null,
        $password = null,
        $rememberme = false,
        $encryptionKey = null,
        array $simpleFMAdapterRow = []
    ) {

        $this->setUsername($username);

        if (!empty($password)) {
            if (empty($encryptionKey)) {
                //If encryptionKey is not set, Identity will not keep $password
                $this->password = null;
            } else {
                //If encryptionKey is set, setter encrypts $password
                $this->setPassword($password, $encryptionKey);
            }
        }

        $this->rememberme = (boolean) $rememberme;

        if (!empty($simpleFMAdapterRow)) {
            foreach ($simpleFMAdapterRow as $field => $value) {
                $this->setArbitraryProperty($field, $value);
            }
        }
    }

    /**
     * Keep the provided syntax for the property name, but also create one that is only alphanumeric
     * in case the field comes with spaces or special characters, so you don't have to do this every
     * time you want to use a property: $identity->{'My Table::My Field'}
     * @param $field
     * @param $value
     * @return Identity
     */
    protected function setArbitraryProperty($field, $value)
    {
        if (!empty($field)) {
            $this->$field = $value;
            $noSpaces = str_replace(' ', '_', $field);
            $alphaNum = preg_replace("/[^A-Za-z0-9-_]/", '', $noSpaces);
            $this->$alphaNum = $value;
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsLoggedIn()
    {
        return (boolean) $this->isLoggedIn;
    }

    /**
     * @param boolean $value
     * @return Identity
     */
    public function setIsLoggedIn($value)
    {
        $this->isLoggedIn = (boolean) $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return Identity
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @var string|null $encryptionKey
     * @return string|null
     */
    public function getPassword($encryptionKey)
    {
        if (empty($encryptionKey)) {
            return null;
        }

        if (empty($this->password)) {
            return null;
        }

        $blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
        $blockCipher->setKey($encryptionKey);
        return $blockCipher->decrypt($this->password);
    }

    /**
     * @param string $password
     * @return Identity
     */
    public function setPassword($password, $encryptionKey)
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

        return $this;
    }
}

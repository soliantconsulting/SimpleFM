# Authenticating a user

SimpleFM supplies you with the tools to authenticate a user against the server an run requests under their permissions.
Since the user's name and password are required for making requests on their behalf, it needs to be stored temporarily.
When you authenticate the user against the server, it will return an identity which contains both the username and the
password in an encrypted form. An identity handler is responsible for encrypting and decrypting the password, and while
you can implement your own one, SimpleFM supplies a block chipher identity handler based on `Zend\Crypt`.

Using the built-in identity handler, you can quickly create an authenticator like this:

```
<?php
use Soliant\SimpleFM\Authentication\Authenticator;
use Soliant\SimpleFM\Authentication\BlockCipherIdentityHandler;
use Zend\Crypt\BlockCipher;

$identityHandler = new BlockCipherIdentityHandler(
    BlockCipher::factory('openssl', ['algo' => 'aes'])->setKey('some-strong-encryption-key')
);

$authenticator = new Authenticator(
    $resultSetClient,
    $identityHandler,
    'identity-layout',
    'username-field'
);
```

With the authenticator created you are able to authenticator the user against it. It will return a `Result` object,
which will contain the identity on success:

```php
<?php
$result = $authenticator->authenticate($username, $password);

if (!$result->isSuccess()) {
    // Prompt the user to enter their credentials again
}

$identity = $result->getIdentity();
```

You should store the returned identity, either in a session or in a cookie. Now before you can make requests with that
identity, you have to pass the identity handler to the connection first. To do so, you pass it as fourth parameter to
the constructor:

```php
<?php
$connection = new Connection(
    $httpClient,
    $uri,
    $databsae,
    $identityHandler
);
```

# Running requests with user permissions

To run a request with user permissions, you can do that either manually directly through the result set client or via
repositories. First, let's illustrate a simple example with the result set client:

```php
<?php
$records = $resultSetClient->execute(
    (new Command('sample-layout', ['-findall' => null]))->withIdentity($identity)
);
```

This would return all records which the user has permission to see. Since you will normally not run commands directly
but via a repository, you'd want to run requests through those instead:

```php
<?php
$entities = $repository->withIdentity($identity)->findAll();
```

As any object within SimpleFM is immutable, the `withIdentity()` method actually returns a clone of the repository with
the identity set to it. You can re-use that clone if you need to run multiple requests.


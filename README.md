# Symfony rpc bundle

Rpc server for symfony that supports annotation of all Http methods


### Installation

Install with composer

```sh
$ composer require lvovgeka/symfony-json-rpc-bundle
```

### Configure

Add in AppKernel:

```php
// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            //..
            new \Lvovgeka\JsonRpcBundle\LvovgekaJsonRpcBundle()
        ];
    //..    
```

Add in routing.yml:

```yml
#..
lvovgeka_json_rpc:
    resource: "@LvovgekaJsonRpcBundle/Resources/config/routing.yml"
    
```

Run:

```sh
$ php bin/console debug:rpc:methods
```
if you see:

```sh
+---+---------+------------------------------------------+---------+
| # | method  | class                                    | params  |
+---+---------+------------------------------------------+---------+
| 0 | Example | Lvovgeka\JsonRpcBundle\RpcMethod\Example | param1  |
|   |         |                                          | param2  |
+---+---------+------------------------------------------+---------+
```
You have properly configured rpc bundle

### Add new rpc method

in /vendor/lvovgeka/symfony-json-rpc-bundle/RpcMethod is an example of a method which you can take to create a new method.

Methods should be in YouBundle/RpcMethod.

Will do an example on the AppBundle:

- Create folder RpcMethod
- Copy from /vendor/lvovgeka/symfony-json-rpc-bundle/RpcMethod/Example.php to src/AppBundle/RpcMethod with name Foo.php
- We will review the file:
```php
<?php

namespace AppBundle\RpcMethod;

use Lvovgeka\JsonRpcBundle\Mapping as Rpc;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Foo class
 * @Rpc\Method("Foo")
 * @Rpc\HttpMethods({"GET", "HEAD", "POST"})
 * @Rpc\Cache(lifetime=3600) 
 */
class Foo
{
    /**
     * @Rpc\Param()
     *
     */
    protected $param1 = 'default value';

    /**
     * @Rpc\Param()
     * @Assert\NotBlank()
     */
    protected $param2;

    /**
     * @Rpc\Execute()
     */
    public function execute()
    {
        return 'result : param2 - ' . $this->param2 . '; param1 - ' . $this->param1;
    }
} 
```
- Save all
- Run:
```sh
$ php bin/console debug:rpc:methods
```
if you see:

```sh
+---+--------+-------------------------+---------+
| # | method | class                   | params  |
+---+--------+-------------------------+---------+
| 0 | Foo    | AppBundle\RpcMethod\Foo | param1  |
|   |        |                         | param2  |
+---+--------+-------------------------+---------+
```
You have correctly created your new rpc method.


License
----

MIT



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

### How to throw a request to the server

- On which route to send the requests?

By default, rpc server is listening http://youdomain.com/json-rpc but you can change this in the routing.yml by adding the statement prefix.

- How to send requests?

For POST, PUT ... : In the request body

Example with HttpRequest:
```php
<?php

$request = new HttpRequest();
$request->setUrl('http://youdomain.com/json-rpc');
$request->setMethod(HTTP_METH_POST); 
$request->setBody('{"jsonrpc": "2.0", "method": "Foo", "params": {"param1": 1, "param2": 2}, "id": 1}');

try {
  $response = $request->send();

  echo $response->getBody();
} catch (HttpException $ex) {
  echo $ex;
}
```

---------------------------------------

For Get, COPY ... : In the get params


Example with HttpRequest:
```php
 <?php
 
 $request = new HttpRequest();
 $request->setUrl('http://youdomain.com/json-rpc');
 $request->setMethod(HTTP_METH_GET);
 $request->setQueryData(array(
   'request' => '{"jsonrpc": "2.0", "method": "Foo", "params": {"param1": 1, "param2": 2}, "id": 1}'
 )); 
  
 try {
   $response = $request->send();
 
   echo $response->getBody();
 } catch (HttpException $ex) {
   echo $ex;
 }
```


---------------------------------------







License
----

MIT



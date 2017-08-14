<?php
/**
 * @Response 200 object User
 */
class GetUser
{
    public function __invoke(string $userId)
    {
        return [];
    }
}

/**
 * @Response 200 object User
 */
class PutUser
{
    /**
     * @throws UnauthorizedException
     */
    public function __invoke(string $userId)
    {
        return [];
    }
}

class UnauthorizedException extends Exception
{
    public function __construct($message = "", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
    
}

class User
{
    private $id;
}

$loader = require '../vendor/autoload.php';

# Required by gson library
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$app = new \Documenter\FakeApp('My api name');
$app->setBasePath('/my/api/basepath');
$app->setDescription('My api description');
$app->setVersion('1.0.0');


$app->get('/users/{userId}', GetUser::class);
$app->put('/users/{userId}', PutUser::class);

$formatter = new \Documenter\Formatter\SwaggerFormatter();

echo $formatter->getDocumentation($app);
/**
    Output:
 {"swagger":"2.0","info":{"title":"My api name","description":"My api description","version":"1.0.0"},"basePath":"\/my\/api\/basepath","consumes":["application\/json"],"produces":["application\/json"],"paths":{"\/users\/{userId}":{"get":{"operationId":"GET-\/users\/{userId}","description":"","responses":{"200":{"description":"User","schema":{"$ref":"#\/definitions\/User"}}},"parameters":[{"name":"userId","description":"","required":true,"in":"path","type":"string"}]},"put":{"operationId":"PUT-\/users\/{userId}","description":"","responses":{"403":{"description":""},"200":{"description":"User","schema":{"$ref":"#\/definitions\/User"}}},"parameters":[{"name":"userId","description":"","required":true,"in":"path","type":"string"}]}}},"definitions":{"User":{"type":"object","properties":{"id":{"type":"string"}}}}}
 
 */
<?php

namespace Documenter;

use DocBlockReader\Reader;
use ReflectionClass;
use Documenter\Responses\ArrayResponse;
use Documenter\Responses\ErrorResponse;
use Documenter\Responses\ObjectResponse;
use Documenter\Responses\Response;
use Tebru\Gson\Gson;

class FakeApp
{
    /**
     * @var Gson
     */
    protected $gson;
    protected $name;
    protected $description = '';
    protected $version = '0.0.0';
    protected $basePath = '';
    /**
     * @var DefinitionsContainer
     */
    protected $definitionsContainer;
    
    public function __construct(string $name)
    {
        $this->gson = Gson::builder()->build();
        $this->name = $name;
        $this->definitionsContainer = new DefinitionsContainer();
    }
    
    protected $prefix;
    /**
     * @var Endpoint[]
     */
    protected $endpoints;
    
    public function group(string $path, callable $callable)
    {
        $originalPrefix = $this->prefix;
        $this->prefix .= $path;
        call_user_func($callable);
        $this->prefix = $originalPrefix;
    }
    
    public function __call($method, $arguments)
    {
        $method = strtoupper($method);
        
        $endpoint = new Endpoint($method, $this->prefix . $arguments[0]);
        $reader = new Reader($arguments[1]);
        $apiAuth = $reader->getParameter('ApiAuth');
        if ($apiAuth !== null) {
            $endpoint->isAuthenticated(true);
        }
        
        $methodReader = new Reader($arguments[1], '__invoke');
        $throws = $methodReader->getParameter('throws') ?? [];
        if (! empty($throws) && ! is_array($throws)) {
            $throws = [$throws];
        }
        $reflectionClass = new ReflectionClass($arguments[1]);
        $reflectionMethod = $reflectionClass->getMethod('__invoke');
        $parameters = $reflectionMethod->getParameters();
        foreach ($parameters as $parameter) {
            switch ($parameter->getName()) {
                case 'response':
                    break;
                case 'payload':
                    $endpoint->setPayload($this->gson->toJson($this->buildPayload($parameter->getClass())));
                    break;
                default:
                    $endpoint->addPathParam($parameter->getName(), '');
            }
        }
        
        $this->parseResponses($reader, $endpoint, $throws);
        if (empty($endpoint->getResponses())) {
            throw new \RuntimeException('Missing responses for endpoint: ' . $endpoint);
        }
        $this->endpoints[] = $endpoint;
    }
    
    /**
     * @return Endpoint[]
     */
    public function getEndpoints(): array
    {
        return $this->endpoints;
    }
    
    /**
     * @param ReflectionClass $class
     * @return Object
     * @internal param $parameter
     */
    protected function buildPayload(ReflectionClass $class)
    {
        $newInstance = $class->newInstance();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $propertyReader = new Reader($class->getName(), $property->getName(), 'property');
            $type = null;
            if ($property->getDocComment()) {
                $type = $propertyReader->getParameter('var');
            }
            
            
            $value = $this->getValue($type, $property->getName());
            
            $property->setAccessible(true);
            $property->setValue($newInstance, $value);
        }
        return $newInstance;
    }
    
    /**
     * @param $type
     * @param $name
     * @return array|null|string
     */
    protected function getValue(?string $type, string $name)
    {
        switch (true) {
            case $type == 'null';
                $value = '';
                break;
            case $type == 'int':
                $value = 0;
                break;
            case $type == 'array':
                $value = [];
                break;
            case substr($type, -2) == '[]':
                $subType = substr($type, 0, strlen($type) - 2);
                $value = [
                    $this->getValue($subType, $name)
                ];
                break;
            case class_exists($type):
                $value = $this->buildPayload(new ReflectionClass($type));
                break;
            default:
                $value = $name;
        }
        return $value;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * @param string $description
     * @return FakeApp
     */
    public function setDescription(string $description): FakeApp
    {
        $this->description = $description;
        return $this;
    }
    
    public function getVersion()
    {
        return $this->version;
    }
    
    /**
     * @param string $version
     * @return FakeApp
     */
    public function setVersion(string $version): FakeApp
    {
        $this->version = $version;
        return $this;
    }
    
    /**
     * @param Reader $reader
     * @param Endpoint $endpoint
     * @param array|null $throws
     */
    protected function parseResponses(Reader $reader, Endpoint $endpoint, ?array $throws = []): void
    {
        $responses = $reader->getParameter('Response');
    
        $this->addThrows($endpoint, $throws);
    
        if (empty($responses)) {
            return;
        }
        
        if ( ! is_array($responses)) {
            $responses = [$responses];
        }
        foreach ($responses as $responseDefinition) {
            $this->addResponse($endpoint, $responseDefinition);
        }
    }
    
    public function getBasePath()
    {
        return $this->basePath;
    }
    
    /**
     * @param string $basePath
     * @return FakeApp
     */
    public function setBasePath(string $basePath): FakeApp
    {
        $this->basePath = $basePath;
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getDefinitions()
    {
        return $this->definitionsContainer->getDefinitions();
    }
    
    /**
     * @param Endpoint $endpoint
     * @param array $throws
     */
    protected function addThrows(Endpoint $endpoint, ?array $throws = []): void
    {
        if (!empty($throws)) {
            foreach ($throws as $throw) {
                /**
                 * @var \Throwable $error
                 */
                $error = new $throw();
                $endpoint->addResponse(new ErrorResponse($error->getCode()));
            }
        }
    }
    
    /**
     * @param Endpoint $endpoint
     * @param $responseDefinition
     */
    protected function addResponse(Endpoint $endpoint, string $responseDefinition): void
    {
        $elements = explode(' ', $responseDefinition);
        $statusCode = array_shift($elements);
        if (!in_array($statusCode, [204])) {
            $type = array_shift($elements);
            $responseClass = $elements[0] ?? null;
            switch ($type) {
                case 'array':
                    $response = new ArrayResponse(
                        intval($statusCode),
                        $this->definitionsContainer->getDefinition($responseClass),
                        implode(' ', $elements)
                    );
                    break;
                case 'object':
                    $response = new ObjectResponse(
                        intval($statusCode),
                        $this->definitionsContainer->getDefinition($responseClass),
                        implode(' ', $elements)
                    );
                    break;
                default:
                    $response = new Response(
                        intval($statusCode),
                        implode(' ', $elements)
                    );
            }
        } else {
            $response = new Response(
                intval($statusCode),
                implode($elements)
            );
        }
        $endpoint->addResponse($response);
    }
};
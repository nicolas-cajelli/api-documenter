<?php

namespace Documenter;

use Documenter\Responses\Response;

class Endpoint
{
    /**
     * @var bool
     */
    protected $isAuthenticated = false;
    protected $pathParams = [];
    protected $bodyParams = [];
    protected $payload;
    /**
     * @var Response[]
     */
    protected $responses = [];
    protected $description = '';
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $path;
    
    public function __construct(string $method, string $path)
    {
        $this->method = $method;
        $this->path = $path;
    }
    
    public function isAuthenticated($value)
    {
        if ($value !== null) {
            $this->isAuthenticated = $value;
        }
        return $this->isAuthenticated;
    }
    
    public function addPathParam(string $paramName, string $paramDescription)
    {
        $this->pathParams[$paramName] = $paramDescription;
        return $this;
    }
    
    public function getMethod()
    {
        return strtoupper($this->method);
    }
    
    public function getPath()
    {
        return $this->path;
    }
    
    public function getPathParams()
    {
        return $this->pathParams;
    }
    
    public function setPayload(string $class)
    {
        $this->payload = $class;
        return $this;
    }
    
    /**
     * @return string|null
     */
    public function getPayload() : ?string
    {
        return $this->payload;
    }
    
    /**
     * @return Response[]
     */
    public function getResponses() : array
    {
        return $this->responses;
    }
    
    public function __toString()
    {
        return $this->method . '-' . $this->path;
    }
    
    public function addResponse(Response $response)
    {
        $this->responses[] = $response;
        return $this;
    }
    
    public function getDescription()
    {
        return $this->description . ($this->isAuthenticated(null) ? '(AUTH REQUIRED)' : '');
    }
}
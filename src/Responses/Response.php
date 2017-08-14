<?php

namespace Documenter\Responses;

class Response
{
    /**
     * @var int
     */
    private $statusCode;
    /**
     * @var string
     */
    private $description;
    
    public function __construct(int $statusCode, string $description)
    {
        $this->statusCode = $statusCode;
        $this->description = $description;
    }
    
    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    
    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
}
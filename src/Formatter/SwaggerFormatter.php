<?php

namespace Documenter\Formatter;

use Documenter\Definition;
use Documenter\FakeApp;
use Documenter\Responses\ArrayResponse;
use Documenter\Responses\ObjectResponse;

class SwaggerFormatter implements Formatter
{
    public function getDocumentation(FakeApp $app) : string
    {
        $documentation = [
            'swagger' => '2.0',
            'info' => [
                'title' => $app->getName(),
                'description' => $app->getDescription(),
                'version' => $app->getVersion(),
            ],
            'basePath' => $app->getBasePath(),
            'consumes' => ['application/json'],
            'produces' => ['application/json'],
            'paths' => $this->getPaths($app),
            'definitions' => $this->getDefinitions($app),
        ];
        return json_encode($documentation);
    }
    
    protected function getPaths(FakeApp $app) : array
    {
        $paths = [];
        
        foreach ($app->getEndpoints() as $endpoint) {
            $path = [
                'operationId' => (string) $endpoint,
                'description' => $endpoint->getDescription(),
            ];
            
            foreach ($endpoint->getResponses() as $response) {
                $responseDefinition = [
                    'description' => $response->getDescription(),
                ];
                switch (true) {
                    case ($response instanceof ArrayResponse):
                        $responseDefinition['schema'] = [
                            'type' => 'array',
                            'items' => [
                                '$ref' => '#/definitions/' . $response->getElementsDefinition(),
                            ],
                        ];
                        break;
                    case ($response instanceof ObjectResponse):
                        $responseDefinition['schema'] = [
                            '$ref' => '#/definitions/' . $response->getDefinition(),
                        ];
                        break;
                    default:
                    
                }
                $path['responses'][$response->getStatusCode()] = $responseDefinition;
            }
            foreach ($endpoint->getPathParams() as $pathParam => $description) {
                $path['parameters'][] = [
                    'name' => $pathParam,
                    'description' => $description,
                    'required' => true,
                    'in' => 'path',
                    'type' => 'string'
                ];
            }
            $endpointPath = str_replace($app->getBasePath(), '', $endpoint->getPath());
            $paths[$endpointPath][strtolower($endpoint->getMethod())] = $path;
        }
        return $paths;
    }
    
    protected function getDefinitions(FakeApp $app)
    {
        $definitions = [];
        /** @var Definition $definition */
        foreach ($app->getDefinitions() as $definition) {
            
            $properties = [];
            
            foreach ($definition->getProperties() as $name => $type) {
                if ($type instanceof Definition) {
                    $properties[$name] = [
                        '$ref' => '#/definitions/' . $type->getName()
                    ];
                } else {
                    $properties[$name] = [
                        'type' => $type,
                    ];
                }
            }
            
            $definitions[$definition->getName()] = [
                'type' => 'object',
                'properties' => $properties,
            ];
        }
        return $definitions;
    }
}
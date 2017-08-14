<?php

namespace Documenter\Formatter;


use Documenter\FakeApp;

interface Formatter
{
    public function getDocumentation(FakeApp $app) : string;
}
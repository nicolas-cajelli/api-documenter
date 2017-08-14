API documenter
==============

This library is intended to automatically build documentation with the smallest friction possible in a defined scope (see [Scope](#Scope))
If your use case doesn't match the current scope, please create an issue/pr

Scope
-----

APIS running:

- Php 7.1 or higher (required by gson)
- Isolated Routes file with structure as [Slim framework](https://github.com/slimphp/Slim)

Install
-------

```bash
composer require nicolas-cajelli/api-documenter
```

Run (Default)
-------------

```bash
./vendor/bin/build-documentation src/routes.php "My api name" "/my/api/basepath"
```

Run (Custom)
------------

Create a .php in your project:

```php
<?php
$loader = require 'vendor/autoload.php';

# Required by gson library
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$app = new \Documenter\FakeApp('My api name');
$app->setBasePath('/my/api/basepath');
$app->setDescription('My api description');
$app->setVersion('1.0.0');

require 'src/routes.php';

$formatter = new \Documenter\Formatter\SwaggerFormatter();

echo $formatter->getDocumentation($app);

```



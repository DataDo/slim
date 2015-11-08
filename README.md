# slim
Build a Slim framework rest api from your Repository

## Usage
To use this bootstrapper you first need to add the composer dependency

    composer require datado/slim

Then you create your repository [more info here](https://github.com/DataDo/data) and attach it to your application.

```php

    $app = new \Slim\App;
    \DataDo\Slim::attach($app, $repository);
    
    
    // Or a shortcut if you are in a hurry
    $app = \DataDo\Slim::create($repository);
    
    
    // You can also pass multiple repositories at the same time
    $app = \DataDo\Slim::create($pageRepository, $fileRepository, $userRepository);

```

And now you're good to go!
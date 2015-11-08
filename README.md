# DataDo Slim
Build a Slim framework rest api from your Repository

## What is Slim?
Slim is a solid rest api framework that allows you to easily develop rest apis.
You can find all you need to know at [slimframework.com](http://www.slimframework.com/docs/).

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
Let's say your repository is hosting *User* entities. Then the above code will have created a rest api at `/users`.
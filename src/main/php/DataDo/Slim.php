<?php
/**
 * //TODO PHPDoc
 * @author Thomas Biesaart
 */

namespace DataDo;


use DataDo\Data\Repository;
use Psr\Http\Message\StreamInterface;
use ReflectionClass;
use ReflectionException;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

class Slim
{

    /**
     * Create a new slim app that hosts the provided repositories
     * @return App
     */
    public static function create()
    {
        $app = new App();
        $slimBS = new Slim();

        foreach (func_get_args() as $repo) {
            $slimBS->bootStrap($app, $repo);
        }

        return $app;

    }

    public static function attach(App $slim)
    {
        $slimBS = new Slim();

        foreach (func_get_args() as $arg) {
            if ($arg instanceof Repository) {
                $slimBS->bootStrap($slim, $arg);
            }
        }
    }

    /**
     * Add a repository to your Slim App
     * @param App $app
     * @param Repository $repository
     * @return App the given app for chaining
     */
    public function bootStrap(App $app, Repository $repository)
    {
        $baseUrl = '/' . self::parseRepoName($repository->getEntityClass()->getShortName());

        /**
         * Get the whole collection.
         */
        $app->get($baseUrl, function (Request $request, Response $response) use ($repository) {
            return $response
                ->write(self::output($repository->findAll()))
                ->withHeader('Content-Type', 'application/json');
        });

        /**
         * Delete the whole collection.
         */
        $app->delete($baseUrl, function (Request $request, Response $response) use ($repository) {
            $repository->deleteAll();
        });

        /**
         * Add a new entity to the collection.
         */
        $app->post($baseUrl, function (Request $request, Response $response) use ($repository) {

            $body = self::getBody($request->getBody(), $repository->getEntityClass(), $response);

            if ($body instanceof Response) {
                return $body;
            } else {
                // Store the entity
                $repository->insert($body);

                return $response
                    ->withStatus(Status::CREATED)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(self::output($body));
            }
        });


        /**
         * Display the repository check page.
         */
        $app->get($baseUrl . '/check', function (Request $request, Response $response) use ($repository) {
            $repository->checkDatabase();
        });


        $entityUrl = $baseUrl . '/{id}';

        /**
         * Get a single entity.
         */
        $app->get($entityUrl, function (Request $request, Response $response, $args) use ($repository) {
            $entity = $repository->get($args['id']);

            if ($entity) {
                return $response
                    ->write(self::output($entity))
                    ->withHeader('Content-Type', 'application/json');
            }

            return $response->withStatus(Status::NOT_FOUND);
        });

        /**
         * Delete a single entity
         */
        $app->delete($entityUrl, function (Request $request, Response $response, $args) use ($repository) {
            $repository->delete($args['id']);
        });

        /**
         * Replace a single entity
         */
        $app->put($entityUrl, function (Request $request, Response $response, $args) use ($repository) {

            $body = self::getBody($request->getBody(), $repository->getEntityClass(), $response);

            if ($body instanceof Response) {
                return $body;
            } else {
                // Store the entity
                $repository->getIdProperty()->setValue($body, $args['id']);
                $repository->update($body);

                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->write(self::output($body));
            }
        });

        return $app;
    }

    private static function output($input)
    {
        return json_encode($input, JSON_PRETTY_PRINT);
    }

    private static function parseRepoName($className)
    {
        $repoName = lcfirst($className);

        if (substr($repoName, strlen($repoName) - 1) !== 's') {
            $repoName .= 's';
        }

        return $repoName;
    }

    private static function getBody(StreamInterface $input, ReflectionClass $class, Response $response)
    {
        $body = self::parseBody($input);
        $newInstance = $class->newInstanceWithoutConstructor();

        foreach ($body as $key => $value) {
            try {
                $property = $class->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($newInstance, $value);
            } catch (ReflectionException $e) {
                return $response
                    ->withStatus(Status::BAD_REQUEST)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(self::output(['error' => $e->getMessage()]));
            }
        }

        if ($class->getConstructor() !== null) {
            $class->getConstructor()->setAccessible(true);
            $class->getConstructor()->invoke($newInstance);
        }

        return $newInstance;
    }

    private static function parseBody(StreamInterface $body)
    {
        $result = json_decode($body->getContents());

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid json');
        }

        return $result;
    }
}
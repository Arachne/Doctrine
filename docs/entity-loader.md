Entities as presenter parameters
====

To use entities as presenter parameters you'll need both Arachne/EntityLoader and Arachne/Doctrine:

```
$ composer require arachne/entity-loader arachne/doctrine
```

Then add the necessary extensions into your config.neon:

```
extensions:
    oops.cacheFactory: Oops\CacheFactory\DI\CacheFactoryExtension
    arachne.serviceCollections: Arachne\ServiceCollections\DI\ServiceCollectionsExtension
    arachne.containerAdapter: Arachne\ContainerAdapter\DI\ContainerAdapterExtension
    arachne.eventDispatcher: Arachne\EventDispatcher\DI\EventDispatcherExtension
    arachne.doctrine: Arachne\Doctrine\DI\DoctrineExtension
    arachne.entityLoader: Arachne\EntityLoader\DI\EntityLoaderExtension
```

Now continue normally with the [EntityLoader installation](https://github.com/Arachne/EntityLoader/blob/master/docs/installation.md).


Friendly URLs
----

You can use entities as parameters now. But what if you want to use a slugified name of the entity in URLs instead of ID? With some magic in Arachne/EntityLoader and Arachne/Doctrine you can do it.

First you need to change your routing to use `Arachne\EntityLoader\Routing\Route` instead of `Nette\Application\Routers\Route`.

Next enable envelopes in your config.neon:

```
arachne.entityLoader:
    envelopes: true
```

The envelopes are a simple objects implementing the __toString method. Thanks to that and some magic in the `Arachne\EntityLoader\Routing\Route` class they won't have any effect on your application other than that you can use them to get the underlying object in `Route::FILTER_OUT` callback. To turn the slugs back into entities implement `Arachne\Doctrine\EntityLoader\QueryInterface` and use it in `Route::FILTER_IN` callback.

```php
$router[] = new Route('/article/<entity>', [
    'presenter' => 'Article',
    'entity' => [
        Route::FILTER_OUT => function (\Arachne\EntityLoader\Application\Envelope $value) {
            return $value->getEntity()->getSlug();
        },
        Route::FILTER_IN => function ($slug) {
            return new ArticleQuery($slug);
        }
    ],
]);

class ArticleQuery implements \Arachne\Doctrine\EntityLoader\QueryInterface
{
    private $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function getEntity(EntityRepository $repository)
    {
        return $repository->findOneBy(['slug' => $this->slug]);
    }
}
```


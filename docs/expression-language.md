Caching for Symfony/ExpressionLanguage
====

To enable caching for ExpressionLanguage parser you'll need to install the following packages:

```
$ composer require arachne/expression-language arachne/doctrine kdyby/doctrine-cache symfony/doctrine-bridge
```

Then add the necessary extensions into your config.neon:

```
extensions:
    arachne.expression_language: Arachne\ExpressionLanguage\DI\ExpressionLanguageExtension
    arachne.doctrine: Arachne\Doctrine\DI\DoctrineExtension
```

You can use any cache driver supported by [Kdyby/DoctrineCache](https://github.com/Kdyby/DoctrineCache). By default it will use your autowired `Nette\Caching\IStorage` implementation.

```
arachne.doctrine:
    expressionLanguageCache: filesystem
```

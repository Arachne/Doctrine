Symfony/Validator and Doctrine ORM
====

To use Symfony/Validator and it's integrations for Doctrine ORM in your nette application install the following packages:

```
$ composer require kdyby/validator kdyby/annotations arachne/doctrine symfony/doctrine-bridge
```

Then add the extensions into your config.neon:

```
extensions:
    kdyby.annotations: Kdyby\Annotations\DI\AnnotationsExtension
    kdyby.validator: Kdyby\Validator\DI\ValidatorExtension
    arachne.doctrine: Arachne\Doctrine\DI\DoctrineExtension
```

UniqueEntity constraint
----

With Arachne/Doctrine you can use the [UniqueEntity](https://symfony.com/doc/current/reference/constraints/UniqueEntity.html) constraint.

Initializer
----

Doctrine entities sometimes may not have all properties loaded which is necessary for validation. Arachne/Doctrine adds the DoctrineInitializer to fix this.

Validation on flush
----

Sometimes it's useful to validate all inserted or updated entities to ensure consistency of the database. You can activate this feature in config.neon:

```
arachne.doctrine:
    validateOnFlush: true
```

You can also specify an array of validation groups:

```
arachne.doctrine:
    validateOnFlush:
        - Default
        - flush
```

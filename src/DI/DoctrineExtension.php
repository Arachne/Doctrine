<?php

namespace Arachne\Doctrine\DI;

use Arachne\Doctrine\EntityLoader\FilterInResolver;
use Arachne\Doctrine\EntityLoader\FilterOutResolver;
use Arachne\Doctrine\Validator\ValidatorListener;
use Arachne\EntityLoader\DI\EntityLoaderExtension;
use Arachne\EventManager\DI\EventManagerExtension;
use Arachne\Forms\DI\FormsExtension;
use Arachne\ServiceCollections\DI\ServiceCollectionsExtension;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Validator\DI\ValidatorExtension;
use Nette\DI\CompilerExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Bridge\Doctrine\Validator\DoctrineInitializer;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DoctrineExtension extends CompilerExtension
{
    /**
     * @var array
     */
    public $defaults = [
        'validateOnFlush' => false,
    ];

    public function loadConfiguration()
    {
        $this->validateConfig($this->defaults);
        Validators::assertField($this->config, 'validateOnFlush', 'bool|list');

        $builder = $this->getContainerBuilder();

        if ($this->getExtension(EntityLoaderExtension::class, false)) {
            /* @var $serviceCollectionsExtension ServiceCollectionsExtension */
            $serviceCollectionsExtension = $this->getExtension(ServiceCollectionsExtension::class);

            $serviceCollectionsExtension->overrideCollection(
                ServiceCollectionsExtension::TYPE_RESOLVER,
                EntityLoaderExtension::TAG_FILTER_IN,
                function ($originalService) use ($builder) {
                    $service = $this->prefix('entityLoader.filterInResolver');

                    $builder->addDefinition($service)
                        ->setClass(FilterInResolver::class)
                        ->setArguments(
                            [
                                'resolver' => '@'.$originalService,
                            ]
                        )
                        ->setAutowired(false);

                    return $service;
                }
            );

            $serviceCollectionsExtension->overrideCollection(
                ServiceCollectionsExtension::TYPE_RESOLVER,
                EntityLoaderExtension::TAG_FILTER_OUT,
                function ($originalService) use ($builder) {
                    $service = $this->prefix('entityLoader.filterOutResolver');

                    $builder->addDefinition($service)
                        ->setClass(FilterOutResolver::class)
                        ->setArguments(
                            [
                                'resolver' => '@'.$originalService,
                            ]
                        )
                        ->setAutowired(false);

                    return $service;
                }
            );
        }

        if ($this->getExtension(ValidatorExtension::class, false)) {
            $builder->addDefinition($this->prefix('validator.constraint.uniqueEntity'))
                ->setClass(UniqueEntityValidator::class)
                ->addTag(
                    ValidatorExtension::TAG_CONSTRAINT_VALIDATOR,
                    [
                        UniqueEntityValidator::class,
                        'doctrine.orm.validator.unique',
                    ]
                );

            $builder->addDefinition($this->prefix('validator.initializer'))
                ->setClass(DoctrineInitializer::class)
                ->addTag(ValidatorExtension::TAG_INITIALIZER);

            if ($this->config['validateOnFlush']) {
                $listener = $builder->addDefinition($this->prefix('validator.validatorListener'))
                    ->setClass(ValidatorListener::class)
                    ->setArguments(
                        [
                            'groups' => is_array($this->config['validateOnFlush']) ? $this->config['validateOnFlush'] : null,
                        ]
                    );

                if ($this->getExtension(EventManagerExtension::class, false)) {
                    $listener->addTag(EventManagerExtension::TAG_SUBSCRIBER);
                } elseif ($this->getExtension(EventsExtension::class, false)) {
                    $listener->addTag(EventsExtension::TAG_SUBSCRIBER);
                } else {
                    throw new AssertionException('The "validateOnFlush" option requires either Arachne/EventManager or Kdyby/Events to be installed.');
                }
            }
        } elseif ($this->config['validateOnFlush']) {
            throw new AssertionException('The "validateOnFlush" option requires Kdyby/Validator to be installed.');
        }

        if ($this->getExtension(FormsExtension::class, false)) {
            $builder->addDefinition($this->prefix('forms.typeGuesser'))
                ->setClass(DoctrineOrmTypeGuesser::class)
                ->addTag(FormsExtension::TAG_TYPE_GUESSER)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('forms.type.entity'))
                ->setClass(EntityType::class)
                ->addTag(
                    FormsExtension::TAG_TYPE,
                    [
                        EntityType::class,
                    ]
                )
                ->setAutowired(false);
        }
    }

    /**
     * @param string $class
     * @param bool   $need
     *
     * @return CompilerExtension|null
     */
    private function getExtension($class, $need = true)
    {
        $extensions = $this->compiler->getExtensions($class);

        if (!$extensions) {
            if (!$need) {
                return null;
            }

            throw new AssertionException(
                sprintf('Extension "%s" requires "%s" to be installed.', get_class($this), $class)
            );
        }

        return reset($extensions);
    }
}

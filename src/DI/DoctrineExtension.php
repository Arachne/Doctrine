<?php

namespace Arachne\Doctrine\DI;

use Arachne\Doctrine\EntityLoader\FilterIn;
use Arachne\Doctrine\EntityLoader\FilterOut;
use Arachne\Doctrine\Validator\ValidatorListener;
use Arachne\EntityLoader\DI\EntityLoaderExtension;
use Arachne\EventManager\DI\EventManagerExtension;
use Arachne\Forms\DI\FormsExtension;
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

        if ($this->getExtension(EntityLoaderExtension::class)) {
            $builder->addDefinition($this->prefix('validator.entityLoader.filterIn'))
                ->setClass(FilterIn::class)
                ->addTag(EntityLoaderExtension::TAG_FILTER_IN);

            $builder->addDefinition($this->prefix('validator.entityLoader.filterOut'))
                ->setClass(FilterOut::class)
                ->addTag(EntityLoaderExtension::TAG_FILTER_OUT);
        }

        if ($this->getExtension(ValidatorExtension::class)) {
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

                if ($this->getExtension(EventManagerExtension::class)) {
                    $listener->addTag(EventManagerExtension::TAG_SUBSCRIBER);
                } elseif ($this->getExtension(EventsExtension::class)) {
                    $listener->addTag(EventsExtension::TAG_SUBSCRIBER);
                } else {
                    throw new AssertionException('The "validateOnFlush" option requires either Arachne/EventManager or Kdyby/Events to be installed.');
                }
            }
        } elseif ($this->config['validateOnFlush']) {
            throw new AssertionException('The "validateOnFlush" option requires Kdyby/Validator to be installed.');
        }

        if ($this->getExtension(FormsExtension::class)) {
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
     *
     * @return CompilerExtension|null
     */
    private function getExtension(string $class): ?CompilerExtension
    {
        $extensions = $this->compiler->getExtensions($class);

        if (!$extensions) {
            return null;
        }

        return reset($extensions);
    }
}

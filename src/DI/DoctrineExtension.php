<?php

declare(strict_types=1);

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

    public function loadConfiguration(): void
    {
        $this->validateConfig($this->defaults);
        Validators::assertField($this->config, 'validateOnFlush', 'bool|list');

        $builder = $this->getContainerBuilder();

        if ($this->getExtension(EntityLoaderExtension::class) !== null) {
            $builder->addDefinition($this->prefix('entityLoader.filterIn'))
                ->setType(FilterIn::class)
                ->addTag(EntityLoaderExtension::TAG_FILTER_IN);

            $builder->addDefinition($this->prefix('entityLoader.filterOut'))
                ->setType(FilterOut::class)
                ->addTag(EntityLoaderExtension::TAG_FILTER_OUT);
        }

        if ($this->getExtension(ValidatorExtension::class) !== null) {
            $builder->addDefinition($this->prefix('validator.constraint.uniqueEntity'))
                ->setType(UniqueEntityValidator::class)
                ->addTag(
                    ValidatorExtension::TAG_CONSTRAINT_VALIDATOR,
                    [
                        UniqueEntityValidator::class,
                        'doctrine.orm.validator.unique',
                    ]
                );

            $builder->addDefinition($this->prefix('validator.initializer'))
                ->setType(DoctrineInitializer::class)
                ->addTag(ValidatorExtension::TAG_INITIALIZER);

            if ($this->config['validateOnFlush'] === true) {
                $listener = $builder->addDefinition($this->prefix('validator.validatorListener'))
                    ->setType(ValidatorListener::class)
                    ->setArguments(
                        [
                            'groups' => is_array($this->config['validateOnFlush']) ? $this->config['validateOnFlush'] : null,
                        ]
                    );

                if ($this->getExtension(EventManagerExtension::class) !== null) {
                    $listener->addTag(EventManagerExtension::TAG_SUBSCRIBER);
                } elseif ($this->getExtension(EventsExtension::class) !== null) {
                    $listener->addTag(EventsExtension::TAG_SUBSCRIBER);
                } else {
                    throw new AssertionException('The "validateOnFlush" option requires either Arachne/EventManager or Kdyby/Events to be installed.');
                }
            }
        } elseif ($this->config['validateOnFlush'] === true) {
            throw new AssertionException('The "validateOnFlush" option requires Kdyby/Validator to be installed.');
        }

        if ($this->getExtension(FormsExtension::class) !== null) {
            $builder->addDefinition($this->prefix('forms.typeGuesser'))
                ->setType(DoctrineOrmTypeGuesser::class)
                ->addTag(FormsExtension::TAG_TYPE_GUESSER)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('forms.type.entity'))
                ->setType(EntityType::class)
                ->addTag(
                    FormsExtension::TAG_TYPE,
                    [
                        EntityType::class,
                    ]
                )
                ->setAutowired(false);
        }
    }

    private function getExtension(string $class): ?CompilerExtension
    {
        $extensions = $this->compiler->getExtensions($class);

        if ($extensions === []) {
            return null;
        }

        return reset($extensions);
    }
}

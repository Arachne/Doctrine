<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\DI;

use Arachne\DIHelpers\CompilerExtension;
use Arachne\EntityLoader\DI\EntityLoaderExtension;
use Arachne\Forms\DI\FormsExtension;
use Kdyby\DoctrineCache\DI\Helpers;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Validator\DI\ValidatorExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;
use ReflectionClass;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DoctrineExtension extends CompilerExtension
{
    const TAG_SUBSCRIBER = 'arachne.doctrine.subscriber';

    /** @var array */
    public $defaults = [
        'validateOnFlush' => false,
        'expressionLanguageCache' => 'default',
    ];

    public function __construct($debugMode = false)
    {
        $this->defaults['debug'] = $debugMode;
    }

    public function loadConfiguration()
    {
        $this->validateConfig($this->defaults);
        Validators::assertField($this->config, 'validateOnFlush', 'bool|list');

        $builder = $this->getContainerBuilder();

        if (!$this->getExtension('Kdyby\Events\DI\EventsExtension', false) && $this->getExtension('Arachne\ContainerAdapter\DI\ContainerAdapterExtension', false)) {
            $builder->addDefinition($this->prefix('eventManager'))
                ->setClass('Symfony\Bridge\Doctrine\ContainerAwareEventManager');
        }

        if ($this->getExtension('Arachne\EntityLoader\DI\EntityLoaderExtension', false)) {
            $builder->addDefinition($this->prefix('entityLoader.filterInResolver'))
                ->setClass('Arachne\Doctrine\EntityLoader\FilterInResolver')
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('entityLoader.filterOutResolver'))
                ->setClass('Arachne\Doctrine\EntityLoader\FilterOutResolver')
                ->setAutowired(false);

            $extension = $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension');
            $extension->override(EntityLoaderExtension::TAG_FILTER_IN, $this->prefix('entityLoader.filterInResolver'));
            $extension->override(EntityLoaderExtension::TAG_FILTER_OUT, $this->prefix('entityLoader.filterOutResolver'));
        }

        if ($this->getExtension('Kdyby\Validator\DI\ValidatorExtension', false)) {
            $builder->addDefinition($this->prefix('validator.constraint.uniqueEntity'))
                ->setClass('Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator')
                ->addTag(ValidatorExtension::TAG_CONSTRAINT_VALIDATOR, [
                    'Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator',
                    'doctrine.orm.validator.unique',
                ]);

            $builder->addDefinition($this->prefix('validator.initializer'))
                ->setClass('Symfony\Bridge\Doctrine\Validator\DoctrineInitializer')
                ->addTag(ValidatorExtension::TAG_INITIALIZER);

            if ($this->config['validateOnFlush'] && $this->getExtension('Kdyby\Events\DI\EventsExtension', false)) {
                $builder->addDefinition($this->prefix('validator.validatorListener'))
                    ->setClass('Arachne\Doctrine\Validator\ValidatorListener')
                    ->setArguments([
                        'groups' => is_array($this->config['validateOnFlush']) ? $this->config['validateOnFlush'] : null,
                    ])
                    ->addTag(EventsExtension::TAG_SUBSCRIBER);
            }
        }

        if ($this->config['validateOnFlush'] && !$builder->hasDefinition($this->prefix('validator.validatorListener'))) {
            throw new AssertionException("The 'validateOnFlush' option requires Kdyby\Validator\DI\ValidatorExtension and Kdyby\Events\DI\EventsExtension.");
        }

        if ($this->getExtension('Arachne\Forms\DI\FormsExtension', false)) {
            $builder->addDefinition($this->prefix('forms.typeGuesser'))
                ->setClass('Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser')
                ->addTag(FormsExtension::TAG_TYPE_GUESSER)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('forms.type.entity'))
                ->setClass('Symfony\Bridge\Doctrine\Form\Type\EntityType')
                ->addTag(FormsExtension::TAG_TYPE, [
                    'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                    'entity',
                ])
                ->setAutowired(false);
        }

        if ($this->getExtension('Arachne\ExpressionLanguage\DI\ExpressionLanguageExtension', false)) {
            $builder->addDefinition($this->prefix('expressionLanguage.parserCache'))
                ->setClass('Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface')
                ->setFactory('Symfony\Bridge\Doctrine\ExpressionLanguage\DoctrineParserCache', [
                    'cache' => Helpers::processCache($this, $config['expressionLanguageCache'], 'expressionLanguage', $config['debug']),
                ]);
        }
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        if ($this->getExtension('Arachne\EntityLoader\DI\EntityLoaderExtension', false)) {
            $extension = $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension', false);

            $builder->getDefinition($this->prefix('entityLoader.filterInResolver'))
                ->setArguments([
                    'resolver' => '@' . $extension->get(EntityLoaderExtension::TAG_FILTER_IN, false),
                ]);

            $builder->getDefinition($this->prefix('entityLoader.filterOutResolver'))
                ->setArguments([
                    'resolver' => '@' . $extension->get(EntityLoaderExtension::TAG_FILTER_OUT, false),
                ]);
        }

        $subscribers = $builder->findByTag(self::TAG_SUBSCRIBER);
        if ($subscribers) {
            if (!$builder->hasDefinition($this->prefix('eventManager'))) {
                throw new AssertionException("Subscribers support requires 'Arachne\ContainerAdapter\DI\ContainerAdapterExtension' to be installed and 'Kdyby\Events\DI\EventsExtension' to NOT be installed.", E_USER_NOTICE);
            }

            $evm = $builder->getDefinition($this->prefix('eventManager'));
            foreach ($subscribers as $name => $attributes) {
                $subscriber = $builder->getDefinition($name);
                $evm->addSetup('?->addEventListener(?, ?)', [
                    '@self',
                    (new ReflectionClass($subscriber->getClass()))->newInstanceWithoutConstructor()->getSubscribedEvents(),
                    $name, // Intentionally without @ for laziness.
                ]);
            }
        }
    }
}

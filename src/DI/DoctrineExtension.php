<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\DI;

use Arachne\DIHelpers\CompilerExtension;
use Arachne\EntityLoader\DI\EntityLoaderExtension;
use Arachne\EventManager\DI\EventManagerExtension;
use Arachne\Forms\DI\FormsExtension;
use Kdyby\DoctrineCache\DI\Helpers;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Validator\DI\ValidatorExtension;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;

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
                ->addTag(
                    ValidatorExtension::TAG_CONSTRAINT_VALIDATOR,
                    [
                        'Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator',
                        'doctrine.orm.validator.unique',
                    ]
                );

            $builder->addDefinition($this->prefix('validator.initializer'))
                ->setClass('Symfony\Bridge\Doctrine\Validator\DoctrineInitializer')
                ->addTag(ValidatorExtension::TAG_INITIALIZER);

            if ($this->config['validateOnFlush']) {
                $listener = $builder->addDefinition($this->prefix('validator.validatorListener'))
                    ->setClass('Arachne\Doctrine\Validator\ValidatorListener')
                    ->setArguments(
                        [
                            'groups' => is_array($this->config['validateOnFlush']) ? $this->config['validateOnFlush'] : null,
                        ]
                    );

                if ($this->getExtension('Arachne\EventManager\DI\EventManagerExtension', false)) {
                    $listener->addTag(EventManagerExtension::TAG_SUBSCRIBER);
                } elseif ($this->getExtension('Kdyby\Events\DI\EventsExtension', false)) {
                    $listener->addTag(EventsExtension::TAG_SUBSCRIBER);
                } else {
                    throw new AssertionException("The 'validateOnFlush' option requires either Arachne/EventManager or Kdyby/Events to be installed.");
                }
            }
        } elseif ($this->config['validateOnFlush']) {
            throw new AssertionException("The 'validateOnFlush' option requires Kdyby/Validator to be installed.");
        }

        if ($this->getExtension('Arachne\Forms\DI\FormsExtension', false)) {
            $builder->addDefinition($this->prefix('forms.typeGuesser'))
                ->setClass('Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser')
                ->addTag(FormsExtension::TAG_TYPE_GUESSER)
                ->setAutowired(false);

            $builder->addDefinition($this->prefix('forms.type.entity'))
                ->setClass('Symfony\Bridge\Doctrine\Form\Type\EntityType')
                ->addTag(
                    FormsExtension::TAG_TYPE,
                    [
                        'Symfony\Bridge\Doctrine\Form\Type\EntityType',
                    ]
                )
                ->setAutowired(false);
        }

        if ($this->getExtension('Arachne\ExpressionLanguage\DI\ExpressionLanguageExtension', false)) {
            $builder->addDefinition($this->prefix('expressionLanguage.parserCache'))
                ->setClass('Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface')
                ->setFactory(
                    'Symfony\Bridge\Doctrine\ExpressionLanguage\DoctrineParserCache',
                    [
                        'cache' => Helpers::processCache(
                            $this,
                            $this->config['expressionLanguageCache'],
                            'expressionLanguage',
                            $this->config['debug']
                        ),
                    ]
                );
        }
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        if ($this->getExtension('Arachne\EntityLoader\DI\EntityLoaderExtension', false)) {
            $extension = $this->getExtension('Arachne\DIHelpers\DI\ResolversExtension', false);

            $builder->getDefinition($this->prefix('entityLoader.filterInResolver'))
                ->setArguments(
                    [
                        'resolver' => '@'.$extension->get(EntityLoaderExtension::TAG_FILTER_IN, false),
                    ]
                );

            $builder->getDefinition($this->prefix('entityLoader.filterOutResolver'))
                ->setArguments(
                    [
                        'resolver' => '@'.$extension->get(EntityLoaderExtension::TAG_FILTER_OUT, false),
                    ]
                );
        }
    }
}

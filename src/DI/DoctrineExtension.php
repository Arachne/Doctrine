<?php

namespace Arachne\Doctrine\DI;

use Arachne\DIHelpers\CompilerExtension;
use Arachne\EntityLoader\DI\EntityLoaderExtension;
use Arachne\Forms\DI\FormsExtension;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Validator\DI\ValidatorExtension;
use Nette\Utils\Validators;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DoctrineExtension extends CompilerExtension
{

	/** @var array */
	public $defaults = [
		'validateOnFlush' => false,
	];

	public function loadConfiguration()
	{
		$this->validateConfig($this->defaults);
		Validators::assertField($this->config, 'validateOnFlush', 'bool');

		$builder = $this->getContainerBuilder();

		if ($this->getExtension('Arachne\EntityLoader\DI\EntityLoaderExtension', false)) {
			$builder->addDefinition($this->prefix('entityLoader.filterInResolver'))
				->setClass('Arachne\Doctrine\EntityLoader\FilterInResolver')
				->setAutowired(false);

			$builder->addDefinition($this->prefix('entityLoader.filterOutResolver'))
				->setClass('Arachne\Doctrine\EntityLoader\FilterOutResolver')
				->setAutowired(false);

			$extension = $this->getExtension('Arachne\DIHelpers\DI\DIHelpersExtension');
			$extension->overrideResolver(EntityLoaderExtension::TAG_FILTER_IN, $this->prefix('entityLoader.filterInResolver'));
			$extension->overrideResolver(EntityLoaderExtension::TAG_FILTER_OUT, $this->prefix('entityLoader.filterOutResolver'));
		}

		if ($this->getExtension('Kdyby\Validator\DI\ValidatorExtension', false)) {
			$builder->addDefinition($this->prefix('validator.initializer'))
				->setClass('Symfony\Bridge\Doctrine\Validator\DoctrineInitializer')
				->addTag(ValidatorExtension::TAG_INITIALIZER);

			if ($this->config['validateOnFlush'] && $this->getExtension('Kdyby\Events\DI\EventsExtension', false)) {
				$builder->addDefinition($this->prefix('validator.validatorListener'))
					->setClass('Arachne\Doctrine\Validator\ValidatorListener')
					->addTag(EventsExtension::TAG_SUBSCRIBER);
			}
		}

		if ($this->getExtension('Arachne\Forms\DI\FormsExtension', false)) {
			$builder->addDefinition($this->prefix('forms.typeGuesser'))
				->setClass('Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser')
				->addTag(FormsExtension::TAG_TYPE_GUESSER)
				->setAutowired(false);

			$builder->addDefinition($this->prefix('forms.type.entity'))
				->setClass('Symfony\Bridge\Doctrine\Form\Type\EntityType')
				->addTag(FormsExtension::TAG_TYPE, 'entity')
				->setAutowired(false);
		}
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();

		if ($this->getExtension('Arachne\EntityLoader\DI\EntityLoaderExtension', false)) {
			$extension = $this->getExtension('Arachne\DIHelpers\DI\DIHelpersExtension');

			$builder->getDefinition($this->prefix('entityLoader.filterInResolver'))
				->setArguments([
					'resolver' => '@' . $extension->getResolver(EntityLoaderExtension::TAG_FILTER_IN, false),
				]);

			$builder->getDefinition($this->prefix('entityLoader.filterOutResolver'))
				->setArguments([
					'resolver' => '@' . $extension->getResolver(EntityLoaderExtension::TAG_FILTER_OUT, false),
				]);
		}
	}

}

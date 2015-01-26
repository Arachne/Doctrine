<?php

namespace Arachne\Doctrine\DI;

use Arachne\DIHelpers\CompilerExtension;
use Arachne\EntityLoader\DI\EntityLoaderExtension;
use Arachne\Forms\DI\FormsExtension;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Validator\DI\ValidatorExtension;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class DoctrineExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		if ($extension = $this->getExtension('Arachne\EntityLoader\DI\EntityLoaderExtension', FALSE)) {
			$builder->getDefinition($extension->prefix('entityLoader'))
				->setArguments([
					'converterResolver' => $this->prefix('@entityLoader.converterResolver'),
				]);

			$builder->addDefinition($this->prefix('entityLoader.doctrineConverter'))
				->setClass('Arachne\Doctrine\EntityLoader\DoctrineConverter');

			$extension = $this->getExtension('Arachne\DIHelpers\DI\DIHelpersExtension');

			$builder->addDefinition($this->prefix('entityLoader.converterResolver'))
				->setClass('Arachne\Doctrine\EntityLoader\ConverterResolver')
				->setArguments([
					'resolver' => '@' . $extension->getResolver(EntityLoaderExtension::TAG_CONVERTER),
				])
				->setAutowired(FALSE);
		}

		if ($this->getExtension('Kdyby\Events\DI\EventsExtension', FALSE)) {
			$builder->addDefinition($this->prefix('validator.validatorListener'))
				->setClass('Arachne\Doctrine\Validator\ValidatorListener')
				->addTag(EventsExtension::TAG_SUBSCRIBER);
		}

		if ($this->getExtension('Kdyby\Validator\DI\ValidatorExtension', FALSE)) {
			$builder->addDefinition($this->prefix('validator.initializer'))
				->setClass('Symfony\Bridge\Doctrine\Validator\DoctrineInitializer')
				->addTag(ValidatorExtension::TAG_INITIALIZER);
		}

		if ($this->getExtension('Arachne\Forms\DI\FormsExtension', FALSE)) {
			$builder->addDefinition($this->prefix('forms.typeGuesser'))
				->setClass('Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser')
				->addTag(FormsExtension::TAG_TYPE_GUESSER)
				->setAutowired(FALSE);

			$builder->addDefinition($this->prefix('forms.type.entity'))
				->setClass('Symfony\Bridge\Doctrine\Form\Type\EntityType')
				->addTag(FormsExtension::TAG_TYPE, 'entity')
				->setAutowired(FALSE);
		}
	}

}

<?php

namespace Arachne\Doctrine\DI;

use Nette\DI\CompilerExtension;
use Arachne\Forms\DI\FormsExtension;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Validator\DI\ValidatorExtension;
use Nette\DI\Statement;

/**
 * @author Jáchym Toušek
 */
class DoctrineExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		if ($extensions = $this->compiler->getExtensions('Arachne\EntityLoader\DI\EntityLoaderExtension')) {
			$extension = reset($extensions);

			$builder->addDefinition($this->prefix('entityLoader.doctrineConverter'))
				->setClass('Arachne\Doctrine\EntityLoader\DoctrineConverter');

			$builder->addDefinition($this->prefix('entityLoader.converterResolver'))
				->setFactory('Arachne\Doctrine\EntityLoader\ConverterResolver', [ 'resolver' => $extension->prefix('@converterResolver') ])
				->setAutowired(FALSE);

			$builder->getDefinition($extension->prefix('entityLoader'))
				->setArguments([
					'converterResolver' => $this->prefix('@entityLoader.converterResolver'),
				]);
		}

		if ($this->compiler->getExtensions('Kdyby\Events\DI\EventsExtension')) {
			$builder->addDefinition($this->prefix('validator.validatorListener'))
				->setClass('Arachne\Doctrine\Validator\ValidatorListener')
				->addTag(EventsExtension::SUBSCRIBER_TAG);
		}

		if ($this->compiler->getExtensions('Kdyby\Validator\DI\ValidatorExtension')) {
			$builder->addDefinition($this->prefix('validator.initializer'))
				->setClass('Symfony\Bridge\Doctrine\Validator\DoctrineInitializer')
				->addTag(ValidatorExtension::TAG_INITIALIZER);
		}

		if ($this->compiler->getExtensions('Arachne\Forms\DI\FormsExtension')) {
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

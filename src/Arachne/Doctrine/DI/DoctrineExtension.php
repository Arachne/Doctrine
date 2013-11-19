<?php

namespace Arachne\Doctrine\DI;

use Nette\DI\CompilerExtension;
use Arachne\Forms\DI\FormsExtension;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Validator\DI\ValidatorExtension;

/**
 * @author Jáchym Toušek
 */
class DoctrineExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('managerRegistry'))
			->setClass('Doctrine\Common\Persistence\ManagerRegistry')
			->setFactory('Arachne\Doctrine\ManagerRegistry');

		if (class_exists('Arachne\EntityLoader\DI\EntityLoaderExtension')) {
			$builder->addDefinition($this->prefix('entityLoader.doctrineConverter'))
				->setClass('Arachne\EntityLoader\IConverter')
				->setFactory('Arachne\Doctrine\EntityLoader\DoctrineConverter');
		}

		if (class_exists('Kdyby\Events\DI\EventsExtension')) {
			$builder->addDefinition($this->prefix('validator.validatorListener'))
				->setClass('Arachne\Doctrine\Validator\ValidatorListener')
				->addTag(EventsExtension::SUBSCRIBER_TAG);
		}

		if (class_exists('Kdyby\Validator\DI\ValidatorExtension')) {
			$builder->addDefinition($this->prefix('validator.initializer'))
				->setFactory('Symfony\Bridge\Doctrine\Validator\DoctrineInitializer')
				->addTag(ValidatorExtension::TAG_INITIALIZER);
		}

		if (class_exists('Arachne\Forms\DI\FormsExtension')) {
			$builder->addDefinition($this->prefix('forms.typeGuesser'))
				->setClass('Symfony\Component\Form\FormTypeGuesserInterface')
				->setFactory('Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser')
				->addTag(FormsExtension::TAG_TYPE_GUESSER);

			$builder->addDefinition($this->prefix('forms.type.entity'))
				->setClass('Symfony\Bridge\Doctrine\Form\Type\EntityType')
				->addTag(FormsExtension::TAG_TYPE, 'entity')
				->setAutowired(FALSE);
		}
	}

}

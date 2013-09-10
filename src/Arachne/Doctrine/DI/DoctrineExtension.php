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

		$builder->addDefinition($this->prefix('validatorListener'))
			->setClass('Arachne\Doctrine\ValidatorListener')
			->addTag(EventsExtension::SUBSCRIBER_TAG);

		$builder->addDefinition($this->prefix('typeGuesser'))
			->setClass('Symfony\Component\Form\FormTypeGuesserInterface')
			->setFactory('Symfony\Bridge\Doctrine\Form\DoctrineOrmTypeGuesser')
			->addTag(FormsExtension::TAG_TYPE_GUESSER);
			
		$builder->addDefinition($this->prefix('initializer'))
			->setFactory('Symfony\Bridge\Doctrine\Validator\DoctrineInitializer')
			->addTag(ValidatorExtension::TAG_INITIALIZER);

		$builder->addDefinition($this->prefix('type.entity'))
			->setClass('Symfony\Bridge\Doctrine\Form\Type\EntityType')
			->addTag(FormsExtension::TAG_TYPE, 'entity')
			->setAutowired(FALSE);
	}

}

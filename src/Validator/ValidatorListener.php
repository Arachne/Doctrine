<?php

namespace Arachne\Doctrine\Validator;

use Arachne\Doctrine\Exception\EntityValidationException;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Michael Moravec
 */
class ValidatorListener implements Subscriber
{

	/** @var ValidatorInterface */
	private $validator;

	public function __construct(ValidatorInterface $validator)
	{
		$this->validator = $validator;
	}

	public function getSubscribedEvents()
	{
		return [
			Events::onFlush,
		];
	}

	public function onFlush(OnFlushEventArgs $args)
	{
		$uow = $args->getEntityManager()->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() as $entity) {
			$this->validateEntity($entity);
		}

		foreach ($uow->getScheduledEntityUpdates() as $entity) {
			$this->validateEntity($entity);
		}
	}

	protected function validateEntity($entity)
	{
		$violations = $this->validator->validate($entity);

		if ($violations->count() === 0) {
			return;
		}

		// taken from UnitOfWork::objToStr()
		$entityIdentifier = method_exists($entity, '__toString') ? (string) $entity : get_class($entity) . '@' . spl_object_hash($entity);

		throw new EntityValidationException('Entity ' . $entityIdentifier . ' is not valid: ' . $violations);
	}

}

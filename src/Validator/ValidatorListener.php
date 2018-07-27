<?php

declare(strict_types=1);

namespace Arachne\Doctrine\Validator;

use Arachne\Doctrine\Exception\EntityValidationException;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 * @author Michael Moravec
 */
class ValidatorListener implements EventSubscriber
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var string[]|null
     */
    private $groups;

    /**
     * @param string[]|null $groups
     */
    public function __construct(ValidatorInterface $validator, ?array $groups = null)
    {
        $this->validator = $validator;
        $this->groups = $groups;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * @throws EntityValidationException
     */
    public function onFlush(OnFlushEventArgs $event): void
    {
        $uow = $event->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->validateEntity($entity);
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $this->validateEntity($entity);
        }
    }

    /**
     * @param object $entity
     */
    private function validateEntity($entity): void
    {
        /** @var ConstraintViolationList $violations */
        $violations = $this->validator->validate($entity, null, $this->groups);

        if ($violations->count() === 0) {
            return;
        }

        // Copied from UnitOfWork::objToStr().
        $entityIdentifier = method_exists($entity, '__toString') ? (string) $entity : get_class($entity).'@'.spl_object_hash($entity);

        throw new EntityValidationException(sprintf('Entity "%s" is not valid: %s', $entityIdentifier, (string) $violations));
    }
}

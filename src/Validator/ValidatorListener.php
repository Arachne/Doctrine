<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\Validator;

use Arachne\Doctrine\Exception\EntityValidationException;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 * @author Michael Moravec
 */
class ValidatorListener implements Subscriber
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var string[]|null
     */
    private $groups;

    public function __construct(ValidatorInterface $validator, array $groups = null)
    {
        $this->validator = $validator;
        $this->groups = $groups;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    /**
     * @throws EntityValidationException
     */
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
        $violations = $this->validator->validate($entity, null, $this->groups);

        if (!$violations->count()) {
            return;
        }

        // Copied from UnitOfWork::objToStr().
        $entityIdentifier = method_exists($entity, '__toString') ? (string) $entity : get_class($entity).'@'.spl_object_hash($entity);

        throw new EntityValidationException('Entity '.$entityIdentifier.' is not valid: '.$violations);
    }
}

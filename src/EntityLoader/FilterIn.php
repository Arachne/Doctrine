<?php

declare(strict_types=1);

namespace Arachne\Doctrine\EntityLoader;

use Arachne\EntityLoader\FilterInInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterIn implements FilterInInterface
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var EntityRepository[]
     */
    private $repositories;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $type): bool
    {
        $manager = $this->managerRegistry->getManagerForClass($type);

        if (!$manager) {
            return false;
        }

        $repository = $manager->getRepository($type);
        if (!$repository instanceof EntityRepository) {
            return false;
        }

        $this->repositories[$type] = $repository;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function filterIn($value, string $type)
    {
        $repository = $this->repositories[$type];

        if (!is_object($value)) {
            $entity = $repository->find($value);
        } elseif ($value instanceof QueryInterface) {
            $entity = $value->getEntity($repository);
        }

        $class = $repository->getClassName();
        if (!isset($entity) || !$entity instanceof $class) {
            throw new BadRequestException(sprintf('Desired entity of type "%s" could not be found.', $repository->getClassName()));
        }

        return $entity;
    }
}

<?php

namespace Arachne\Doctrine\EntityLoader;

use Arachne\EntityLoader\FilterInInterface;
use Doctrine\ORM\EntityRepository;
use Nette\Application\BadRequestException;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterIn implements FilterInInterface
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param mixed $value
     *
     * @throws BadRequestException
     *
     * @return object
     */
    public function filterIn($value)
    {
        if (!is_object($value)) {
            $entity = $this->repository->find($value);
        } elseif ($value instanceof QueryInterface) {
            $entity = $value->getEntity($this->repository);
        }
        $class = $this->repository->getClassName();
        if (!$entity instanceof $class) {
            throw new BadRequestException(sprintf('Desired entity of type "%s" could not be found.', $this->repository->getClassName()));
        }

        return $entity;
    }
}

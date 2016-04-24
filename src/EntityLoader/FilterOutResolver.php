<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\EntityLoader;

use Arachne\DIHelpers\ResolverInterface;
use Arachne\Doctrine\Exception\NotImplementedException;
use Doctrine\Common\Persistence\ManagerRegistry;
use IteratorAggregate;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterOutResolver extends Object implements IteratorAggregate, ResolverInterface
{
    /** @var ResolverInterface */
    private $resolver;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var FilterOut[] */
    private $filters;

    public function __construct(ResolverInterface $resolver, ManagerRegistry $managerRegistry)
    {
        $this->resolver = $resolver;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $name
     * @return object
     */
    public function resolve($name)
    {
        return $this->resolver->resolve($name) ?: (isset($this->filters[$name]) ? $this->filters[$name] : $this->filters[$name] = $this->create($name));
    }

    /**
     * @param string $type
     * @return FilterOut|null
     */
    private function create($type)
    {
        $manager = $this->managerRegistry->getManagerForClass($type);
        if ($manager) {
            return new FilterOut($manager->getClassMetadata($type)->getSingleIdentifierFieldName());
        }
    }

    public function getIterator()
    {
        throw new NotImplementedException();
    }
}

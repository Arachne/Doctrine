<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\EntityLoader;

use Arachne\DIHelpers\ResolverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterOutResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FilterOut[]
     */
    private $filters;

    public function __construct(ResolverInterface $resolver, ManagerRegistry $managerRegistry)
    {
        $this->resolver = $resolver;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $name
     *
     * @return FilterOut|null
     */
    public function resolve($name)
    {
        return $this->resolver->resolve($name) ?: (isset($this->filters[$name]) ? $this->filters[$name] : $this->filters[$name] = $this->create($name));
    }

    /**
     * @param string $type
     *
     * @return FilterOut|null
     */
    private function create($type)
    {
        $manager = $this->managerRegistry->getManagerForClass($type);
        if (!$manager) {
            return;
        }

        $fields = $manager->getClassMetadata($type)->getIdentifierFieldNames();
        if (count($fields) !== 1 || !isset($fields[0])) {
            return;
        }

        return new FilterOut($fields[0]);
    }
}

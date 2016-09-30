<?php

/*
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterInResolver
{
    /**
     * @var callable
     */
    private $resolver;

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var FilterIn[]
     */
    private $filters;

    public function __construct(callable $resolver, ManagerRegistry $managerRegistry)
    {
        $this->resolver = $resolver;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $name
     *
     * @return FilterIn|null
     */
    public function __invoke($name)
    {
        return call_user_func($this->resolver, $name)
            ?: (isset($this->filters[$name]) ? $this->filters[$name] : $this->filters[$name] = $this->create($name));
    }

    /**
     * @param string $type
     *
     * @return FilterIn|null
     */
    private function create($type)
    {
        $manager = $this->managerRegistry->getManagerForClass($type);
        if ($manager) {
            return new FilterIn($manager->getRepository($type));
        }
    }
}

<?php

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\ORM\EntityRepository;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface QueryInterface
{
    /**
     * @param EntityRepository $repository
     *
     * @return object
     */
    public function getEntity(EntityRepository $repository);
}

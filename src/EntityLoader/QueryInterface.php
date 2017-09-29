<?php

declare(strict_types=1);

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\ORM\EntityRepository;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface QueryInterface
{
    /**
     * @return object
     */
    public function getEntity(EntityRepository $repository);
}

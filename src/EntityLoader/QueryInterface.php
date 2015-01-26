<?php

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\ORM\EntityRepository;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
interface QueryInterface
{

	public function getEntity(EntityRepository $repository);

}

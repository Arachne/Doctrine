<?php

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\ORM\EntityRepository;

interface QueryInterface
{

	public function getEntity(EntityRepository $repository);

}

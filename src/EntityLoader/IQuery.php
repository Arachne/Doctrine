<?php

namespace Arachne\Doctrine\EntityLoader;

use Doctrine\ORM\EntityRepository;

interface IQuery
{

	public function getEntity(EntityRepository $dao);

}

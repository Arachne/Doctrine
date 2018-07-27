<?php

declare(strict_types=1);

namespace Tests\Functional\Fixtures;

use Arachne\Doctrine\EntityLoader\QueryInterface;
use Doctrine\ORM\EntityRepository;
use Exception;

class ArticleQuery implements QueryInterface
{
    public function getEntity(EntityRepository $repository): Article
    {
        try {
            $qb = $repository->createQueryBuilder('a');

            return $qb->where($qb->expr()->like('a.name', ':name'))
                ->setParameter('name', '%ipsum%')
                ->getQuery()
                ->getSingleResult();
        } catch (Exception $e) {
        }
    }
}

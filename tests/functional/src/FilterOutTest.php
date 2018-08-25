<?php

declare(strict_types=1);

namespace Tests\Functional;

use Arachne\Doctrine\Exception\InvalidArgumentException;
use Arachne\EntityLoader\EntityUnloader;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Functional\Fixtures\Article;
use Tests\Functional\Fixtures\Page;

class FilterOutTest extends DatabaseTest
{
    public function testId(): void
    {
        $em = $this->tester->grabService(EntityManagerInterface::class);
        $article = $em->find(Article::class, 1);
        assert($article instanceof Article);
        $entityUnloader = $this->tester->grabService(EntityUnloader::class);
        $id = $entityUnloader->filterOut($article);
        self::assertSame($id, '1');
    }

    public function testProxy(): void
    {
        $em = $this->tester->grabService(EntityManagerInterface::class);

        $em->getProxyFactory()->generateProxyClasses($em->getMetadataFactory()->getAllMetadata());

        $page = $em->createQueryBuilder()
            ->select('p')
            ->from(Page::class, 'p')
            ->where('p.id = :id')
            ->setParameter('id', 1)
            ->getQuery()
            ->getSingleResult();

        $article = $page->getArticle();
        self::assertInstanceOf(Article::class, $article);
        self::assertNotEquals(Article::class, get_class($article));

        $entityUnloader = $this->tester->grabService(EntityUnloader::class);
        $id = $entityUnloader->filterOut($article);
        self::assertSame($id, '1');
    }

    public function testError(): void
    {
        $entityUnloader = $this->tester->grabService(EntityUnloader::class);
        try {
            $entityUnloader->filterOut(new Article());
            self::fail();
        } catch (InvalidArgumentException $e) {
            self::assertSame('Missing value for identifier field "id".', $e->getMessage());
        }
    }
}

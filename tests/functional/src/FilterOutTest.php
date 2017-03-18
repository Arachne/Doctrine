<?php

namespace Tests\Functional;

use Arachne\EntityLoader\EntityUnloader;
use Codeception\Test\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Tests\Functional\Fixtures\Article;
use Tests\Functional\Fixtures\Page;

class FilterOutTest extends Unit
{
    public function testId()
    {
        $em = $this->tester->grabService(EntityManagerInterface::class);
        $article = $em->find(Article::class, 1);
        $this->assertInstanceOf(Article::class, $article);
        $entityUnloader = $this->tester->grabService(EntityUnloader::class);
        $id = $entityUnloader->filterOut($article);
        $this->assertSame($id, '1');
    }

    public function testProxy()
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
        $this->assertInstanceOf(Article::class, $article);
        $this->assertNotEquals(Article::class, get_class($article));

        $entityUnloader = $this->tester->grabService(EntityUnloader::class);
        $id = $entityUnloader->filterOut($article);
        $this->assertSame($id, '1');
    }

    /**
     * @expectedException \Arachne\Doctrine\Exception\InvalidArgumentException
     * @expectedExceptionMessage Missing value for identifier field "id".
     */
    public function testError()
    {
        $entityUnloader = $this->tester->grabService(EntityUnloader::class);
        $entityUnloader->filterOut(new Article());
    }
}

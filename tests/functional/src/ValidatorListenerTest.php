<?php

namespace Tests\Functional;

use Codeception\Test\Unit;
use Doctrine\ORM\EntityManager;
use Tests\Functional\Fixtures\Article;

class ValidatorListenerTest extends Unit
{
    public function testFlushInsertSuccess()
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('FooBar');
        $em->persist($article);
        $em->flush();
    }

    /**
     * @expectedException \Arachne\Doctrine\Exception\EntityValidationException
     * @expectedExceptionMessageRegExp ~^Entity "Tests\\Functional\\Fixtures\\Article@[a-z0-9]++" is not valid: Object\(Tests\\Functional\\Fixtures\\Article\)\.name:\s++This value should not be blank\. \(code [a-z0-9-]++\)$~
     */
    public function testFlushInsertException()
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('');
        $em->persist($article);
        $em->flush();
    }

    public function testFlushUpdateSuccess()
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('FooBar');
        $em->persist($article);
        $em->flush();
        $article->setName('BarFoo');
        $em->flush();
    }

    /**
     * @expectedException \Arachne\Doctrine\Exception\EntityValidationException
     * @expectedExceptionMessageRegExp ~^Entity "Tests\\Functional\\Fixtures\\Article@[a-z0-9]++" is not valid: Object\(Tests\\Functional\\Fixtures\\Article\)\.name:\s++This value should not be blank\. \(code [a-z0-9-]++\)$~
     */
    public function testFlushUpdateException()
    {
        $em = $this->tester->grabService(EntityManager::class);
        $article = new Article();
        $article->setName('FooBar');
        $em->persist($article);
        $em->flush();
        $article->setName('');
        $em->flush();
    }
}

<?php

namespace Tests\Functional\Fixtures;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToOne;
use Kdyby\Doctrine\Entities\Attributes\Identifier;

/**
 * @Entity()
 */
class Article
{
    use Identifier;

    /**
     * @Column()
     *
     * @var string
     */
    private $name;

    /**
     * @OneToOne(targetEntity="Page", mappedBy="article")
     *
     * @var Page
     */
    private $page;
}

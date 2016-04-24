<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Doctrine\EntityLoader;

use Arachne\Doctrine\Exception\InvalidArgumentException;
use Arachne\EntityLoader\FilterOutInterface;
use Nette\Object;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterOut extends Object implements FilterOutInterface
{
    /** @var string */
    private $field;

    /**
     * @param string $field
     */
    public function __construct($field)
    {
        $this->field = $field;
    }

    /**
     * @param object $entity
     * @return string
     */
    public function filterOut($entity)
    {
        $id = $entity->{'get' . $this->field}();
        if ($id === null) {
            throw new InvalidArgumentException("Missing value for identifier field '$this->field'.");
        }
        return (string) $id;
    }
}

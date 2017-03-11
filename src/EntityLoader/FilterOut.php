<?php

namespace Arachne\Doctrine\EntityLoader;

use Arachne\Doctrine\Exception\InvalidArgumentException;
use Arachne\EntityLoader\FilterOutInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class FilterOut implements FilterOutInterface
{
    /**
     * @var string
     */
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
     *
     * @return string
     */
    public function filterOut($entity)
    {
        $id = $entity->{'get'.$this->field}();
        if ($id === null) {
            throw new InvalidArgumentException("Missing value for identifier field '$this->field'.");
        }

        return (string) $id;
    }
}

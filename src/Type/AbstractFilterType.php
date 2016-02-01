<?php

namespace Fludio\DoctrineFilter\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Fludio\DoctrineFilter\FilterBuilder;

abstract class AbstractFilterType
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $field;
    /**
     * @var array
     */
    protected $options;

    public function __construct($name, array $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->field = isset($options['field']) ? $options['field'] : $name;
    }

    /**
     * @param FilterBuilder $filterBuilder
     * @param $value
     * @return QueryBuilder
     */
    abstract public function expand(FilterBuilder $filterBuilder, $value, $table);

    /**
     * @param ArrayCollection $filters
     */
    public function addToFilters(ArrayCollection $filters)
    {
        $filters->set($this->name, $this);
    }

    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    protected function getFieldOnTable()
    {
        $fields = preg_split('/\./', $this->field);
        return array_pop($fields);
    }
}
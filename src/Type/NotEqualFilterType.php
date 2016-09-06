<?php

namespace BiteCodes\DoctrineFilter\Type;

use BiteCodes\DoctrineFilter\FilterBuilder;

class NotEqualFilterType extends AbstractFilterType
{
    public function expand(FilterBuilder $filterBuilder, $value, $table, $field, $where)
    {
        $qb = $filterBuilder->getQueryBuilder();

        return $this->add($qb, $where, $qb->expr()->neq($table . '.' . $field, $filterBuilder->placeValue($value)));
    }
}

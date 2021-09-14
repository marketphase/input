<?php

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\Constraint\DateTime;
use Linio\Component\Input\Transformer\DateTimeTransformer;

class DateTimeNode extends BaseNode
{
    public function __construct()
    {
        parent::__construct();
        $this->addConstraint(new DateTime());
        $this->transformer = new DateTimeTransformer();
    }
}

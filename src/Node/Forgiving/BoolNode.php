<?php

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\Constraint\Type;

class BoolNode extends BaseNode
{
    public function __construct()
    {
        parent::__construct();
        $this->addConstraint(new Type('bool'));
    }
}

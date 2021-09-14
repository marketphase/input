<?php

declare(strict_types=1);

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\Constraint\Type;

class StringNode extends BaseNode
{
    public function __construct()
    {
        parent::__construct();
        $this->addConstraint(new Type('string'));
    }
}

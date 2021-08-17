<?php

namespace Linio\Component\Input\Node\Forgiving;

use Linio\Component\Input\Instantiator\SetInstantiator;
use Linio\Component\Input\Node\BaseNode;
use Linio\Component\Input\Node\CollectionNode;
use Linio\Component\Input\Node\ObjectNode;
use Linio\Component\Input\Node\ScalarCollectionNode;

class TypeHandler extends \Linio\Component\Input\TypeHandler
{
    public function __construct()
    {
        $this->types = [
            'bool' => BoolNode::class,
            'int' => IntNode::class,
            'float' => FloatNode::class,
            'double' => FloatNode::class,
            'numeric' => NumericNode::class,
            'string' => StringNode::class,
            'array' => BaseNode::class,
            'object' => ObjectNode::class,
            'datetime' => DateTimeNode::class,
        ];

        $this->defaultInstantiator = new SetInstantiator();
    }

    public function getType(string $name): BaseNode
    {
        if (isset($this->types[$name])) {
            $type = new $this->types[$name]();
            $type->setTypeAlias($name);
            $type->setTypeHandler($this);

            return $type;
        }

        if ($this->isScalarCollectionType($name)) {
            $type = new ScalarCollectionNode();
            $type->setType($this->getCollectionType($name));
            $type->setTypeAlias($name);
            $type->setTypeHandler($this);

            return $type;
        }

        if ($this->isClassType($name)) {
            $type = new \Linio\Component\Input\Node\Forgiving\ObjectNode();
            $type->setType($name);
            $type->setTypeAlias('object');
            $type->setTypeHandler($this);
            $type->setInstantiator($this->defaultInstantiator);

            return $type;
        }

        if ($this->isCollectionType($name)) {
            $type = new CollectionNode();
            $type->setType($this->getCollectionType($name));
            $type->setTypeAlias('object[]');
            $type->setTypeHandler($this);
            $type->setInstantiator($this->defaultInstantiator);

            return $type;
        }

        throw new \InvalidArgumentException('Unknown type name: ' . $name);
    }
}

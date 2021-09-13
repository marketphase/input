<?php


namespace Linio\Component\Input\Node\Forgiving;


use Linio\Component\Input\CollectionItemMissing;
use Linio\Component\Input\ForgivingInputHandler\CEO;
use Linio\Component\Input\Instantiator\SetInstantiator;
use PHPUnit\Framework\TestCase;

class CollectionNodeTest extends TestCase
{
    public function testResultContainsMissing()
    {
        $node = new CollectionNode("is required");
        $node->setTypeHandler(new TypeHandler());
        $node->setType(CEO::class);
        $node->setInstantiator(new SetInstantiator());
        $node->add('firstName', 'string');
        $node->add('lastName', 'string');
        $result = $node->getValue('ceos', $node->walk([
            [
                'firstName' => 'John',
                'lastName' => 'Doe'
            ],
            [
                'firstName' => 'Jane',
            ]
        ]));
        $this->assertIsArray($result);
        $this->assertEquals(2, sizeof($result));
        $this->assertInstanceOf(CEO::class, $result[0]);
        $this->assertInstanceOf(CollectionItemMissing::class, $result[1]['lastName']);
    }
}

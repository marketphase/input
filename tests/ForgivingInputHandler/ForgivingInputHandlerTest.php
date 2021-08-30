<?php

namespace Linio\Component\Input\ForgivingInputHandler;

use Linio\Component\Input\ForgivingInputHandler;
use Linio\Component\Input\Invalid;
use Linio\Component\Input\Missing;
use PHPUnit\Framework\TestCase;

class ForgivingInputHandlerTest extends TestCase
{
    public function testNestedErrorIsFound()
    {

        $inputHandler = new NestedInputHandler();
        $inputHandler->bind([
            'key1' => 'value1',
            'key2' => [
                'child1' => 'childVal1',
                'child2' => 'childVal2',
            ],
            'key3' => [
                'child3' => [
                    'child1' => 'childVal1',
//                    'child2' => 'childVal2',
                ],
                'child4' => 'childVal4',
            ],
        ]);
        self::assertFalse($inputHandler->hasErrorFor('key1'));
        self::assertFalse($inputHandler->hasErrorFor('key2'));
        self::assertTrue($inputHandler->hasErrorFor('key3'));
    }

    public function testAllowsIncompleteInput()
    {
        $inputHandler = new class () extends ForgivingInputHandler {
            public function define()
            {
                $this->add('key1', 'string');
                $this->add('key2', 'string');
            }
        };
        $inputHandler->bind(['key1' => 'value1']);
        $this->assertTrue($inputHandler->hasData('key1'));
        $this->assertFalse($inputHandler->hasData('key2'));
        $this->assertTrue($inputHandler->hasErrorFor('key2'));
    }

    public function testAllowsPartiallyInvalidInput()
    {
        $inputHandler = new class () extends ForgivingInputHandler {
            public function define()
            {
                $this->add('key1', 'string');
                $this->add('key2', 'string');
            }
        };
        $inputHandler->bind(['key1' => 'value1', 'key2' => 0]);
        $this->assertTrue($inputHandler->hasData('key1'));
        $this->assertFalse($inputHandler->hasData('key2'));
        $this->assertTrue($inputHandler->hasErrorFor('key2'));
    }

    public function testReturnsMissing()
    {
        $inputHandler = new class () extends ForgivingInputHandler {
            public function define()
            {
                $this->add('key1', 'string');
                $this->add('key2', 'string');
            }
        };
        $inputHandler->bind(['key1' => 'value1']);
        $error = $inputHandler->getErrorFor('key2');
        $this->assertNotNull($error);
        $this->assertInstanceOf(Missing::class, $error);
        $this->assertEquals('key2', $error->field);
    }

    public function testReturnsInvalid()
    {
        $inputHandler = new class () extends ForgivingInputHandler {
            public function define()
            {
                $this->add('key1', 'string');
                $this->add('key2', 'string');
            }
        };
        $inputHandler->bind(['key1' => 'value1', 'key2' => 0]);
        $error = $inputHandler->getErrorFor('key2');
        $this->assertNotNull($error);
        $this->assertInstanceOf(Invalid::class, $error);
        $this->assertEquals('key2', $error->field);
    }

    public function testReasonString()
    {
        $inputHandler = new class () extends ForgivingInputHandler {
            public function define()
            {
                $this->add('key1', 'string');
                $this->add('key2', 'string');
            }
        };
        $inputHandler->bind(['key1' => 'value1', 'key2' => 0]);
        $reasonString = $inputHandler->getErrorFor('key2')->getReason();
        self::assertEquals('[key2] Value does not match type: string', $reasonString);
    }

    public function testReasonArray()
    {
        $inputHandler = new NestedInputHandler();
        $inputHandler->bind([
            'key1' => 'value1',
            'key2' => [
                'child1' => 'childVal1',
                'child2' => 'childVal2',
            ],
            'key3' => [
                'child3' => [
                    'child1' => 'childVal1',
//                    'child2' => 'childVal2',
                ],
                'child4' => 'childVal4',
            ],
        ]);
        $reasonArray = $inputHandler->getErrorFor('key3')->getReason();
        self::assertEquals(['child3' => ['child2' => 'We need input for a second child']], $reasonArray);
    }
}

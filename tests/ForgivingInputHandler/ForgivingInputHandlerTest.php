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
        $inputHandler = new CompanyInputHandler();
        $inputHandler->bind([
            'action' => 'found',
            'ceo' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            'company' => [
                'ceo' => [
                    'firstName' => 'John',
                ],
                'branch' => 'IT',
            ],
        ]);
        $this->assertTrue($inputHandler->hasErrorFor('company'));
        $reasonArray = $inputHandler->getErrorFor('company')->getReason();
        $this->assertEquals(['ceo' => ['lastName' => 'We need input for the last name of the CEO']], $reasonArray);
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
        $this->assertTrue($inputHandler->hasData('key1'), "Expected to have actual data for the first key");
        $this->assertFalse($inputHandler->hasData('key2'), "Unexpected data for the second key");
        $this->assertTrue($inputHandler->hasErrorFor('key2'), "Expected to have an error for the second key");
        $this->assertInstanceOf(Missing::class, $inputHandler->getErrorFor('key2'), "Expected the error for the second key to be Missing");
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
        $this->assertTrue($inputHandler->hasData('key1'), "Expected to have actual data for the first key");
        $this->assertFalse($inputHandler->hasData('key2'), "Unexpected data for the second key");
        $this->assertTrue($inputHandler->hasErrorFor('key2'), "Expected to have an error for the second key");
        $this->assertInstanceOf(Invalid::class, $inputHandler->getErrorFor('key2'), "Expected the error for the second key to be Invalid");
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
        $this->assertNotNull($error, "Expected to have an error for the second key");
        $this->assertInstanceOf(Missing::class, $error, "Expected the error for the second key to be Missing");
        $this->assertEquals('key2', $error->field, "Expected the error for the second key to mention the field name 'key2'");
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
        $this->assertNotNull($error, "Expected to have an error for the second key");
        $this->assertInstanceOf(Invalid::class, $error, "Expected the error for the second key to be Invalid");
        $this->assertEquals('key2', $error->field, "Expected the error for the second key to mention the field name 'key2'");
        $reasonString = $inputHandler->getErrorFor('key2')->getReason();
        $this->assertEquals(
            '[key2] Value does not match type: string',
            $reasonString,
            "Expected the reason string to be '[key2] Value does not match type: string', got $reasonString"
        );
    }

    public function testCollectionItemMissingErrorIsFound()
    {
        $inputHandler = new class () extends ForgivingInputHandler {
            public function define()
            {
                $ceos = $this->add('ceos', CEO::class . '[]');
                $ceos->add('firstName', 'string');
                $ceos->add('lastName', 'string');
            }
        };
        $input = [
            'ceos' =>
                [
                    [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                    ],
                    [
                        'firstName' => 'Jane',
                    ],
                    [
                        'firstName' => 'Jack',
                        'lastName' => 'Jones',
                    ],
                ]
        ];
        $inputHandler->bind($input);
        $errorForCeos = $inputHandler->getErrorFor('ceos');
        $this->assertInstanceOf(Invalid::class, $errorForCeos);
        $this->assertEquals('ceos', $errorForCeos->field);
        $this->assertEquals(
            [
                '1' =>
                    [
                        'lastName' => 'This field is required'
                    ]
            ],
            $errorForCeos->getReason()
        );
    }

    public function testCollectionItemInvalidIsFound()
    {
        $inputHandler = new class () extends ForgivingInputHandler {
            public function define()
            {
                $ceos = $this->add('ceos', CEO::class . '[]');
                $ceos->add('firstName', 'string');
                $ceos->add('lastName', 'string');
            }
        };
        $input = [
            'ceos' =>
                [
                    [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                    ],
                    [
                        'firstName' => 'Jane',
                        'lastName' => 403,
                    ],
                    [
                        'firstName' => 'Jack',
                        'lastName' => 'Jones',
                    ],
                ]
        ];
        $inputHandler->bind($input);
        $errorForCeos = $inputHandler->getErrorFor('ceos');
        $this->assertInstanceOf(Invalid::class, $errorForCeos);
        $this->assertEquals('ceos', $errorForCeos->field);
        $this->assertEquals(
            [
                '1' =>
                    [
                        'lastName' => '[lastName] Value does not match type: string'
                    ]
            ],
            $errorForCeos->getReason()
        );
    }
}

<?php

namespace Linio\Component\Input\ForgivingInputHandler;

use Linio\Component\Input\ForgivingInputHandler;

class CompanyInputHandler extends ForgivingInputHandler {
    public function define()
    {
        $this->add('action', 'string');
        $key2 = $this->add('ceo', CEO::class);
        $key2->add('firstName', 'string');
        $key2->add('lastName', 'string');

        $key3 = $this->add('company', Company::class);
        $child3 = $key3->add('ceo', CEO::class);
        $child3->add('firstName', 'string');
        $c2 = $child3->add('lastName', 'string');
        $c2->setFieldMissingMessage('We need input for the last name of the CEO');
        $key3->add('branch', 'string');
    }
}
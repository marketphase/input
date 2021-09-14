<?php

namespace Linio\Component\Input\ForgivingInputHandler;

class CEO {
    public string $firstName;
    public string $lastName;

    public function setFirstName(string $val)
    {
        $this->firstName = $val;
    }

    public function setLastName(string $val)
    {
        $this->lastName = $val;
    }
}
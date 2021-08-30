<?php

namespace Linio\Component\Input\ForgivingInputHandler;

class Company {
    public CEO $ceo;
    public string $tradeName;

    public function setCeo(CEO $val)
    {
        $this->ceo = $val;
    }

    public function setTradeName(string $val)
    {
        $this->tradeName = $val;
    }
}
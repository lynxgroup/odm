<?php namespace LynxGroup\Contracts\Odm;

interface Odm
{
    public function __construct($lock_path, array $repositories);

    public function __destruct();

    public function __get($name);

    public function flash();

    public function drop();
}

<?php namespace LynxGroup\Component\Odm;

use LynxGroup\Contracts\Odm\Odm as OdmInterface;

use LynxGroup\Contracts\Odm\Repository;

class Odm implements OdmInterface
{
    protected $handler;

    protected $repositories = [];

    public function __construct($lock_path, array $repositories)
    {
		$umask = umask(0);

        if( !is_dir(dirname($lock_path)) )
        {
            mkdir(dirname($lock_path), 0777, true);
        }

        $this->handler = fopen($lock_path, "c+");

        flock($this->handler, LOCK_EX);

        $this->repositories = $repositories;

		umask($umask);
    }

    public function __destruct()
    {
        flock($this->handler, LOCK_UN);

        fclose($this->handler);
    }

    public function __get($name)
    {
        if( !($this->repositories[$name] instanceof Repository) )
        {
            $this->repositories[$name] = $this->repositories[$name]($this);
        }

        return $this->repositories[$name];
    }

    public function flash()
    {
        foreach( $this->repositories as $repository )
        {
            if( $repository instanceof Repository )
            {
                $repository->flash();
            }
        }
    }

    public function drop()
    {
        foreach( $this->repositories as $name => $repository )
        {
            $this->$name->drop();
        }
    }
}

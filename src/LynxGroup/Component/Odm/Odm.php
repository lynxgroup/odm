<?php namespace LynxGroup\Component\Odm;

use LynxGroup\Component\Odm\Repository;

class Odm
{
	protected $handler;

	protected $repositories = [];

	public function __construct($lock_path, array $repositories)
	{
		if( !is_dir(dirname($lock_path)) )
		{
			mkdir(dirname($lock_path), 0777, true);
		}

		$this->handler = fopen($lock_path, "c+");

		flock($this->handler, LOCK_EX);

		$this->repositories = $repositories;
	}

	public function __destruct()
	{
		flock($this->handler, LOCK_UN);

		fclose($this->handler);
	}

	public function __get($name)
	{
		if( !($this->repositories[$name] instanceof \Component\Odm\Repository) )
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

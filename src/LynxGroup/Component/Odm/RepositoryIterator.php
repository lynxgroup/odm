<?php namespace LynxGroup\Component\Odm;

use LynxGroup\Contracts\Odm\RepositoryIterator as RepositoryIteratorInterface;

use LynxGroup\Contracts\Odm\Repository;

class RepositoryIterator implements \RepositoryIteratorInterface
{
	protected $repository;

	protected $id = 1;

	public function __construct(Repository $repository)
	{
		$this->repository = $repository;
	}

	public function rewind()
	{
		$this->id = 1;
	}

	public function next()
	{
		$this->id++;
	}

	public function key()
	{
		return $this->id;
	}

	public function current()
	{
		return $this->repository->read($this->id);
	}

	public function valid()
	{
		return $this->id <= $this->repository->getLastId();
	}
}

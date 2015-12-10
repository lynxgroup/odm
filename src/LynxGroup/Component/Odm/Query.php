<?php namespace LynxGroup\Component\Odm;

use LynxGroup\Contracts\Odm\Query as QueryInterface;

use LynxGroup\Contracts\Odm\Repository;
use LynxGroup\Contracts\Odm\RepositoryIterator;

class Query implements \IteratorAggregate
{
	protected $repository;

	protected $class;

	protected $deleted = false;

	protected $filters = [];

	protected $orders = [];

	protected $offset = 0;

	protected $count = -1;

	public function __construct(Repository $repository)
	{
		$this->repository = $repository;
	}

	public function kindof($class)
	{
		$this->class = $class;

		return $this;
	}

	public function deleted($deleted = true)
	{
		$this->deleted = $deleted;

		return $this;
	}

	public function filter(callable $filter)
	{
		$this->filters[] = $filter;

		return $this;
	}

	public function criteria($field, $sign, $value)
	{
		return $this->filter(function($doc) use($field, $sign, $value)
		{
			return eval('return $doc->get'.ucfirst($field).'() '.$sign.' '.$value.';');
		});
	}

	public function where($field, $value = true)
	{
		return $this->filter(function($doc) use($field, $value)
		{
			return $doc->{'get'.ucfirst($field)}() == $value;
		});
	}

	public function except($field, $value = true)
	{
		return $this->filter(function($doc) use($field, $value)
		{
			return $doc->{'get'.ucfirst($field)}() != $value;
		});
	}

	public function in($field, array $matches)
	{
		return $this->filter(function($doc) use($field, $matches)
		{
			return in_array($doc->{'get'.ucfirst($field)}(), $matches);
		});
	}

	public function cmp($field, $value)
	{
		return $this->filter(function($doc) use($field, $value)
		{
			return $doc->{'cmp'.ucfirst($field)}($value);
		});
	}

	public function order(callable $order)
	{
		$this->orders[] = $order;

		return $this;
	}

	public function orderBy($field)
	{
		return $this->order(function($doc1, $doc2) use($field)
		{
			return $doc1->{'order'.ucfirst($field)}($doc2);
		});
	}

	public function limit($offset = 0, $count = -1)
	{
		$this->offset = $offset;

		$this->count = $count;

		return $this;
	}

	public function getIterator()
	{
		$iterator = new RepositoryIterator($this->repository);

		if( $this->class )
		{
			$iterator = new \CallbackFilterIterator($iterator, function($document)
			{
				return $document instanceof $this->class;
			});
		}

		if( $this->deleted !== null )
		{
			$iterator = new \CallbackFilterIterator($iterator, function($document)
			{
				return $this->deleted === $document->getDeleted();
			});
		}

		foreach( $this->filters as $filter )
		{
			$iterator = new \CallbackFilterIterator($iterator, $filter);
		}

		if( count($this->orders) )
		{
			$array = iterator_to_array($iterator);

			foreach( $this->orders as $order )
			{
				uasort($array, $order);
			}

			$iterator = new \ArrayIterator($array);
		}

		$iterator = new \LimitIterator($iterator, $this->offset, $this->count);

		return new \CallbackFilterIterator($iterator, function($document)
		{
			$this->repository->attach($document);

			return true;
		});
	}

	public function find()
	{
		foreach( $this as $document ) return $document;
	}
}

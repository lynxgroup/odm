<?php namespace LynxGroup\Contracts\Odm;

use LynxGroup\Contracts\Odm\Repository;
//use LynxGroup\Component\Odm\RepositoryIterator;

interface Query implements \IteratorAggregate
{
	public function __construct(Repository $repository);

	public function kindof($class);

	public function deleted($deleted = true);

	public function filter(callable $filter);

	public function criteria($field, $sign, $value);

	public function where($field, $value = true);

	public function except($field, $value = true);

	public function in($field, array $matches);

	public function cmp($field, $value);

	public function order(callable $order);

	public function orderBy($field);

	public function limit($offset = 0, $count = -1);

	public function find();
}

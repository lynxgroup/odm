<?php namespace LynxGroup\Contracts\Odm;

use LynxGroup\Contracts\Odm\Odm;
use LynxGroup\Contracts\Odm\Document;

interface Repository
{
	public function __construct(Odm $odm, $index_path, $create_path, $class, $globals_path);

	public function __destruct();

	public function attach(Document $document);

	public function getLastId();

	public function create($class = null);

	public function flash();

	protected function save(Document $document);

	public function load($id);

	public function read($id);

	public function query();

	public function drop();
}

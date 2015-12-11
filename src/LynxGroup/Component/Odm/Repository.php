<?php namespace LynxGroup\Component\Odm;

use LynxGroup\Contracts\Odm\Repository as RepositoryInterface;

use LynxGroup\Contracts\Odm\Odm as OdmRepository;

use LynxGroup\Contracts\Odm\Document;

class Repository implements RepositoryInterface
{
	const LENGTH = 512;

	protected $odm;

	protected $handler;

	protected $data_paths;

	protected $create_path;

	protected $class;

	protected $globals_path;

	protected $documents = [];

	protected $globals = [];

	public function __construct(OdmRepository $odm, $index_path, $create_path, $class, $globals_path)
	{
		$umask = umask(0);

		$this->odm = $odm;

		if( !is_dir(dirname($index_path)) )
		{
			mkdir(dirname($index_path), 0777, true);
		}

		$this->handler = fopen($index_path, "c+");

		$this->create_path = $create_path;

		if( !is_dir($this->create_path) )
		{
			mkdir($this->create_path, 0777, true);
		}

		$this->class = $class;

		$this->globals_path = $globals_path;

		if( !is_dir(dirname($this->globals_path)) )
		{
			mkdir(dirname($this->globals_path), 0777, true);
		}

		if( is_file($this->globals_path) )
		{
			$this->globals = json_decode(file_get_contents($this->globals_path), true);
		}

		umask($umask);
	}

	public function __destruct()
	{
		fclose($this->handler);
	}

	public function attach(Document $document)
	{
		if( isset($this->documents[$document->getId()]) && $this->documents[$document->getId()] !== $document )
		{
			throw new \OutOfRangeException($document->getId());
		}

		$this->documents[$document->getId()] = $document;
	}

	public function getLastId()
	{
		fseek($this->handler, 0, SEEK_END);

		return ftell($this->handler) / static::LENGTH;
	}

	public function create($class = null)
	{
		if( !$class )
		{
			$class = $this->class;
		}

		if( !is_a($class, $this->class, true) )
		{
			throw new \InvalidArgumentException($class);
		}

		$id = $this->getLastId() + 1;

		$now = date('Ymdhis');

		fprintf($this->handler, '%'.static::LENGTH.'s', 
			json_encode([
				$class, false, $now, $now, $this->create_path
			], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
		);

		$document = new $class($this->odm, $id, false, $now, $now, [], $this->create_path);

		$this->attach($document);

		return $document;
	}

	public function flash()
	{
		foreach( $this->documents as $document )
		{
			if( $document->isDirty() )
			{
				$this->save($document);

				$document->setDirty(false);
			}
		}

		file_put_contents(
			$this->globals_path,
			json_encode(
				$this->globals,
				JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
			)
		);
	}

	protected function save(Document $document)
	{
		fseek($this->handler, ($document->getId() - 1) * static::LENGTH);

		fprintf($this->handler, '%'.static::LENGTH.'s', 
			json_encode([
				get_class($document), $document->getDeleted(), $document->getCReated(), date('Ymdhis'), $document->getPath()
			], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
		);

		file_put_contents(
			"{$document->getPath()}/{$document->getId()}",
			json_encode(
				$document->getData(),
				JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE
			)
		);
	}

	public function load($id)
	{
		$document = $this->read($id);

		if( $document )
		{
			$this->attach($document);
		}

		return $document;
	}

	public function read($id)
	{
		if( $id < 1 || $id > $this->getLastId() )
		{
			throw new \RangeException($id);
		}

		if( isset($this->documents[$id]) )
		{
			return $this->documents[$id];
		}

		fseek($this->handler, ($id - 1) * static::LENGTH);

		list($class, $deleted, $created, $updated, $path) = json_decode(
			fread($this->handler, static::LENGTH)
		, true);

		$data = is_file("$path/$id") ? json_decode(file_get_contents("$path/$id"), true) : [];

		return new $class($this->odm, $id, $deleted, $created, $updated, $data, $path);
	}

	public function query()
	{
		return new Query($this);
	}

	public function drop()
	{
		foreach( $this->query()->deleted(null) as $document )
		{
			$path = "{$document->getPath()}/{$document->getId()}";

			if( is_file($path) )
			{
				unlink($path);
			}
		}

		ftruncate($this->handler, 0);

		$this->documents = [];
	}
}

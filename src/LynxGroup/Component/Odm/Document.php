<?php namespace Component\Odm;

use LynxGroup\Contracts\Odm\Document as DocumentInterface;

use LynxGroup\Component\Odm\Odm;

class Document implements DocumentInterface
{
	protected $odm;

	protected $id;

	protected $deleted;

	protected $created;

	protected $updated;

	protected $data;

	protected $path;

	protected $dirty;

	public function __construct(Odm $odm, $id, $deleted, $created, $updated, array $data, $path)
	{
		$this->odm = $odm;

		$this->id = $id;

		$this->deleted = $deleted;

		$this->created = $created;

		$this->updated = $updated;

		$this->data = $data;

		$this->path = $path;
	}

	public function getId()
	{
		return $this->id;
	}

	public function setDeleted($deleted = true)
	{
		$this->deleted = $deleted;

		return $this;
	}

	public function getDeleted()
	{
		return $this->deleted;
	}

	public function getData()
	{
		return $this->data;
	}

	public function setDirty($dirty = true)
	{
		$this->dirty = $dirty;

		return $this;
	}

	public function isDirty()
	{
		return $this->dirty;
	}

	public function getCreated()
	{
		return $this->created;
	}

	public function getUpdated()
	{
		return $this->updated;
	}

	public function getPath()
	{
		return $this->path;
	}
}

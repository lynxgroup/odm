<?php namespace LynxGroup\Contracts\Odm;

interface Document
{
	public function getId();

	public function setDeleted($deleted = true);

	public function getDeleted();

	public function getData();

	public function setDirty($dirty = true);

	public function isDirty();

	public function getCreated();

	public function getUpdated();

	public function getPath();
}

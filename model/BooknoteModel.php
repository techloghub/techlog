<?php
class BooknoteModel
{
	private $note_id;
	private $image_id;
	private $index_article_id;
	private $descs;
	private $inserttime;
	private $updatetime;

	public function __construct($params = array())
	{
		foreach (get_object_vars($this) as $key=>$value)
		{
			if ($key != $this->get_pri_key() && isset($params[$key]))
				$this->$key = $params[$key];
			else if (empty($this->$key))
				$this->$key = "";
		}
	}

	public function get_model_fields()
	{
		return array_keys(get_object_vars($this));
	}
	public function get_note_id()
	{
		return $this->note_id;
	}
	public function get_image_id()
	{
		return $this->image_id;
	}
	public function set_image_id($value)
	{
		$this->image_id = $value;
		return $this;
	}
	public function get_index_article_id()
	{
		return $this->index_article_id;
	}
	public function set_index_article_id($value)
	{
		$this->index_article_id = $value;
		return $this;
	}
	public function get_descs()
	{
		return $this->descs;
	}
	public function set_descs($value)
	{
		$this->descs = $value;
		return $this;
	}
	public function get_inserttime()
	{
		return $this->inserttime;
	}
	public function set_inserttime($value)
	{
		$this->inserttime = $value;
		return $this;
	}
	public function get_updatetime()
	{
		return $this->updatetime;
	}
	public function set_updatetime($value)
	{
		$this->updatetime = $value;
		return $this;
	}
	public function set($params)
	{
		foreach (get_object_vars($this) as $key=>$value)
		{
			if (isset($params[$key]))
				$this->$key = $params[$key];
		}
	}
	public function is_set_pri()
	{
		return !empty($this->note_id);
	}
	public function get_pri_key()
	{
		return "note_id";
	}
}
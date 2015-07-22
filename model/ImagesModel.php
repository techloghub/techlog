<?php
class ImagesModel
{
	private $image_id;
	private $md5;
	private $path;
	private $inserttime;
	private $category;

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
	public function get_image_id()
	{
		return $this->image_id;
	}
	public function get_md5()
	{
		return $this->md5;
	}
	public function set_md5($value)
	{
		$this->md5 = $value;
		return $this;
	}
	public function get_path()
	{
		return $this->path;
	}
	public function set_path($value)
	{
		$this->path = $value;
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
	public function get_category()
	{
		return $this->category;
	}
	public function set_category($value)
	{
		$this->category = $value;
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
		return !empty($this->image_id);
	}
	public function get_pri_key()
	{
		return "image_id";
	}
}
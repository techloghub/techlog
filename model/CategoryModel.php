<?php
class CategoryModel extends AbstractModel
{
	private $category_id;
	private $category;
	private $inserttime;

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
	public function get_category_id()
	{
		return $this->category_id;
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
	public function get_inserttime()
	{
		return $this->inserttime;
	}
	public function set_inserttime($value)
	{
		$this->inserttime = $value;
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
		return !empty($this->category_id);
	}
	public function get_pri_key()
	{
		return "category_id";
	}
}
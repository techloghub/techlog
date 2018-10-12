<?php
class ArticleTagRelationModel extends AbstractModel
{
	private $id;
	private $article_id;
	private $tag_id;
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
	public function get_id()
	{
		return $this->id;
	}
	public function get_article_id()
	{
		return $this->article_id;
	}
	public function set_article_id($value)
	{
		$this->article_id = $value;
		return $this;
	}
	public function get_tag_id()
	{
		return $this->tag_id;
	}
	public function set_tag_id($value)
	{
		$this->tag_id = $value;
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
		return !empty($this->id);
	}
	public function get_pri_key()
	{
		return "id";
	}
}
<?php
class ArticleModel
{
	private $article_id;
	private $title;
	private $inserttime;
	private $updatetime;
	private $indexs;
	private $category_id;
	private $title_desc;
	private $draft;
	private $access_count;
	private $online;

	public function __construct($params = array())
	{
		foreach (get_object_vars($this) as $key=>$value)
		{
			if (isset($params[$key]))
				$this->$key = $params[$key];
			else if (empty($this->$key))
				$this->$key = "";
		}
	}

	public function get_article_id()
	{
		return $this->article_id;
	}
	public function get_title()
	{
		return $this->title;
	}
	public function set_title($value)
	{
		$this->title = $value;
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
	public function get_indexs()
	{
		return $this->indexs;
	}
	public function set_indexs($value)
	{
		$this->indexs = $value;
		return $this;
	}
	public function get_category_id()
	{
		return $this->category_id;
	}
	public function set_category_id($value)
	{
		$this->category_id = $value;
		return $this;
	}
	public function get_title_desc()
	{
		return $this->title_desc;
	}
	public function set_title_desc($value)
	{
		$this->title_desc = $value;
		return $this;
	}
	public function get_draft()
	{
		return $this->draft;
	}
	public function set_draft($value)
	{
		$this->draft = $value;
		return $this;
	}
	public function get_access_count()
	{
		return $this->access_count;
	}
	public function set_access_count($value)
	{
		$this->access_count = $value;
		return $this;
	}
	public function get_online()
	{
		return $this->online;
	}
	public function set_online($value)
	{
		$this->online = $value;
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
		return !empty($this->article_id);
	}
}
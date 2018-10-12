<?php
class CommentModel extends AbstractModel
{
	private $comment_id;
	private $inserttime;
	private $article_id;
	private $email;
	private $qq;
	private $reply;
	private $online;
	private $nickname;
	private $floor;
	private $content;
	private $ip;
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
	public function get_comment_id()
	{
		return $this->comment_id;
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
	public function get_article_id()
	{
		return $this->article_id;
	}
	public function set_article_id($value)
	{
		$this->article_id = $value;
		return $this;
	}
	public function get_email()
	{
		return $this->email;
	}
	public function set_email($value)
	{
		$this->email = $value;
		return $this;
	}
	public function get_qq()
	{
		return $this->qq;
	}
	public function set_qq($value)
	{
		$this->qq = $value;
		return $this;
	}
	public function get_reply()
	{
		return $this->reply;
	}
	public function set_reply($value)
	{
		$this->reply = $value;
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
	public function get_nickname()
	{
		return $this->nickname;
	}
	public function set_nickname($value)
	{
		$this->nickname = $value;
		return $this;
	}
	public function get_floor()
	{
		return $this->floor;
	}
	public function set_floor($value)
	{
		$this->floor = $value;
		return $this;
	}
	public function get_content()
	{
		return $this->content;
	}
	public function set_content($value)
	{
		$this->content = $value;
		return $this;
	}
	public function get_ip()
	{
		return $this->ip;
	}
	public function set_ip($value)
	{
		$this->ip = $value;
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
		return !empty($this->comment_id);
	}
	public function get_pri_key()
	{
		return "comment_id";
	}
}
<?php
class StatsModel
{
	private $stats_id;
	private $time_str;
	private $remote_host;
	private $request;
	private $referer;
	private $user_agent;

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
	public function get_stats_id()
	{
		return $this->stats_id;
	}
	public function get_time_str()
	{
		return $this->time_str;
	}
	public function set_time_str($value)
	{
		$this->time_str = $value;
		return $this;
	}
	public function get_remote_host()
	{
		return $this->remote_host;
	}
	public function set_remote_host($value)
	{
		$this->remote_host = $value;
		return $this;
	}
	public function get_request()
	{
		return $this->request;
	}
	public function set_request($value)
	{
		$this->request = $value;
		return $this;
	}
	public function get_referer()
	{
		return $this->referer;
	}
	public function set_referer($value)
	{
		$this->referer = $value;
		return $this;
	}
	public function get_user_agent()
	{
		return $this->user_agent;
	}
	public function set_user_agent($value)
	{
		$this->user_agent = $value;
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
		return !empty($this->stats_id);
	}
	public function get_pri_key()
	{
		return "stats_id";
	}
}
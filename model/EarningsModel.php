<?php
class EarningsModel extends AbstractModel
{
	private $month;
	private $expend;
	private $income;
	private $image_id;
	private $earnings_id;
	private $inserttime;
	private $article_id;

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
	public function get_month()
	{
		return $this->month;
	}
	public function set_month($value)
	{
		$this->month = $value;
		return $this;
	}
	public function get_expend()
	{
		return $this->expend;
	}
	public function set_expend($value)
	{
		$this->expend = $value;
		return $this;
	}
	public function get_income()
	{
		return $this->income;
	}
	public function set_income($value)
	{
		$this->income = $value;
		return $this;
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
	public function get_earnings_id()
	{
		return $this->earnings_id;
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
		return !empty($this->earnings_id);
	}
	public function get_pri_key()
	{
		return "earnings_id";
	}
}
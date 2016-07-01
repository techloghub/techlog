<?php
class AccountModel
{
	private $id;
	private $name;
	private $esid;
	private $inserttime;
	private $updatetime;
	private $currency;
	private $orderno;
	private $category;
	private $money;
	private $cardno;

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
	public function get_name()
	{
		return $this->name;
	}
	public function set_name($value)
	{
		$this->name = $value;
		return $this;
	}
	public function get_esid()
	{
		return $this->esid;
	}
	public function set_esid($value)
	{
		$this->esid = $value;
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
	public function get_currency()
	{
		return $this->currency;
	}
	public function set_currency($value)
	{
		$this->currency = $value;
		return $this;
	}
	public function get_orderno()
	{
		return $this->orderno;
	}
	public function set_orderno($value)
	{
		$this->orderno = $value;
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
	public function get_money()
	{
		return $this->money;
	}
	public function set_money($value)
	{
		$this->money = $value;
		return $this;
	}
	public function get_cardno()
	{
		return $this->cardno;
	}
	public function set_cardno($value)
	{
		$this->cardno = $value;
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
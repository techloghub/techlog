<?php
class LedgersModel extends AbstractModel
{
	private $id;
	private $esid;
	private $date;
	private $inserttime;
	private $recType;
	private $tag;
	private $comment;
	private $type;
	private $fromAcc;
	private $toAcc;
	private $money;
	private $currency;
	private $category;
	private $subcategory;

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
	public function get_esid()
	{
		return $this->esid;
	}
	public function set_esid($value)
	{
		$this->esid = $value;
		return $this;
	}
	public function get_date()
	{
		return $this->date;
	}
	public function set_date($value)
	{
		$this->date = $value;
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
	public function get_recType()
	{
		return $this->recType;
	}
	public function set_recType($value)
	{
		$this->recType = $value;
		return $this;
	}
	public function get_tag()
	{
		return $this->tag;
	}
	public function set_tag($value)
	{
		$this->tag = $value;
		return $this;
	}
	public function get_comment()
	{
		return $this->comment;
	}
	public function set_comment($value)
	{
		$this->comment = $value;
		return $this;
	}
	public function get_type()
	{
		return $this->type;
	}
	public function set_type($value)
	{
		$this->type = $value;
		return $this;
	}
	public function get_fromAcc()
	{
		return $this->fromAcc;
	}
	public function set_fromAcc($value)
	{
		$this->fromAcc = $value;
		return $this;
	}
	public function get_toAcc()
	{
		return $this->toAcc;
	}
	public function set_toAcc($value)
	{
		$this->toAcc = $value;
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
	public function get_currency()
	{
		return $this->currency;
	}
	public function set_currency($value)
	{
		$this->currency = $value;
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
	public function get_subcategory()
	{
		return $this->subcategory;
	}
	public function set_subcategory($value)
	{
		$this->subcategory = $value;
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
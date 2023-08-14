<?php
class CalendarAlertModel extends AbstractModel
{
	private $id;
	private $name;
	private $insert_time;
	private $update_time;
	private $start_time;
	private $end_time;
	private $alert_time;
	private $status;
	private $lunar;
	private $cycle_type;
	private $period;
	private $category;
	private $remark;
	private $next_time;

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
	public function get_insert_time()
	{
		return $this->insert_time;
	}
	public function set_insert_time($value)
	{
		$this->insert_time = $value;
		return $this;
	}
	public function get_update_time()
	{
		return $this->update_time;
	}
	public function set_update_time($value)
	{
		$this->update_time = $value;
		return $this;
	}
	public function get_start_time()
	{
		return $this->start_time;
	}
	public function set_start_time($value)
	{
		$this->start_time = $value;
		return $this;
	}
	public function get_end_time()
	{
		return $this->end_time;
	}
	public function set_end_time($value)
	{
		$this->end_time = $value;
		return $this;
	}
	public function get_alert_time()
	{
		return $this->alert_time;
	}
	public function set_alert_time($value)
	{
		$this->alert_time = $value;
		return $this;
	}
	public function get_status()
	{
		return $this->status;
	}
	public function set_status($value)
	{
		$this->status = $value;
		return $this;
	}
	public function get_lunar()
	{
		return $this->lunar;
	}
	public function set_lunar($value)
	{
		$this->lunar = $value;
		return $this;
	}
	public function get_cycle_type()
	{
		return $this->cycle_type;
	}
	public function set_cycle_type($value)
	{
		$this->cycle_type = $value;
		return $this;
	}
	public function get_period()
	{
		return $this->period;
	}
	public function set_period($value)
	{
		$this->period = $value;
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
	public function get_remark()
	{
		return $this->remark;
	}
	public function set_remark($value)
	{
		$this->remark = $value;
		return $this;
	}
	public function get_next_time()
	{
		return $this->next_time;
	}
	public function set_next_time($value)
	{
		$this->next_time = $value;
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

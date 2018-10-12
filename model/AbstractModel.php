<?php
abstract class AbstractModel
{
    abstract function is_set_pri();
    abstract function get_model_fields();
    abstract function get_pri_key();
}
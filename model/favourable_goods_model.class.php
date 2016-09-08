<?php
defined('IN_ECJIA') or exit('No permission resources.');

class favourable_goods_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'goods';
		parent::__construct();
	}
	
	public function goods_select($where, $in=false, $field='*') {
		if ($in) {
			return $this->field($field)->in($where)->select();
		}
		return $this->field($field)->where($where)->select();
	}
}
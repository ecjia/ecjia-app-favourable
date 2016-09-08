<?php
defined('IN_ECJIA') or exit('No permission resources.');

class favourable_user_rank_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'user_rank';
		parent::__construct();
	}
	
	/**
	 *
	 * 取得用户等级数组,按用户级别排序
	 * @param   bool      $is_special      是否只显示特殊会员组
	 * @return  array     rank_id=>rank_name
	 */
	public function get_rank_list($is_special = false) {
	    $rank_list = array();
	    if ($is_special) {
	        $data = $this->field('rank_id, rank_name, min_points')->where('special_rank = 1')->order('min_points asc')->select();
	    } else {
	        $data = $this->field('rank_id, rank_name, min_points')->order('min_points asc')->select();
	    }
	    if (!empty($data)) {
	        foreach ($data as $row) {
	            $rank_list[$row['rank_id']] = $row['rank_name'];
	        }
	    }
	    return $rank_list;
	}
	
	public function user_rank_select($field='*', $where=array(), $order=array()) {
		if (!empty($order)) {
			return $this->field($field)->where($where)->order($order)->select();
		}
		return $this->field($field)->where($where)->select();
	}
}

// end
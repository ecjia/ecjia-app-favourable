<?php
defined('IN_ECJIA') or exit('No permission resources.');

class favourable_activity_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'favourable_activity';
		parent::__construct();
	}
	
	/*
	 * 取得优惠活动列表
	* @param   array()     $filter     查询条件
	* @return   array
	*/
	public function favourable_list($filter = array()) {
		/* 过滤条件 */
		$where = array();
		if (!empty($filter['keyword'])) {
			$where['act_name'] = array('like'=>"%" . mysql_like_quote($filter['keyword']) . "%");
		}
		$now = RC_Time::gmtime();
		if (isset($filter['on_going']) && $filter['on_going'] == 1) {
			$where['start_time'] = array('elt' => $now);
			$where['end_time'] = array('egt' => $now);
		}
		/* 正在进行中*/
		if (isset($filter['status']) && $filter['status'] == 'going') {
			$where['start_time'] = array('elt' => $now);
			$where['end_time'] = array('egt' => $now);
		}
		/* 即将开始*/
		if (isset($filter['status']) && $filter['status'] == 'coming') {
			$where['start_time'] = array('egt' => $now);
		}
		/* 已结束*/
		if (isset($filter['status']) && $filter['status'] == 'finished') {
			$where['end_time'] = array('elt' => $now);
		}
	
		/* 排序*/
		$filter['sort_by']    = empty($filter['sort_by']) ? 'act_id' : trim($filter['sort_by']);
		$filter['sort_order'] = empty($filter['sort_order']) ? 'DESC' : trim($filter['sort_order']);
	
		$count = $this->where($where)->count();
		//实例化分页
		$page_row = new ecjia_page($count, $filter['size'], 6, '', $filter['page']);
	
		$res = $this->where($where)->order(array('sort_order' => 'asc', $filter['sort_by'] => $filter['sort_order']))->limit($page_row->limit())->select();
	
		$list = array();
		if (!empty($res)) {
			foreach ($res as $row) {
				$row['start_time']  = RC_Time::local_date('Y-m-d H:i', $row['start_time']);
				$row['end_time']    = RC_Time::local_date('Y-m-d H:i', $row['end_time']);
				$list[] = $row;
			}
		}
		return array('item' => $list, 'page' => $page_row);
	}
	
	/*查询优惠活动信息*/
	public function favourable_info($act_id) {
		$favourable = $this->find (array('act_id' => $act_id));
		if (!empty ($favourable)) {
			$favourable['start_time'] = RC_Time::local_date(ecjia::config('time_format'), $favourable['start_time']);
			$favourable['end_time']	= RC_Time::local_date(ecjia::config('time_format'), $favourable['end_time']);
			$favourable['formatted_min_amount'] = price_format($favourable['min_amount'] );
			$favourable['formatted_max_amount'] = price_format($favourable['max_amount'] );
			$favourable['gift'] = unserialize($favourable['gift']);
			if ($favourable['act_type'] == FAT_GOODS) {
				$favourable['act_type_ext'] = round($favourable['act_type_ext']);
			}
			/* 取得用户等级 */
			$favourable['user_rank_list'] = array();
			$favourable['user_rank_list'][] = array(
				'rank_id'   => 0,
				'rank_name' => RC_Lang::get('favourable::favourable.non_member'),
				'checked'   => strpos(',' . $favourable['user_rank'] . ',', ',0,') !== false,
			);
	
			$data = RC_Model::Model('favourable/favourable_user_rank_model')->user_rank_select('rank_id, rank_name');
			if (!empty($data)) {
				foreach ($data as $row) {
					$row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $row['rank_id']. ',') !== false;
					$favourable['user_rank_list'][] = $row;
				}
			}
			
			/* 取得优惠范围 */
			$act_range_ext = array();
			if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
				if ($favourable['act_range'] == FAR_CATEGORY) {
					$act_range_ext = RC_Model::Model('favourable/favourable_category_model')->field('cat_id AS id, cat_name AS name')->in(array('cat_id'=>$favourable['act_range_ext']))->select();
					
				} elseif ($favourable['act_range'] == FAR_BRAND) {
					$act_range_ext = RC_Model::Model('favourable/favourable_brand_model')->field('brand_id AS id, brand_name AS name')->in(array('brand_id'=>$favourable['act_range_ext']))->select();
				} else {
					$act_range_ext = RC_Model::Model('favourable/favourable_goods_model')->field('goods_id AS id, goods_name AS name')->in(array('goods_id'=>$favourable['act_range_ext']))->select();
				}
			}
			$favourable['act_range_ext'] = $act_range_ext;
		}
		
		return $favourable;
	}
	
	/* 优惠活动管理*/
	public function favourable_manage($parameter) {	
		if (!isset($parameter['act_id'])) {
			$act_id = $this->insert($parameter);
		} else {
			$where = array('act_id' => $parameter['act_id']);
			/* b2b2c判断*/
			if (isset($parameter['user_id'])) {
				$where['user_id'] = $parameter['user_id'];
			}
			$this->where($where)->update($parameter);
			$act_id = $parameter['act_id'];
		}
		
		return $act_id;
	}
	
	/*删除优惠活动*/
	public function favourable_remove($act_id, $in=false) {
		if ($in) {
			return $this->in(array('act_id' => $act_id))->delete();
		}
		return $this->where(array('act_id' => $act_id))->delete();
	}
	
	/*判断重复*/
	public function is_only($where) {
		return $this->where($where)->count();
	}
	
	/*搜索优惠活动*/
	public function favourable_select($where, $in=false) {
		if ($in) {
			return $this->in($where)->select();
		}
		return $this->where($where)->select();
	}
	
	/*搜索优惠活动字段*/
	public function favourable_field($where, $field, $bool=false) {
		return $this->where($where)->get_field($field, $bool);
	}
	
	public function favourable_find($where, $field = '*') {
		return $this->field($field)->where($where)->find();
	}
	
	/*
	 * 取得优惠活动列表
	*/
	public function get_favourable_list($option) {
		return $this->where($option['where'])->order($option['order'])->limit($option['limit'])->select();
	}
}

// end
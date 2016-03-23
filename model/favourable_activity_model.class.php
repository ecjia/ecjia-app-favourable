<?php
defined('IN_ROYALCMS') or exit('No permission resources.');

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
	public function favourable_list($filter = array()) 
	{	
		/* 过滤条件 */
		$where = array();
		if (!empty($filter['keyword'])) {
			$where['act_name'] = array('like'=>"%" . mysql_like_quote($filter['keyword']) . "%");
		}
		$now = RC_Time::gmtime();
		if (isset($filter['is_going']) && $filter['is_going'] == 1) {
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
		
		//加载分页类
		RC_Loader::load_sys_class('ecjia_page', false);
		$count = $this->where($where)->count();
		//实例化分页
		$page_row = new ecjia_page($count, $filter['size'], 6, '', $filter['page']);
		
		$res = $this->where($where)->order('sort_order asc')->limit($page_row->limit())->select();
		
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
	
	
	function favourable_info($act_id) {
		$favourable = $this->find (array('act_id' => $act_id));
		if (!empty ($favourable)) {
			$favourable['start_time']	= RC_Time::local_date(ecjia::config('time_format'), $favourable['start_time']);
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
				'rank_name' => __('非会员'),
				'checked'   => strpos(',' . $favourable['user_rank'] . ',', ',0,') !== false,
			);
	
			$data = RC_Model::Model('user/user_rank_model')->field('rank_id, rank_name')->select();
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
					$act_range_ext = RC_Model::Model('goods/category_model')->field('cat_id AS id, cat_name AS name')->in(array('cat_id'=>$favourable['act_range_ext']))->select();
					
				} elseif ($favourable['act_range'] == FAR_BRAND) {
					$act_range_ext = RC_Model::Model('goods/brand_model')->field('brand_id AS id, brand_name AS name')->in(array('brand_id'=>$favourable['act_range_ext']))->select();
				} else {
					$act_range_ext = RC_Model::Model('goods/goods_model')->field('goods_id AS id, goods_name AS name')->in(array('goods_id'=>$favourable['act_range_ext']))->select();
				}
			}
			$favourable['act_range_ext'] = $act_range_ext;
		}
		
		return $favourable;
	}
	
	/* 优惠活动管理*/
	public function favourable_manage($parameter) 
	{	
		if ($parameter['act_type'] == 0) {
			$act_type = '享受赠品（特惠品）';
		} elseif ($parameter['act_type'] == 1) {
			$act_type = '享受现金减免';
		} else {
			$act_type = '享受价格折扣';
		}
		
		if (!isset($parameter['act_id'])) {
			$act_id = $this->insert($parameter);
			ecjia_admin::admin_log($parameter['act_name'].'，'.'优惠活动方式是 '.$act_type, 'add', 'favourable');	
		} else {
			$this->where(array('act_id' => $parameter['act_id']))->update($parameter);
			ecjia_admin::admin_log($parameter['act_name'].'，'.'优惠活动方式是 '.$act_type, 'edit', 'favourable');
			$act_id = $parameter['act_id'];
		}
		
		return $act_id;
	}
	
	public function favourable_remove($act_id) {
		$favourable = $this->favourable_info($act_id);
		if (!empty($favourable)) {
			return false;
		}
		
		$name = $favourable['act_name'];
		$act_type = $favourable['act_type'];
		
		if ($act_type == 0) {
			$act_type = '享受赠品（特惠品）';
		} elseif ($act_type == 1) {
			$act_type = '享受现金减免';
		} else {
			$act_type = '享受价格折扣';
		}
		
		$this->where(array('act_id' => $act_id))->delete();
		ecjia_admin::admin_log($name.'，'.'优惠活动方式是 '.$act_type, 'remove', 'favourable');
		return true;
	}
}
// end
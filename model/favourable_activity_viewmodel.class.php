<?php
defined('IN_ECJIA') or exit('No permission resources.');

class favourable_activity_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'favourable_activity';
		$this->table_alias_name = 'fa';
		
		$this->view = array(
			'seller_shopinfo' => array(
				'type' 	=> Component_Model_View::TYPE_LEFT_JOIN,
				'alias' => 'ssi',
				'on' 	=> "ssi.id = fa.seller_id"
			)
		);
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
		
		/* 卖家*/
		if (isset($filter['seller_id'])) {
		    $where['seller_id'] = $filter['seller_id'];
		}
		
		/* 排序*/
		$filter['sort_by']    = empty($filter['sort_by']) ? 'act_id' : trim($filter['sort_by']);
		$filter['sort_order'] = empty($filter['sort_order']) ? 'DESC' : trim($filter['sort_order']);
		
		$join = null;
		/* 判断是否是b2b2c*/
		$result_app = ecjia_app::validate_application('seller');
		$is_active = ecjia_app::is_active('ecjia.seller');
		if (!is_ecjia_error($result_app) && $is_active) {
			$join = array('seller_shopinfo');
		}
		
		$count = $this->where($where)->join(null)->count();
		//实例化分页
		$page_row = new ecjia_page($count, $filter['size'], 6, '', $filter['page']);
		
		$res = $this->join($join)->field(array('fa.*', 'seller_id', 'shop_name as seller_name'))->where($where)->order(array($filter['sort_by'] => $filter['sort_order']))->limit($page_row->limit())->select();
		
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
	
	/*获取商家活动列表*/
	public function seller_activity_list($options) {
		$record_count = $this->join(array('seller_shopinfo'))->where($options)->count();
		//实例化分页
		$page_row = new ecjia_page($record_count, $options['size'], 6, '', $options['page']);
		$res = $this->join(array('seller_shopinfo'))->where($options['where'])->field('ssi.shop_name, ssi.logo_thumb,fa.*')->limit($page_row->limit())->select();
		return array('favourable_list' => $res, 'page' => $page_row);
	}
}

// end
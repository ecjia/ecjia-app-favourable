<?php
defined('IN_ECJIA') or exit('No permission resources.');

class favourable_activity_model extends Component_Model_Model {
	public $table_name = '';
	public function __construct() {
		$this->table_name = 'favourable_activity';
		parent::__construct();
	}
	
	/*
	 * 取得优惠活动列表
	 * @param   array()     $filter     查询条件
	 * @return   array
	 */
	public function favourable_list($filter = array()) {	
		$db_favourable_activity = RC_DB::table('favourable_activity');
		
// 		$where = array();
		//判断是否为商家
		// if (isset($_SESSION['seller_id']) && ($_SESSION['seller_id'] > 0)) {
		// 	$where['seller_id'] = $_SESSION['seller_id'];
		// 	$db_favourable_activity->where('seller_id', $_SESSION['seller_id']);
		// }
		
		if (!empty($filter['keyword'])) {
// 			$where['act_name'] = array('like' => "%" . mysql_like_quote($filter['keyword']) . "%");
			$db_favourable_activity->where('act_name', 'like', '%'. mysql_like_quote($filter['keyword']) .'%');
		}
		$now = RC_Time::gmtime();
		if (isset($filter['is_going']) && $filter['is_going'] == 1) {
// 			$where['start_time'] = array('elt' => $now);
// 			$where['end_time'] = array('egt' => $now);
			$db_favourable_activity->where('start_time', '<=', $now)->where('end_time', '>=', $now);
		}
		/* 正在进行中*/
		if (isset($filter['status']) && $filter['status'] == 'going') {
// 			$where['start_time'] = array('elt' => $now);
// 			$where['end_time'] = array('egt' => $now);
			$db_favourable_activity->where('start_time', '<=', $now)->where('end_time', '>=', $now);
		}
		/* 即将开始*/
		if (isset($filter['status']) && $filter['status'] == 'coming') {
// 			$where['start_time'] = array('egt' => $now);
			
			$db_favourable_activity->where('start_time', '>=', $now);
		}
		/* 已结束*/
		if (isset($filter['status']) && $filter['status'] == 'finished') {
// 			$where['end_time'] = array('elt' => $now);
			$db_favourable_activity->where('end_time', '<=', $now);
		}
		
		/* 排序*/
		$filter['sort_by']    = empty($filter['sort_by']) ? 'act_id' : trim($filter['sort_by']);
		$filter['sort_order'] = empty($filter['sort_order']) ? 'DESC' : trim($filter['sort_order']);
		
		$count = $db_favourable_activity->count();
		//实例化分页
		$page_row = new ecjia_page($count, $filter['size'], 6, '', $filter['page']);
		
// 		$res = $this->where($where)->order(array($filter['sort_by'] => $filter['sort_order']))->limit($page_row->limit())->select();
		$res = $db_favourable_activity
			->orderby($filter['sort_by'], $filter['sort_order'])
			->select('*')
			->take($filter['size'])
			->skip($page->start_id-1)
			->get();
		
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
	
	public function favourable_info($act_id) {
// 		$favourable = $this->find(array('act_id' => $act_id));
		$favourable = RC_DB::table('favourable_activity')->where('act_id', $act_id)->where('store_id', $_SESSION['store_id'])->first();
		
		if (!empty ($favourable)) {
			$favourable['start_time']	= RC_Time::local_date(ecjia::config('time_format'), $favourable['start_time']);
			$favourable['end_time']		= RC_Time::local_date(ecjia::config('time_format'), $favourable['end_time']);
			
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
	
			$data = RC_DB::table('user_rank')->select('rank_id', 'rank_name')->get();
			if (!empty($data)) {
				foreach ($data as $row) {
					$row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $row['rank_id']. ',') !== false;
					$favourable['user_rank_list'][] = $row;
				}
			}
			
			/* 取得优惠范围 */
			$act_range_ext = array();
			if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
				$favourable['act_range_ext'] = explode(',', $favourable['act_range_ext']);
				if ($favourable['act_range'] == FAR_CATEGORY) {
					$act_range_ext = RC_DB::table('category')->whereIn('cat_id', $favourable['act_range_ext'])->select(RC_DB::raw('cat_id as id'), RC_DB::raw('cat_name as name'))->get();
					
				} elseif ($favourable['act_range'] == FAR_BRAND) {
					/* 区分入驻商及平台*/
// 					if (!isset($_SESSION['seller_id'])) {
						$act_range_ext = RC_DB::table('brand')->whereIn('brand_id', $favourable['act_range_ext'])->select(RC_DB::raw('brand_id as id'), RC_DB::raw('brand_name as name'))->get();
// 					} else {
// 						$act_range_ext = RC_DB::table('merchants_shop_brand')->leftJoin('seller_shopinfo', 'seller_shopinfo.id', '=', 'merchants_shop_brand.seller_id')
// 							->select(RC_DB::raw('bid as id'), RC_DB::raw('brandName as name'))
// 							->whereIn('bid', $favourable['act_range_ext'])
// 							->get();
// 					}
				} else {
					$act_range_ext = RC_DB::table('goods')->whereIn('goods_id', $favourable['act_range_ext'])->select(RC_DB::raw('goods_id as id'), RC_DB::raw('goods_name as name'))->get();
				}
			}
			$favourable['act_range_ext'] = $act_range_ext;
		}
		return $favourable;
	}
	
	/* 优惠活动管理*/
	public function favourable_manage($parameter) {	
		$db_favourable = RC_DB::table('favourable_activity');
		if (!isset($parameter['act_id'])) {
// 			$act_id = $this->insert($parameter);
			$act_id = $db_favourable->insertGetId($parameter);
		} else {
// 			$where = array('act_id' => $parameter['act_id']);
// 			/* b2b2c判断*/
// 			if (isset($parameter['seller_id'])) {
// 				$where['seller_id'] = $parameter['seller_id'];
// 			}
// 			$this->where($where)->update($parameter);
			
			$db_favourable->where('act_id', $parameter['act_id']);
			if (isset($parameter['store_id'])) {
				$db_favourable->where('store_id', $parameter['store_id']);
			}
			$db_favourable->update($parameter);
			
			$act_id = $parameter['act_id'];
		}
		
		return $act_id;
	}
	
	public function favourable_remove($act_id, $bool = false) {
// 		return $this->where(array('act_id' => $act_id))->delete();
		if (!empty($_SESSION['store_id']) && $_SESSION['store_id'] > 0) {
			RC_DB::table('favourable_activity')->where('store_id', $_SESSION['store_id']);
		}
		if ($bool) {
			return RC_DB::table('favourable_activity')->whereIn('act_id', $act_id)->delete();
		}
		return RC_DB::table('favourable_activity')->where('act_id', $act_id)->delete();
	}
}
// end
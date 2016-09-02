<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 满减满赠活动列表
 * @author will
 *
 */
class list_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		
		$status = _POST('status', 'coming');
		$size = EM_Api::$pagination['count'];
		$page = EM_Api::$pagination['page'];
		
		
		$filter = array(
			'status' => $status,
			'size'	 => !empty($size) ? intval($size) : 15,
			'page'	 => !empty($page) ? intval($page) : 1,
		);
		
		$result = RC_Model::Model('favourable/favourable_activity_viewmodel')->favourable_list($filter);
		$data = array();
		if (!empty($result['item'])) {
			
			/* 取得用户等级 */
			$db_user_rank = RC_Loader::load_app_model('user_rank_model', 'user');
			$user_rank_list = $db_user_rank->field('rank_id, rank_name')->select();
			foreach ($result['item'] as $key => $val) {
				$rank_name = array();
				if (strpos(',' . $val['user_rank'] . ',', ',0,') !== false) {
					$rank_name[] = __('非会员');
				}
				
				if (!empty($user_rank_list)) {
					foreach ($user_rank_list as $row) {
						if (strpos(',' . $val['user_rank'] . ',', ',' . $row['rank_id']. ',') !== false) {
							$rank_name[] = $row['rank_name'];
						}
					}
				}
				$act_type = $val['act_type'] == 1 || $val['act_type'] == 2 ? $val['act_type'] == 1 ? '满'.$val['min_amount'].'减'.$val['act_type_ext'] : '满'.$val['min_amount'].'享受'.($val['act_type_ext']/10).'折' : __('享受赠品（特惠品）');
				$data[] = array(
					'act_id'	=> $val['act_id'],
					'act_name'	=> $val['act_name'],
					'label_act_type'	=> $act_type,
					'rank_name'		=> $rank_name,
					'max_amount'	=> $val['max_amount'],
					'formatted_start_time'	=> $val['start_time'],
					'formatted_end_time'	=> $val['end_time'],
					'seller_id'		=> !empty($val['seller_id']) ? $val['seller_id'] : 0,
					'seller_name'	=> !empty($val['seller_name']) ? $val['seller_name'] : '自营', 
				);
			}
		}
		
		$pager = array(
				'total' => $result['page']->total_records,
				'count' => $result['page']->total_records,
				'more'	=> $result['page']->total_pages <= $page ? 0 : 1,
		);
		EM_Api::outPut($data, $pager);
		

	}
}
// end
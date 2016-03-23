<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 满减满赠活动信息
 * @author will
 *
 */
class info_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		
		$id = _POST('act_id', 0);
		if ($id <= 0) {
			EM_Api::outPut(101);
		}
	
		$result = RC_Model::Model('favourable/favourable_activity_model')->favourable_info($id);
		
		if (!empty($result)) {
			if ($result['act_range'] == 0) {
				$result['label_act_range'] = __('全部商品');
			} elseif ($result['act_range'] == 1) {
				$result['label_act_range'] = __('指定分类');
			} elseif ($result['act_range'] == 2) {
				$result['label_act_range'] = __('指定品牌');
			} else {
				$result['label_act_range'] = __('指定商品');
			}
			
			if ($result['act_type'] == 0) {
				$result['label_act_type'] = __('特惠品');
			} elseif ($result['act_type'] == 1) {
				$result['label_act_type'] = __('现金减免');
			} else {
				$result['label_act_type'] = __('价格折扣');
			}
			$result['gift_items'] = $result['gift'];
			$result['formatted_start_time'] = $result['start_time'];
			$result['formatted_end_time'] = $result['end_time'];
			
			/* 去除不要的字段*/
			unset($result['start_time']);
			unset($result['end_time']);
			unset($result['gift']);
			unset($result['user_rank']);
			unset($result['sort_order']);
			return $result;
		} else {
			EM_Api::outPut(13);
		}
	}
}
// end
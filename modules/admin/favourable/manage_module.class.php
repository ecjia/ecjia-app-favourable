<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 满减满赠活动添加编辑处理
 * @author will
 *
 */
class manage_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		
		RC_Loader::load_app_class('favourable', 'favourable', false);
		
		$act_id = _POST('act_id', 0);
		$user_rank = _POST('user_rank');
		$gift = _POST('gift', array());

		$favourable = array(
			'act_name'      => _POST('act_name'),
			'start_time'    => RC_Time::local_strtotime(_POST('start_time')),
			'end_time'      => RC_Time::local_strtotime(_POST('end_time')),
			'user_rank'     => $user_rank,
			'act_range'     => _POST('act_range'),
			'act_range_ext' => _POST('act_range_ext'),
			'min_amount'    => _POST('min_amount'),
			'max_amount'    => _POST('max_amount'),
			'act_type'      => _POST('act_type'),
			'act_type_ext'  => _POST('act_type_ext'),
			'gift'          => serialize($gift),
		);
		if ($act_id > 0) {
			$favourable['act_id'] = $act_id;
		}
		
		$result = RC_Model::Model('favourable/favourable_activity_model')->favourable_manage($favourable);
		if (is_ecjia_error($result)) {
			return $result;
		} else {
			return array();
		}
	}
}
// end
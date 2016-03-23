<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 满减满赠活动删除
 * @author will
 *
 */
class delete_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		
		$id = _POST('act_id', 0);
		if ($id <= 0) {
			EM_Api::outPut(101);
		}
		
		$result = RC_Model::Model('favourable/favourable_activity_model')->favourable_remove($id);
		if (is_ecjia_error($result)) {
			return $result;
		} else {
			return array();
		}
	}
}
// end
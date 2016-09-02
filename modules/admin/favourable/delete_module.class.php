<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 满减满赠活动删除
 * @author will
 *
 */
class delete_module extends api_admin implements api_interface {
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
    		
		$this->authadminSession();
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$id = _POST('act_id', 0);
		if ($id <= 0) {
			EM_Api::outPut(101);
		}
		
		$favourable = RC_Model::Model('favourable/favourable_activity_model')->favourable_info($id);
		if (empty($favourable)) {
			EM_Api::outPut(13);
		}
		/* 多商户处理*/
		if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] > 0 && $favourable['seller_id'] != $_SESSION['seller_id']) {
			EM_Api::outPut(8);
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
		
		$result = RC_Model::Model('favourable/favourable_activity_model')->favourable_remove($id);
		ecjia_admin::admin_log($name.'，'.'优惠活动方式是 '.$act_type, 'remove', 'favourable');
		return array();
	}
}
// end
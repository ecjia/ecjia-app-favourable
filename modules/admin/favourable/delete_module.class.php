<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 满减满赠活动删除
 * @author will
 *
 */
class delete_module extends api_admin implements api_interface
{
    public function handleRequest(\Royalcms\Component\HttpKernel\Request $request)
    {
		$this->authadminSession();
		if ($_SESSION['admin_id'] <= 0) {
			return new ecjia_error(100, 'Invalid session');
		}
		$id = $this->requestData('act_id', 0);
		if ($id <= 0) {
			return new ecjia_error('invalid_parameter', RC_Lang::get('system::system.invalid_parameter'));
		}
		
		$favourable = RC_Model::Model('favourable/favourable_activity_model')->favourable_info($id);
		if (empty($favourable)) {
			return new ecjia_error('not_exists_info', '不存在的信息');
		}
		/* 多商户处理*/
		if (isset($_SESSION['ru_id']) && $_SESSION['ru_id'] > 0 && $favourable['user_id'] != $_SESSION['ru_id']) {
			return new ecjia_error('not_exists_info', '不存在的信息');
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
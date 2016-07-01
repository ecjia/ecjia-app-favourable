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
		
		if (!empty($gift)) {
			foreach ($gift as $key => $val) {
				if (empty($val['price']) && $val['price'] == '') {
					
				}
			}
		}
		
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
		
		/* 检查优惠活动时间 */
		if ($favourable['start_time'] >= $favourable['end_time']) {
			return new ecjia_error('time_error', __('优惠开始时间不能大于或等于结束时间'));
		}
		
		/* 检查享受优惠的会员等级 */
		if (!isset($favourable['user_rank'])) {
			return new ecjia_error('user_rank_error', __('请设置享受优惠的会员等级'));
		}
	
		/* 检查优惠范围扩展信息 */
		if ($favourable['act_range'] > 0 && !isset($favourable['act_range_ext'])) {
			return new ecjia_error('act_range_error', __('请设置优惠范围'));
		}
		/* 检查金额上下限 */
		$min_amount = floatval($favourable['min_amount']) >= 0 ? floatval($favourable['min_amount']) : 0;
		$max_amount = floatval($favourable['max_amount']) >= 0 ? floatval($favourable['max_amount']) : 0;
		if ($max_amount > 0 && $min_amount > $max_amount) {
			return new ecjia_error('amount_error', __('金额下限不能大于金额上限'));
		}
		
		if ($act_id > 0) {
			$favourable['act_id'] = $act_id;
		}
		if (isset($_SESSION['seller_id']) && $_SESSION['seller_id'] > 0) {
			$favourable['seller_id'] = $_SESSION['seller_id'];
		}
		
		if ($favourable['act_type'] == 0) {
			$act_type = '享受赠品（特惠品）';
		} elseif ($favourable['act_type'] == 1) {
			$act_type = '享受现金减免';
		} else {
			$act_type = '享受价格折扣';
		}
		RC_Model::Model('favourable/favourable_activity_model')->favourable_manage($favourable);
		if ($act_id > 0 ) {
			ecjia_admin::admin_log($favourable['act_name'].'，'.'优惠活动方式是 '.$act_type, 'edit', 'favourable');
		} else {
			ecjia_admin::admin_log($favourable['act_name'].'，'.'优惠活动方式是 '.$act_type, 'add', 'favourable');
		}
		return array();
	}
}
// end
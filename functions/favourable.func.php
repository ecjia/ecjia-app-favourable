<?php

/**
 * 优惠活动相关函数库
 */
defined ( 'IN_ECJIA' ) or exit ( 'No permission resources.' );
/*
 * 取得优惠活动列表
* @return   array
*/
function favourable_list() {
	$db_favourable_activity = RC_Loader::load_app_model('favourable_activity_model', 'favourable');
	/* 过滤条件 */
	
	$filter['keyword']    = empty($_REQUEST['keyword']) 	? '' 		: trim($_REQUEST['keyword']);
	$filter['on_going']   = empty($_REQUEST['on_going'])	? 0 		: 1;
	$filter['sort_by']    = empty($_REQUEST['sort_by']) 	? 'act_id' 	: trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) 	? 'DESC' 	: trim($_REQUEST['sort_order']);
	
	$where = array();
	if (!empty($filter['keyword'])) {
		$where['act_name'] = array('like'=>"%" . mysql_like_quote($filter['keyword']) . "%");
	}
	if ($filter['on_going'] == 1) {
		$now = RC_Time::gmtime();
		$where['start_time'] = array('elt' => $now);
		$where['end_time'] = array('egt' => $now);
	}
	$filter['record_count'] = $db_favourable_activity->where($where)->count();
	$res = $db_favourable_activity->where($where)->order('sort_order asc')->limit($filter['start'], $filter['page_size'])->select();
	$filter['keyword'] = stripslashes($filter['keyword']);
	
	$list = array();
	if (!empty($res)) {
		foreach ($res as $row) {
			$row['start_time']  = RC_Time::local_date('Y-m-d H:i', $row['start_time']);
			$row['end_time']    = RC_Time::local_date('Y-m-d H:i', $row['end_time']);
			$list[] = $row;
		}
	}
	return array('item' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

function favourable_info($act_id) {
	$db = RC_Loader::load_app_model ( 'favourable_activity_model', 'favourable' );
	$row = $db->find ( array (
		'act_id' => $act_id
	) );
	if (! empty ( $row )) {
		$row ['start_time'] = RC_Time::local_date ( ecjia::config ( 'time_format' ), $row ['start_time'] );
		$row ['end_time'] = RC_Time::local_date ( ecjia::config ( 'time_format' ), $row ['end_time'] );
		$row ['formated_min_amount'] = price_format ( $row ['min_amount'] );
		$row ['formated_max_amount'] = price_format ( $row ['max_amount'] );
		$row ['gift'] = unserialize ( $row ['gift'] );
		if ($row ['act_type'] == FAT_GOODS) {
			$row ['act_type_ext'] = round ( $row ['act_type_ext'] );
		}
	}
	return $row;
}
// end
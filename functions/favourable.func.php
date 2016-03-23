<?php

/**
 * 文章及文章分类相关函数库
 */
defined ( 'IN_ECJIA' ) or exit ( 'No permission resources.' );
/*
 * 取得优惠活动列表
* @return   array
*/
function favourable_list()
{
	$db_favourable_activity_viewmodel = RC_Loader::load_app_model('favourable_activity_package_viewmodel','favourable');
	$db_favourable_activity = RC_Loader::load_app_model('favourable_activity_model','favourable');
	/* 过滤条件 */
	
	$filter['keyword']    = empty($_REQUEST['keyword']) ? '' : trim($_REQUEST['keyword']);
	$filter['is_going']   = empty($_REQUEST['is_going']) ? 0 : 1;
	$filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'act_id' : trim($_REQUEST['sort_by']);
	$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
	$where = array();
	
	if (!empty($filter['keyword'])) {
		$where['act_name'] = array('like'=>"%" . mysql_like_quote($filter['keyword']) . "%");//" and act_name LIKE '%" . mysql_like_quote($filter['keyword']) . "%'";
	}
	
	if ($filter['is_going'] == 1) {
		$now = RC_Time::gmtime();
		$where['start_time']=array('elt'=>$now);
		$where['end_time']=array('egt'=>$now);
	}
	
	$filter['record_count'] = $db_favourable_activity->where($where)->count();
	
	$page = new ecjia_page($filter['record_count'],10,5);
	
	$field = 'fa.act_id, fa.act_name, fa.user_rank, fa.start_time, fa.end_time, fa.act_range, fa.act_range_ext, fa.min_amount, fa.act_type, fa.act_type_ext, fa.gift, fa.sort_order | sort, fa.max_amount, fa.user_id, msi.shopNamesuffix,msi.shoprz_brandName';
	$res = $db_favourable_activity_viewmodel->field($field)->where($where)->order('sort asc')->limit($page->limit())->select();

	$filter['keyword'] = stripslashes($filter['keyword']);
	
	$list = array();
	foreach ($res as $row) {
		$row['start_time']  	= RC_Time::local_date('Y-m-d H:i', $row['start_time']);
		$row['end_time']    	= RC_Time::local_date('Y-m-d H:i', $row['end_time']);
		$row['shop_name'] = $row['user_id'] == 0 ? '' : $row['shoprz_brandName'].$row['shopNamesuffix'];
		$list[] = $row;
	}
	return array('item' => $list, 'filter' => $filter, 'page' => $page->show(5), 'desc' => $page->page_desc());
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
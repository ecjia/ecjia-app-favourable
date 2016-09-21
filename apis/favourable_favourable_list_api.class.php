<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 获取商家活动
 * @author zrl
 *
 */
class favourable_favourable_list_api extends Component_Event_Api {
    
    public function call(&$options) {
    	if (!is_array($options)) {
    		return new ecjia_error('invalid_parameter', '参数无效');
    	}
    	
    	$favourable_activity_dbview = RC_DB::table('favourable_activity as fa');
    	
    	/* 判断是否有store应用*/
    	$result = ecjia_app::validate_application('store');
    	if (!is_ecjia_error($result)) {
    		$favourable_activity_dbview->leftJoin('store_franchisee as s', RC_DB::raw('fa.store_id'), '=', RC_DB::raw('s.store_id'));
    	}
    	
    	if (isset($options['keyword']) && !empty($options['keyword'])) {
    		$favourable_activity_dbview->where('act_name', 'like', '%' . $options['keyword'] . '%');
    	}
    	
    	if (isset($options['merchant_name']) && !empty($options['merchant_name'])) {
    		$favourable_activity_dbview->where('merchants_name', 'like', '%' . $options['merchant_name'] . '%');
    	}
    	
    	if (isset($options['type']) && $options['type'] == 'on_going') {
    		$time = RC_Time::gmtime();
    		$favourable_activity_dbview->where('start_time', '<=', $time)->where('end_time', '>=', $time);
    	} elseif (isset($options['type']) && $options['type'] == 'merchants') {
    		$favourable_activity_dbview->where(RC_DB::raw('fa.store_id'), '>', 0);
    	}
    	
    	if (isset($options['store_id']) && !empty($options['store_id'])) {
    		$favourable_activity_dbview->whereIn(RC_DB::raw('fa.store_id'), $options['store_Id']);
    	}
    	
    	$res = $favourable_activity_dbview
			    	->selectRaw('fa.*, s.merchants_name')
			    	->orderby($options['sort_by'], $options['sort_order'])
			    	->take($options['limit'])
			    	->skip($options['skip'])
			    	->get();
    	return $res;
    }
  
}

// end
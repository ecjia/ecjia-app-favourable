<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 获取商家活动
 * @author zrl
 *
 */
class favourable_favourable_list_api extends Component_Event_Api {
    
    public function call(&$options) {
    	if (!is_array($options) || empty($options['location'])) {
    		return new ecjia_error('invalid_parameter', '参数无效');
    	}
        return $this->favourable_list($options);
    }
    
    /**
	 * 取取商家活动
	 * @param   array	 $location		经纬度
	 * @return  array   商家活动数组
	 */
	private function favourable_list($options) 
	{
		$where = array();
		$where['fa.seller_id'] = array('gt' => '0');
		$now = RC_Time::gmtime();
		$where['start_time'] = array('elt' => $now);
		$where['end_time'] = array('egt' => $now);
		/*根据经纬度查询附近店铺*/
		if (is_array($options['location']) && !empty($options['location']['latitude']) && !empty($options['location']['longitude'])) {
			$geohash = RC_Loader::load_app_class('geohash', 'shipping');
			$geohash_code = $geohash->encode($options['location']['latitude'] , $options['location']['longitude']);
			$geohash_code = substr($geohash_code, 0, 5);
			$where['geohash'] = array('like' => "%$geohash_code%");
		}
	   
		$dbview = RC_Model::Model('favourable/favourable_activity_viewmodel');
		$record_count = $dbview->join(array('seller_shopinfo'))->where($where)->count();
		//实例化分页
		$page_row = new ecjia_page($record_count, $options['size'], 6, '', $options['page']);
		$par = array(
				'where' => $where,
				'limit' => $page_row,
		);
		$res = RC_Model::Model('favourable/favourable_activity_viewmodel')->seller_activity_list($par);
		$list = array();
		if (!empty($res)) {
			foreach ($res as $row) {
				$list['seller_id']  				= $row['seller_id'];
				$list['seller_name']   				= $row['shop_name'];
				$list['seller_logo']				= empty($row ['logo_thumb']) ? RC_Uri::admin_url('statics/images/nopic.png') : RC_Upload::upload_url($row ['logo_thumb']);
				$list['favourable_name']			= $row['act_name'];
				$list['favourable_type']			= $row['act_type'] == 1 || $row['act_type'] == 2 ?  $row['act_type'] == 1 ? 'price_reduction' : 'price_discount' : 'premiums';
				$list['label_favourable_type']		= $row['act_type'] == 1 || $row['act_type'] == 2 ? $row['act_type'] == 1 ? '满'.$row['min_amount'].'减'.$row['act_type_ext'] : '满'.$row['min_amount'].'享受'.($row['act_type_ext']/10).'折' : __('享受赠品（特惠品）');
				$lists[] = $list;
			}
		}
		return array('favourable_list' => $lists, 'page' => $page_row);		
	}
}

// end
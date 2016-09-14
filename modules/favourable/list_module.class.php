<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 获取商家活动列表
 * @author zrl
 *
 */
class list_module extends api_front implements api_interface {
	
	 public function handleRequest(\Royalcms\Component\HttpKernel\Request $request) {
		$this->authSession();	
		
		$location	 = $this->requestData('location', array());
		/*经纬度为空判断*/
		if (!is_array($location) || empty($location['longitude']) || empty($location['latitude'])) {
			return new ecjia_error('invalid_parameter', '参数无效');
		}
		
		$size = $this->requestData('pagination.count', 15);
		$page = $this->requestData('pagination.page', 1);
		
		$where = array();
		$where['fa.seller_id'] = array('gt' => '0');
		$now = RC_Time::gmtime();
		$where['start_time'] = array('elt' => $now);
		$where['end_time'] = array('egt' => $now);
		/*根据经纬度查询附近店铺*/
		if (is_array($location) && !empty($location['latitude']) && !empty($location['longitude'])) {
			$geohash = RC_Loader::load_app_class('geohash', 'shipping');
			$geohash_code = $geohash->encode($location['latitude'] , $location['longitude']);
			$geohash_code = substr($geohash_code, 0, 5);
			$where['geohash'] = array('like' => "%$geohash_code%");
		}
		$options = array('location' => $location, 'page' => $page, 'size' => $size, 'where' => $where);
		$cache_id = sprintf('%X', crc32($location . '-' . page  .'-' . $size  . '-' . $where ));
		
		$cache_key = 'api_favourable_list_'.'_'.$cache_id;
		$data = RC_Cache::app_cache_get($cache_key, 'favourable');
		if (empty($data)) {
			$result = RC_Api::api('favourable', 'favourable_list', $options);
			$data = array();
			$data['pager'] = array(
					'total' => $result['page']->total_records,
					'count' => $result['page']->total_records,
					'more'	=> $result['page']->total_pages <= $page ? 0 : 1,
			);
			$data['list'] = array();
			if (!empty($result['favourable_list'])) {
				foreach ($result['favourable_list'] as $key => $row) {
					$data['list'][] = array(
							'seller_id'				=> 	$row['seller_id'],
							'seller_name'			=>  $row['shop_name'],
							'seller_logo'			=>  empty($row ['logo_thumb']) ? RC_Uri::admin_url('statics/images/nopic.png') : RC_Upload::upload_url($row ['logo_thumb']),
							'favourable_name'		=>  $row['act_name'],
							'favourable_type'		=>  $row['act_type'] == 1 || $row['act_type'] == 2 ?  $row['act_type'] == 1 ? 'price_reduction' : 'price_discount' : 'premiums',
							'label_favourable_type' =>  $row['act_type'] == 1 || $row['act_type'] == 2 ? $row['act_type'] == 1 ? '满'.$row['min_amount'].'减'.$row['act_type_ext'] : '满'.$row['min_amount'].'享受'.($row['act_type_ext']/10).'折' : __('享受赠品（特惠品）'),
					);
				}
			}
			RC_Cache::app_cache_set($cache_key, $data, 'favourable');
		}
		return array('data' => $data['list'], 'pager' => $data['pager']);
	 }	
}
// end
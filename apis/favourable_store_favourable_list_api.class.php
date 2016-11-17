<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 获取商家优惠活动
 * @author zrl
 */
class favourable_store_favourable_list_api extends Component_Event_Api {
    
    public function call(&$options) {
    	if (!is_array($options)) {
    		return new ecjia_error('invalid_parameter', RC_Lang::get('system::system.invalid_parameter'));
    	}
    	
    	$db_favourable = RC_Model::model('favourable/favourable_activity_model');    	
    	//TODO ::增加优惠活动缓存
    	$cache_favourable_key = 'favourable_list_store_'.$options['store_id'];
    	$cache_id = sprintf('%X', crc32($cache_favourable_key));
    	$favourable_activity_db = RC_Model::model('favourable/orm_favourable_activity_model');
    	$favourable_list = $favourable_activity_db->get_cache_item($cache_id);
    	
    	if (empty($favourable_list)) {
    			$favourable_result = $db_favourable->where(array('store_id' => $options['store_id'], 'start_time' => array('elt' => RC_Time::gmtime()), 'end_time' => array('egt' => RC_Time::gmtime()), 'act_type' => array('neq' => 0)))->select();
    			if (!empty($favourable_result)) {
    				foreach ($favourable_result as $val) {
    					if ($val['act_range'] == '0') {
    						$favourable_list[] = array(
    								'name' => $val['act_name'],
    								'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
    								'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
    						);
    					} else {
    						$act_range_ext = explode(',', $val['act_range_ext']);
    						switch ($val['act_range']) {
    							case 1 :
    								$favourable_list[] = array(
    								'name' => $val['act_name'],
    								'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
    								'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
    								);
    								break;
    							case 2 :
    								$favourable_list[] = array(
    								'name' => $val['act_name'],
    								'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
    								'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
    								);
    								break;
    							case 3 :
    								$favourable_list[] = array(
    								'name' => $val['act_name'],
    								'type' => $val['act_type'] == '1' ? 'price_reduction' : 'price_discount',
    								'type_label' => $val['act_type'] == '1' ? __('满减') : __('满折'),
    								);
    								break;
    							default:
    								break;
    						}
    					}
    				}
    				$favourable_activity_db->set_cache_item($cache_id, $favourable_list);
    			}
    	}
    	return $favourable_list;
    }
}

// end
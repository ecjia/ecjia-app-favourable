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
        return $this->favourable_list($options['where']);
    }
    
    /**
	 * 取取商家活动
	 * @param   array	 $options（包含经纬度，当前页码，每页显示条数）
	 * @return  array   商家活动数组
	 */
	private function favourable_list($options) 
	{
		$res = RC_Model::Model('favourable/favourable_activity_viewmodel')->seller_activity_list($options);
		return $res;		
	}
}

// end
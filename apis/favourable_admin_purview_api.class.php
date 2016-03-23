<?php
defined('IN_ECJIA') or exit('No permission resources.');
/**
 * 后台权限API
 * @author royalwang
 *
 */
class favourable_admin_purview_api extends Component_Event_Api {
    
    public function call(&$options) {
        $purviews = array(
            array('action_name' => __('优惠活动管理'), 'action_code' => 'favourable_manage', 'relevance'   => ''),
        	array('action_name' => __('优惠活动添加'), 'action_code' => 'favourable_add', 'relevance'   => ''),
        	array('action_name' => __('优惠活动更新'), 'action_code' => 'favourable_update', 'relevance'   => ''),
        	array('action_name' => __('优惠活动删除'), 'action_code' => 'favourable_delete', 'relevance'   => ''),
        );
        
        return $purviews;
    }
}

// end
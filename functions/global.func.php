<?php

/**
 * 文章及文章分类相关函数库
 */
defined ( 'IN_ECJIA' ) or exit ( 'No permission resources.' );
/*
 * 管理员操作对象和动作
 */
function assign_adminlog_content(){
	ecjia_admin_log::instance()->add_object('discount', '优惠');
}
// end
<?php
/**
 * 管理中心优惠活动管理
 */
defined('IN_ECJIA') or exit('No permission resources.');

class admin extends ecjia_admin {
	private $db_favourable_activity;
	private $db_user_rank;
	private $db_category;
	private $db_brand;
	private $db_goods;
	public function __construct() {
		parent::__construct();
		
		RC_Lang::load('favourable');
		RC_Loader::load_app_func('favourable');
		RC_Loader::load_app_func('common','goods');
        RC_Loader::load_app_func('global');
        assign_adminlog_content();

		$this->db_favourable_activity = RC_Loader::load_app_model('favourable_activity_model', 'favourable');
		$this->db_user_rank = RC_Loader::load_app_model('user_rank_model', 'user');
		$this->db_category = RC_Loader::load_app_model('category_model', 'goods');
		$this->db_brand = RC_Loader::load_app_model('brand_model','goods');
		$this->db_goods = RC_Loader::load_app_model('goods_model','goods');
		
		/* 加载全局 js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('bootstrap-editable.min', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js') , array(), false, false);
		
		RC_Style::enqueue_style('bootstrap-editable', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'), array(), false, false);
		RC_Style::enqueue_style('chosen');
		RC_Style::enqueue_style('uniform-aristo');
		
		RC_Script::enqueue_script('bootstrap-datetimepicker.min', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datetimepicker.min.js'));
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datetimepicker.min.css'));
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datetimepicker.css'));
		
		RC_Script::enqueue_script('jquery-uniform');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Script::enqueue_script('favourable_list', RC_App::apps_url('statics/js/favourable_list.js', __FILE__));
		RC_Script::enqueue_script('bootstrap-datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datepicker.min.js'));
		RC_Script::enqueue_script('bootstrap-timepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-timepicker.min.js'));
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('优惠活动列表'), RC_Uri::url('favourable/admin/init')));
	}
	
	/**
	 * 活动列表页
	 */
	public function init() {
		$this->admin_priv('favourable_manage', ecjia::MSGTYPE_JSON);
		
		$this->assign('ur_here', RC_Lang::lang('favourable_list'));
		$this->assign('action_link', array('href' => RC_Uri::url('favourable/admin/add'), 'text' => RC_Lang::lang('add_favourable')));

		$list = favourable_list();
		$this->assign('favourable_list', $list);
		$this->assign('filter', $list['filter']);
		$this->assign('search_action', RC_Uri::url('favourable/admin/init'));
		
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('优惠活动列表')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台优惠活动页面，系统中所有的优惠活动都会显示在此列表中。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:优惠活动" target="_blank">关于优惠活动帮助文档</a>') . '</p>'
		);
		
		$this->assign_lang();
		$this->display('favourable_list.dwt');
	}
	
	/**
	 * 添加
	 */
	public function add() {
		$this->admin_priv('favourable_add', ecjia::MSGTYPE_JSON);
	
		$this->assign('ur_here', '添加优惠活动');
		$this->assign('action_link', array('text' => '优惠活动列表', 'href' => RC_Uri::url('favourable/admin/init')));
		$this->assign('form_action', RC_Uri::url('favourable/admin/insert'));
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('添加优惠活动')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台添加优惠活动页面，可以在此页面添加优惠活动信息。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:优惠活动#.E6.B7.BB.E5.8A.A0.E4.BC.98.E6.83.A0.E6.B4.BB.E5.8A.A8" target="_blank">关于添加优惠活动帮助文档</a>') . '</p>'
		);
		
		/* 初始化、取得优惠活动信息 */
		$favourable = array (
			'act_id'        => 0,
			'act_name'      => '',
			'start_time'    => date('Y-m-d', time() + 86400),
			'end_time'      => date('Y-m-d', time() + 4 * 86400),
			'user_rank'     => '',
			'act_range'     => FAR_ALL,
			'act_range_ext' => '',
			'min_amount'    => 0,
			'max_amount'    => 0,
			'act_type'      => FAT_GOODS,
			'act_type_ext'  => 0,
			'gift'          => array()
		);
		$this->assign('favourable', $favourable);
		/* 取得用户等级 */
		$user_rank_list   = array();
		$user_rank_list[] = array(
			'rank_id'   => 0,
			'rank_name' => RC_Lang::lang('not_user'),
			'checked'   => strpos(',' . $favourable['user_rank'] . ',', ',0,') !== false
		);
		$data = $this->db_user_rank->field('rank_id, rank_name')->select();
		if (!empty($data)) {
			foreach ($data as $row) {
				$row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $row['rank_id']. ',') !== false;
				$user_rank_list[] = $row;
			}
		}
		$this->assign('user_rank_list', $user_rank_list);

		/* 取得优惠范围 */
		$act_range_ext = array();
		if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
			if ($favourable['act_range'] == FAR_CATEGORY) {
				$act_range_ext = $this->db_category->field('cat_id AS id, cat_name AS name')->in(array('cat_id'=>$favourable['act_range_ext']))->select();
			} elseif ($favourable['act_range'] == FAR_BRAND) {
				$act_range_ext = $this->db_brand->field('brand_id AS id, brand_name AS name')->in(array('brand_id'=>$favourable['act_range_ext']))->select();
			} else {
				$act_range_ext = $this->db_goods->field('goods_id AS id, goods_name AS name')->in(array('goods_id'=>$favourable['act_range_ext']))->select();
			}
		}
		$this->assign('act_range_ext', $act_range_ext);
		$this->assign_lang();
		
		$this->display('favourable_info.dwt');
	}
	
	/**
	 * 添加处理
	 */
	public function insert() {
		$this->admin_priv('favourable_add', ecjia::MSGTYPE_JSON);
			
		$act_name = trim($_POST['act_name']);
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		if ($this->db_favourable_activity->where(array('act_name' => $act_name))->count() > 0) {
			$this->showmessage('该优惠活动名称已存在，请您换一个', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 检查享受优惠的会员等级 */
		if (!isset($_POST['user_rank'])) {
			$this->showmessage(RC_Lang::lang('pls_set_user_rank'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		/* 检查优惠范围扩展信息 */
		if (intval($_POST['act_range']) > 0 && !isset($_POST['act_range_ext'])) {
			$this->showmessage('请设置优惠范围', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		/* 检查金额上下限 */
		$min_amount = floatval($_POST['min_amount']) >= 0 ? floatval($_POST['min_amount']) : 0;
		$max_amount = floatval($_POST['max_amount']) >= 0 ? floatval($_POST['max_amount']) : 0;
		if ($max_amount > 0 && $min_amount > $max_amount) {
			$this->showmessage(RC_Lang::lang('amount_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		/* 取得赠品 */
		$gift = array();
		if (intval($_POST['act_type']) == FAT_GOODS && isset($_POST['gift_id'])) {
			foreach ($_POST['gift_id'] as $key => $id) {
				$gift[] = array('id' => $id, 'name' => $_POST['gift_name'][$key], 'price' => $_POST['gift_price'][$key]);
			}
		}

		/* 提交值 */
		$favourable = array(
			'act_id'        => intval($_POST['id']),
			'act_name'      => $act_name,
			'start_time'    => RC_Time::local_strtotime($_POST['start_time']),
			'end_time'      => RC_Time::local_strtotime($_POST['end_time']),
			'user_rank'     => isset($_POST['user_rank']) ? join(',', $_POST['user_rank']) : '0',
			'act_range'     => intval($_POST['act_range']),
			'act_range_ext' => intval($_POST['act_range']) == 0 ? '' : join(',', $_POST['act_range_ext']),
			'min_amount'    => floatval($_POST['min_amount']),
			'max_amount'    => floatval($_POST['max_amount']),
			'act_type'      => intval($_POST['act_type']),
			'act_type_ext'  => floatval($_POST['act_type_ext']),
			'gift'          => serialize($gift),
			'user_id'		=> 0,
		);

		if ($favourable['act_type'] == FAT_GOODS) {
			$favourable['act_type_ext'] = round($favourable['act_type_ext']);
		}
		
		$favourable['act_id'] = $this->db_favourable_activity->insert($favourable);
		/*管理员记录日志*/
        if (intval($_POST['act_type']) == 0) {
            $act_type = '享受赠品（特惠品）';
        } elseif (intval($_POST['act_type']) == 1) {
            $act_type = '享受现金减免';
        } elseif (intval($_POST['act_type']) == 2) {
            $act_type = '享受价格折扣';
        }
        $content = '优惠活动方式是 '.$act_type.'，优惠活动名称是 '.$act_name;
        ecjia_admin::admin_log($content, 'add', 'discount');

		$links[] = array('text' => RC_Lang::lang('back_favourable_list'), 'href' => RC_Uri::url('favourable/admin/init'));
		$links[] = array('text' => RC_Lang::lang('continue_add_favourable'), 'href' => RC_Uri::url('favourable/admin/add'));
		$this->showmessage(RC_Lang::lang('add_favourable_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('favourable/admin/edit', array('act_id' => $favourable['act_id']))));
	}
	
	/**
	 * 编辑
	 */
	public function edit() {
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
		
		$this->assign('ur_here', '编辑优惠活动');
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(__('编辑优惠活动')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> __('概述'),
			'content'	=>
			'<p>' . __('欢迎访问ECJia智能后台编辑优惠活动页面，可以在此页面对相应的优惠活动进行编辑。') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('更多信息:') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:优惠活动#.E7.BC.96.E8.BE.91.E4.BC.98.E6.83.A0.E6.B4.BB.E5.8A.A8" target="_blank">关于编辑优惠活动帮助文档</a>') . '</p>'
		);
		
		$this->assign('action_link', array('text' => '优惠活动列表', 'href' => RC_Uri::url('favourable/admin/init')));
		$id   = !empty($_GET['act_id']) ? intval($_GET['act_id']) : 0;
		
		$favourable = favourable_info($id);
		if (empty($favourable)) {
			$this->showmessage(RC_Lang::lang('favourable_not_exist'));
		}
		$this->assign('favourable', $favourable);
		
		/* 取得用户等级 */
		$user_rank_list = array();
		$user_rank_list[] = array(
			'rank_id'   => 0,
			'rank_name' => RC_Lang::lang('not_user'),
			'checked'   => strpos(',' . $favourable['user_rank'] . ',', ',0,') !== false
		);

		$data = $this->db_user_rank->field('rank_id, rank_name')->select();

		if (!empty($data)) {
			foreach ($data as $row) {
				$row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $row['rank_id']. ',') !== false;
				$user_rank_list[] = $row;
			}
		}
		$this->assign('user_rank_list', $user_rank_list);
		
		/* 取得优惠范围 */
		$act_range_ext = array();
		if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
			if ($favourable['act_range'] == FAR_CATEGORY) {
				$act_range_ext = $this->db_category->field('cat_id AS id, cat_name AS name')->in(array('cat_id'=>$favourable['act_range_ext']))->select();
			} elseif ($favourable['act_range'] == FAR_BRAND) {
				$act_range_ext = $this->db_brand->field('brand_id AS id, brand_name AS name')->in(array('brand_id'=>$favourable['act_range_ext']))->select();
			} else {
				$act_range_ext = $this->db_goods->field('goods_id AS id, goods_name AS name')->in(array('goods_id'=>$favourable['act_range_ext']))->select();
			}
		}
		$this->assign('act_range_ext', $act_range_ext);
		$this->assign('form_action', RC_Uri::url('favourable/admin/update'));
		$this->assign_lang();
		
		$this->display('favourable_info.dwt');
	}
	
	/**
	 * 编辑处理
	 */
	public function update() { 
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
		
		$act_name = !empty($_POST['act_name']) ? trim($_POST['act_name']) : '';
		$old_actname = !empty($_POST['old_actname']) ? trim($_POST['old_actname']) : '';
		if ($act_name != $old_actname ) {
			if ($this->db_favourable_activity->where(array('act_name' => $act_name))->count() > 0) {
				$this->showmessage(RC_Lang::lang('act_name_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
			}
		}

		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 检查享受优惠的会员等级 */
		if (!isset($_POST['user_rank'])) {
			$this->showmessage(RC_Lang::lang('pls_set_user_rank'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 检查优惠范围扩展信息 */
		if (intval($_POST['act_range']) > 0 && !isset($_POST['act_range_ext'])) {
			$this->showmessage('请设置优惠范围', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 检查金额上下限 */
		$min_amount = floatval($_POST['min_amount']) >= 0 ? floatval($_POST['min_amount']) : 0;
		$max_amount = floatval($_POST['max_amount']) >= 0 ? floatval($_POST['max_amount']) : 0;
		if ($max_amount > 0 && $min_amount > $max_amount) {
			$this->showmessage(RC_Lang::lang('amount_error'));
		}
		
		/* 取得赠品 */
		$gift = array();
		if (intval($_POST['act_type']) == FAT_GOODS && isset($_POST['gift_id'])) {
			foreach ($_POST['gift_id'] as $key => $id) {
				$gift[] = array('id' => $id, 'name' => $_POST['gift_name'][$key], 'price' => $_POST['gift_price'][$key]);
			}
		}
		
		/* 提交值 */
		$favourable = array(
			'act_id'        => intval($_POST['id']),
			'act_name'      => $act_name,
			'start_time'    => RC_Time::local_strtotime($_POST['start_time']),
			'end_time'      => RC_Time::local_strtotime($_POST['end_time']),
			'user_rank'     => isset($_POST['user_rank']) ? join(',', $_POST['user_rank']) : '0',
			'act_range'     => intval($_POST['act_range']),
			'act_range_ext' => intval($_POST['act_range']) == 0 ? '' : join(',', $_POST['act_range_ext']),
			'min_amount'    => floatval($_POST['min_amount']),
			'max_amount'    => floatval($_POST['max_amount']),
			'act_type'      => intval($_POST['act_type']),
			'act_type_ext'  => floatval($_POST['act_type_ext']),
			'gift'          => serialize($gift)
		);
		if ($favourable['act_type'] == FAT_GOODS) {
			$favourable['act_type_ext'] = round($favourable['act_type_ext']);
		}
		
		/* 保存数据 */		
		$this->db_favourable_activity->where('act_id = '.$favourable['act_id'].'')->update($favourable);
		/*管理员记录日志*/
        if (intval($_POST['act_type']) == 0) {
            $act_type = '享受赠品（特惠品）';
        } elseif (intval($_POST['act_type']) == 1) {
            $act_type = '享受现金减免';
        } elseif (intval($_POST['act_type']) == 2) {
            $act_type = '享受价格折扣';
        }
        
        $content = '优惠活动方式是 '.$act_type.'，优惠活动名称是 '.$act_name;
        ecjia_admin::admin_log($content, 'edit', 'discount');

		$links = array(array('href' => RC_Uri::url('favourable/admin/init'), 'text' => RC_Lang::lang('back_favourable_list')));
		$this->showmessage(RC_Lang::lang('edit_favourable_ok'), ecjia::MSGTYPE_JSON|ecjia::MSGSTAT_SUCCESS);
	}

	/**
	 * 删除
	 */
	public function remove() {
		$this->admin_priv('favourable_delete', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}

		$id = intval($_GET['act_id']);
		$favourable = favourable_info($id);
		$name = $favourable['act_name'];
		$this->db_favourable_activity->where(array('act_id' => $id))->delete();
		
		/*管理员记录日志*/
        if (intval($favourable['act_type']) == 0) {
            $act_type = '享受赠品（特惠品）';
        } elseif (intval($favourable['act_type']) == 1) {
            $act_type = '享受现金减免';
        } elseif(intval($favourable['act_type']) == 2) {
            $act_type = '享受价格折扣';
        }
        $content = '优惠活动方式是 '.$act_type.'，优惠活动名称是 '.$name;
        ecjia_admin::admin_log($content, 'remove', 'discount');
		
		$this->showmessage('删除优惠活动'.'['.$name.']'. '成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	/**
	 * 批量操作
	 */
	public function batch() {
		$this->admin_priv('favourable_delete', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$ids = $_POST['act_id'];
		
		$info = $this->db_favourable_activity->field('act_name, act_type')->in(array('act_id' => $ids))->select();
		$this->db_favourable_activity->in(array('act_id'=>$ids))->delete();
		
		if (!empty($info)) {
			foreach ($info as $v) {
				if (intval($v['act_type']) == 0) {
					$act_type = '享受赠品（特惠品）';
				} elseif (intval($v['act_type']) == 1) {
					$act_type = '享受现金减免';
				} elseif(intval($v['act_type']) == 2) {
					$act_type = '享受价格折扣';
				}
				$content = '优惠活动方式是 '.$act_type.'，优惠活动名称是 '.$v['act_name'];
				ecjia_admin::admin_log($content, 'batch_remove', 'discount');
			}
		}
		$this->showmessage(RC_Lang::lang('batch_drop_ok'), ecjia::MSGTYPE_JSON|ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('favourable/admin/init')));
	}
	/**
	 * 编辑优惠活动名称
	 */
	public function edit_act_name() {
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$actname  = trim($_POST['value']);
		$id		  = intval($_POST['pk']);
		$old_actname = $this->db_favourable_activity->where(array('act_id' => $id))->get_field('act_name');
		if (!empty($actname)) {
			if ($actname != $old_actname) {
				if ($this->db_favourable_activity->where(array('act_name' => $actname))->count() == 0) {
					$this->db_favourable_activity->where(array('act_id' => $id))->update(array('act_name' => $actname));
					$this->showmessage('更新优惠活动名称成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
				} else {
					$this->showmessage('该优惠活动名称已存在，请您换一个', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
		} else {
			$this->showmessage('请输入优惠活动名称', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	/**
	 * 修改排序
	 */
	public function edit_sort_order() {
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
		
		if (!empty($_SESSION['ru_id'])) {
			$this->showmessage(__('入驻商家没有操作权限，请登陆商家后台操作！'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$id  = intval($_POST['pk']);
		$val = intval($_POST['value']);
		$data = array('sort_order' => $val);
		$this->db_favourable_activity->where(array('act_id' => $id))->update($data);
		$this->showmessage('排序操作成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_uri::url('favourable/admin/init')) );
	}
	
	/**
	 * 搜索商品
	 */
	public function search() {
		$this->admin_priv('favourable_manage', ecjia::MSGTYPE_JSON);
		$act_range = !empty($_POST['act_range']) ? intval($_POST['act_range']) : 0;
		$keyword = !empty($_POST['keyword']) ? $_POST['keyword'] : '';
		
		if ($act_range == FAR_ALL) {		//全部商品
			$arr[0] = array(
				'id'   => 0,
				'name' => RC_Lang::lang('js_languages/all_need_not_search')
			);
		} elseif ($act_range == FAR_CATEGORY) {		//按分类选择
			if(empty($keyword)) {
				$arr = $this->db_category->field('cat_id AS id, cat_name AS name')->select();
				RC_Loader::load_app_func('category', 'goods');
				$result = cat_list(0, 0, false);
				$arr = array();
				if (!empty($result)) {
					foreach ($result as $key=>$row) {
						$arr[$key]['id'] = $row['cat_id'];
						$arr[$key]['level'] = $row['level'];
						$arr[$key]['name'] = $row['cat_name'];
					}
					$arr = array_merge($arr);
				}
			} else {
				$arr = $this->db_category->field('cat_id AS id, cat_name AS name')->where(array('cat_name' => array('like' => "%".mysql_like_quote($keyword)."%")))->select();
			}
		} elseif ($act_range == FAR_BRAND) {//按品牌选择
			if (empty($keyword)) {
				$arr = $this->db_brand->field('brand_id AS id, brand_name AS name')->select();
			} else {
				$arr = $this->db_brand->field('brand_id AS id, brand_name AS name')->where(array('brand_name' => array('like' => "%".mysql_like_quote($keyword)."%")))->select();
			}
		} else {
			if (empty($keyword)) {
				$arr = $this->db_goods->field('goods_id AS id, goods_name AS name')->select();
			} else {
				$arr = $this->db_goods->field('goods_id AS id, goods_name AS name')->where(array('goods_name' => array('like' => "%".mysql_like_quote($keyword)."%")))->select();
			}
			if (!empty($arr)) {
				foreach ($arr as $key => $row) {
					$arr[$key]['url'] = RC_Uri::url('goods/admin/preview','id='.$row['id']);
				}
			}
		}
		if (empty($arr)) {
			$arr = array(0 => array(
				'id'   => 0,
				'name' => RC_Lang::lang('search_result_empty')
			));
		}
		$this->showmessage('排序操作成功！', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $arr) );
	}
}
//end
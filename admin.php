<?php
/**
 * 管理中心优惠活动管理
 * @author songqian
 * 
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
		
		RC_Loader::load_app_func('favourable');
		$this->db_favourable_activity 	= RC_Model::model('favourable/favourable_activity_model');
		$this->db_user_rank 			= RC_Model::model('favourable/favourable_user_rank_model');
		$this->db_category 				= RC_Model::model('favourable/favourable_category_model');
		$this->db_brand 				= RC_Model::model('favourable/favourable_brand_model');
		$this->db_goods 				= RC_Model::model('favourable/favourable_goods_model');
		
		/* 加载全局 js/css */
		RC_Script::enqueue_script('jquery-validate');
		RC_Script::enqueue_script('jquery-form');
		RC_Script::enqueue_script('smoke');
		RC_Script::enqueue_script('bootstrap-editable.min', RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/js/bootstrap-editable.min.js'), array(), false, false);
		RC_Style::enqueue_style('bootstrap-editable',RC_Uri::admin_url('statics/lib/x-editable/bootstrap-editable/css/bootstrap-editable.css'), array(), false, false);
		RC_Style::enqueue_style('chosen');
		RC_Style::enqueue_style('uniform-aristo');
		RC_Script::enqueue_script('datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datetimepicker.min.js'));
		RC_Style::enqueue_style('datepicker', RC_Uri::admin_url('statics/lib/datepicker/bootstrap-datetimepicker.min.css'));
		RC_Script::enqueue_script('jquery-uniform');
		RC_Script::enqueue_script('jquery-chosen');
		RC_Script::enqueue_script('favourable_list', RC_App::apps_url('statics/js/favourable_list.js', __FILE__));
		
		RC_Script::localize_script('favourable_list', 'js_lang', RC_Lang::get('favourable::favourable.js_lang'));
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('favourable::favourable.favourable_list'), RC_Uri::url('favourable/admin/init')));
	}
	
	/**
	 * 活动列表页
	 */
	public function init() {
		$this->admin_priv('favourable_manage', ecjia::MSGTYPE_JSON);
	
		ecjia_screen::get_current_screen()->remove_last_nav_here();
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('favourable::favourable.favourable_list')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('favourable::favourable.overview'),
			'content'	=> '<p>' . RC_Lang::get('favourable::favourable.favourable_list_help') . '</p>'
		));
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('favourable::favourable.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:优惠活动" target="_blank">'.RC_Lang::get('favourable::favourable.about_favourable_list').'</a>') . '</p>'
		);
		
		$this->assign('ur_here', RC_Lang::get('favourable::favourable.favourable_list'));
		$this->assign('action_link', array('href' => RC_Uri::url('favourable/admin/add'), 'text' => RC_Lang::get('favourable::favourable.add_favourable')));
		
		$list = $this->get_favourable_list();
		
		$this->assign('favourable_list', $list);
		$this->assign('search_action', RC_Uri::url('favourable/admin/init'));

		$this->display('favourable_list.dwt');
	}
	
	/**
	 * 添加页面
	 */
	public function add() {
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
	
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('favourable::favourable.add_favourable')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('favourable::favourable.overview'),
			'content'	=> '<p>' . RC_Lang::get('favourable::favourable.add_favourable_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('favourable::favourable.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:优惠活动#.E6.B7.BB.E5.8A.A0.E4.BC.98.E6.83.A0.E6.B4.BB.E5.8A.A8" target="_blank">'.RC_Lang::get('favourable::favourable.about_add_favourable').'</a>') . '</p>'
		);
		$this->assign('ur_here', RC_Lang::get('favourable::favourable.add_favourable'));
		$this->assign('action_link', array('text' => RC_Lang::get('favourable::favourable.favourable_list'), 'href' => RC_Uri::url('favourable/admin/init')));
		
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
		
		$user_rank_list = array();
		$user_rank_list[] = array(
			'rank_id'   => 0,
			'rank_name' => RC_Lang::get('favourable::favourable.not_user'),
			'checked'   => strpos(',' . $favourable['user_rank'] . ',', ',0,') !== false
		);
		$data = $this->db_user_rank->user_rank_select('rank_id, rank_name');

		if (!empty($data)) {
			foreach ($data as $key => $row) {
				$row['checked'] = strpos(',' . $favourable['user_rank'] . ',', ',' . $key. ',') !== false;
				$user_rank_list[] = $row;
			}
		}
		$this->assign('user_rank_list', $user_rank_list);
		
		$act_range_ext = array();
		if ($favourable['act_range'] != FAR_ALL && !empty($favourable['act_range_ext'])) {
			if ($favourable['act_range'] == FAR_CATEGORY) {
				$act_range_ext = $this->db_category->category_select(array('cat_id' => $favourable['act_range_ext']), true, 'cat_id AS id, cat_name AS name');
			} elseif ($favourable['act_range'] == FAR_BRAND) {
				$act_range_ext = $this->db_brand->category_select(array('brand_id' => $favourable['act_range_ext']), true, 'brand_id AS id, brand_name AS name');
			} else {
				$act_range_ext = $this->db_goods->category_select(array('goods_id' => $favourable['act_range_ext']), true, 'goods_id AS id, goods_name AS name');
			}
		}
		$this->assign('act_range_ext', $act_range_ext);
		$this->assign('form_action', RC_Uri::url('favourable/admin/insert'));
		
		$this->display('favourable_info.dwt');
	}
	
	/**
	 * 添加处理
	 */
	public function insert() {
		$this->admin_priv('favourable_update' ,ecjia::MSGTYPE_JSON);
		
		$act_name = !empty($_POST['act_name']) ? trim($_POST['act_name']) : '';
		if ($this->db_favourable_activity->is_only(array('act_name' => $act_name)) > 0) {
			$this->showmessage(RC_Lang::get('favourable::favourable.act_name_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$start_time = !empty($_POST['start_time']) ? RC_Time::local_strtotime($_POST['start_time']) : '';
		$end_time = !empty($_POST['end_time']) ? RC_Time::local_strtotime($_POST['end_time']) : '';
		
		if ($start_time >= $end_time) {
			$this->showmessage(RC_Lang::get('favourable::favourable.start_lt_end'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 检查享受优惠的会员等级 */
		if (!isset($_POST['user_rank']) || empty($_POST['user_rank'])) {
			$this->showmessage(RC_Lang::get('favourable::favourable.pls_set_user_rank'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		/* 检查优惠范围扩展信息 */
		if ($_POST['act_range'] > 0 && !isset($_POST['act_range_ext'])) {
			$this->showmessage(RC_Lang::get('favourable::favourable.pls_set_act_range'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 检查金额上下限 */
		$min_amount = floatval($_POST['min_amount']) >= 0 ? floatval($_POST['min_amount']) : 0;
		$max_amount = floatval($_POST['max_amount']) >= 0 ? floatval($_POST['max_amount']) : 0;
		if ($max_amount > 0 && $min_amount > $max_amount) {
			$this->showmessage(RC_Lang::get('favourable::favourable.amount_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
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
			'act_name'      => $act_name,
			'start_time'    => $start_time,
			'end_time'      => $end_time,
			'user_rank'     => isset($_POST['user_rank']) ? join(',', $_POST['user_rank']) : '0',
			'act_range'     => intval($_POST['act_range']),
			'act_range_ext' => intval($_POST['act_range']) == 0 ? '' : join(',', $_POST['act_range_ext']),
			'min_amount'    => $min_amount,
			'max_amount'    => $max_amount,
			'act_type'      => intval($_POST['act_type']),
			'act_type_ext'  => floatval($_POST['act_type_ext']),
			'gift'          => serialize($gift)
		);

		if ($favourable['act_type'] == FAT_GOODS) {
			$favourable['act_type_ext'] = round($favourable['act_type_ext']);
		}
		if ($favourable['act_type'] == 0) {
			$act_type = RC_Lang::get('favourable::favourable.fat_goods');
		} elseif ($favourable['act_type'] == 1) {
			$act_type = RC_Lang::get('favourable::favourable.fat_price');
		} else {
			$act_type = RC_Lang::get('favourable::favourable.fat_discount');
		}
		$result = $this->db_favourable_activity->favourable_manage($favourable);
		
		ecjia_admin::admin_log($favourable['act_name'].'，'.RC_Lang::get('favourable::favourable.favourable_way_is').$act_type, 'add', 'favourable');
		$links[] = array('text' => RC_Lang::get('favourable::favourable.back_favourable_list'), 'href' => RC_Uri::url('favourable/admin/init'));
		$links[] = array('text' => RC_Lang::get('favourable::favourable.continue_add_favourable'), 'href' => RC_Uri::url('favourable/admin/add'));
		
		$this->showmessage(RC_Lang::get('favourable::favourable.add_favourable_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('links' => $links, 'pjaxurl' => RC_Uri::url('favourable/admin/edit', array('act_id' => $result))));
	}
	
	/**
	 * 编辑
	 */
	public function edit() {
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
		
		ecjia_screen::get_current_screen()->add_nav_here(new admin_nav_here(RC_Lang::get('favourable::favourable.edit_favourable')));
		ecjia_screen::get_current_screen()->add_help_tab(array(
			'id'		=> 'overview',
			'title'		=> RC_Lang::get('favourable::favourable.overview'),
			'content'	=> '<p>' . RC_Lang::get('favourable::favourable.edit_favourable_help') . '</p>'
		));
		
		ecjia_screen::get_current_screen()->set_help_sidebar(
			'<p><strong>' . RC_Lang::get('favourable::favourable.more_info') . '</strong></p>' .
			'<p>' . __('<a href="https://ecjia.com/wiki/帮助:ECJia智能后台:优惠活动#.E7.BC.96.E8.BE.91.E4.BC.98.E6.83.A0.E6.B4.BB.E5.8A.A8" target="_blank">'.RC_Lang::get('favourable::favourable.about_edit_favourable').'</a>') . '</p>'
		);
		
		$this->assign('ur_here', RC_Lang::get('favourable::favourable.edit_favourable'));
		$this->assign('action_link', array('text' => RC_Lang::get('favourable::favourable.favourable_list'), 'href' => RC_Uri::url('favourable/admin/init')));
		
		$id = !empty($_GET['act_id']) ? intval($_GET['act_id']) : 0;
		$favourable = $this->db_favourable_activity->favourable_info($id);
		if (empty($favourable)) {
			$this->showmessage(RC_Lang::get('favourable::favourable.favourable_not_exist'), ecjia::MSGTYPE_HTML | ecjia::MSGSTAT_ERROR);
		}
		
		$this->assign('favourable', $favourable);
		$this->assign('user_rank_list', $favourable['user_rank_list']);
		$this->assign('act_range_ext', $favourable['act_range_ext']);
		
		$this->assign('form_action', RC_Uri::url('favourable/admin/update'));
		
		$this->display('favourable_info.dwt');
	}
	
	/**
	 * 编辑处理
	 */
	public function update() { 
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
		
		$act_name 	= !empty($_POST['act_name']) 	? trim($_POST['act_name']) : '';
		$act_id 	= !empty($_POST['act_id']) 		? intval($_POST['act_id']) : 0;
		if ($this->db_favourable_activity->is_only(array('act_name' => $act_name, 'act_id' => array('neq' => $act_id))) > 0) {
			$this->showmessage(RC_Lang::get('favourable::favourable.act_name_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		$start_time = !empty($_POST['start_time'])	? RC_Time::local_strtotime($_POST['start_time']) 	: '';
		$end_time 	= !empty($_POST['end_time']) 	? RC_Time::local_strtotime($_POST['end_time']) 		: '';
		/* 检查优惠活动时间 */
		if ($start_time >= $end_time) {
			$this->showmessage(RC_Lang::get('favourable::favourable.start_lt_end'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		/* 检查享受优惠的会员等级 */
		if (!isset($_POST['user_rank']) || empty($_POST['user_rank'])) {
			$this->showmessage(RC_Lang::get('favourable::favourable.pls_set_user_rank'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	
		/* 检查优惠范围扩展信息 */
		if ($_POST['act_range'] > 0 && !isset($_POST['act_range_ext'])) {
			$this->showmessage(RC_Lang::get('favourable::favourable.pls_set_act_range'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		/* 检查金额上下限 */
		$min_amount = floatval($_POST['min_amount']) >= 0 ? floatval($_POST['min_amount']) : 0;
		$max_amount = floatval($_POST['max_amount']) >= 0 ? floatval($_POST['max_amount']) : 0;
		if ($max_amount > 0 && $min_amount > $max_amount) {
			$this->showmessage(RC_Lang::get('favourable::favourable.amount_error'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
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
			'act_id'		=> $act_id,
			'act_name'      => $act_name,
			'start_time'    => $start_time,
			'end_time'      => $end_time,
			'user_rank'     => isset($_POST['user_rank']) ? join(',', $_POST['user_rank']) : '0',
			'act_range'     => intval($_POST['act_range']),
			'act_range_ext' => intval($_POST['act_range']) == 0 ? '' : join(',', $_POST['act_range_ext']),
			'min_amount'    => $min_amount,
			'max_amount'    => $max_amount,
			'act_type'      => intval($_POST['act_type']),
			'act_type_ext'  => floatval($_POST['act_type_ext']),
			'gift'          => serialize($gift)
		);

		if ($favourable['act_type'] == FAT_GOODS) {
			$favourable['act_type_ext'] = round($favourable['act_type_ext']);
		}
		
		if ($favourable['act_type'] == 0) {
			$act_type = RC_Lang::get('favourable::favourable.fat_goods');
		} elseif ($favourable['act_type'] == 1) {
			$act_type = RC_Lang::get('favourable::favourable.fat_price');
		} else {
			$act_type = RC_Lang::get('favourable::favourable.fat_discount');
		}
		$this->db_favourable_activity->favourable_manage($favourable);
		
		ecjia_admin::admin_log($favourable['act_name'].'，'.RC_Lang::get('favourable::favourable.favourable_way_is').$act_type, 'edit', 'favourable');
		$this->showmessage(RC_Lang::get('favourable::favourable.edit_favourable_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('favourable/admin/edit', array('act_id' => $_POST['act_id']))));
	}

	/**
	 * 删除
	 */
	public function remove() {
		$this->admin_priv('favourable_delete', ecjia::MSGTYPE_JSON);
		
		$id = intval($_GET['act_id']);
		$favourable = $this->db_favourable_activity->favourable_info($id);
		if (empty($favourable)) {
			$this->showmessage(RC_Lang::get('favourable::favourable.favourable_not_exist'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
		
		$name = $favourable['act_name'];
		$act_type = $favourable['act_type'];
		
		if ($act_type == 0) {
			$act_type = RC_Lang::get('favourable::favourable.fat_goods');
		} elseif ($act_type == 1) {
			$act_type = RC_Lang::get('favourable::favourable.fat_price');
		} else {
			$act_type = RC_Lang::get('favourable::favourable.fat_discount');
		}

		$this->db_favourable_activity->favourable_remove($id);
		
		ecjia_admin::admin_log($name.'，'.RC_Lang::get('favourable::favourable.favourable_way_is').$act_type, 'remove', 'favourable');
		$this->showmessage(RC_Lang::get('favourable::favourable.remove_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
	}
	
	/**
	 * 批量操作
	 */
	public function batch() {
		$this->admin_priv('favourable_delete');
		
		$ids = $_POST['act_id'];
		$info = $this->db_favourable_activity->favourable_select(array('act_id' => $ids));
		$this->db_favourable_activity->favourable_remove($ids, true);
		
		if (!empty($info)) {
			foreach ($info as $v) {
				if ($v['act_type'] == 0) {
					$act_type = RC_Lang::get('favourable::favourable.fat_goods');
				} elseif ($v['act_type'] == 1) {
					$act_type = RC_Lang::get('favourable::favourable.fat_price');
				} else {
					$act_type = RC_Lang::get('favourable::favourable.fat_discount');
				}
				ecjia_admin::admin_log($v['act_name'].'，'.RC_Lang::get('favourable::favourable.favourable_way_is').$act_type, 'batch_remove', 'favourable');
			}
		}
		$this->showmessage(RC_Lang::get('favourable::favourable.batch_drop_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_Uri::url('favourable/admin/init')));
	}
	/**
	 * 编辑优惠活动名称
	 */
	public function edit_act_name() {
		$this->admin_priv('favourable_update');
		
		$actname = trim($_POST['value']);
		$id	= intval($_POST['pk']);
		$old_actname = $this->db_favourable_activity->favourable_field(array('act_id' => $id), 'act_name');
		if (!empty($actname)) {
			if ($actname != $old_actname) {
				if ($this->db_favourable_activity->is_only(array('act_name' => $actname)) == 0) {
					$data = array(
						'act_id'	=> $id,
						'act_name'	=> $actname
					);
					$this->db_favourable_activity->favourable_manage($data);
					$this->showmessage(RC_Lang::get('favourable::favourable.edit_name_success'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS);
				} else {
					$this->showmessage(RC_Lang::get('favourable::favourable.act_name_exists'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
				}
			}
		} else {
			$this->showmessage(RC_Lang::get('favourable::favourable.pls_enter_name'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_ERROR);
		}
	}
	
	/**
	 * 修改排序
	 */
	public function edit_sort_order() {
		$this->admin_priv('favourable_update', ecjia::MSGTYPE_JSON);
		
		$id  = intval($_POST['pk']);
		$val = intval($_POST['value']);
		$data = array(
			'act_id' 		=> $id,
			'sort_order' 	=> $val
		);
		$this->db_favourable_activity->favourable_manage($data);
		$this->showmessage(RC_Lang::get('favourable::favourable.sort_edit_ok'), ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('pjaxurl' => RC_uri::url('favourable/admin/init')) );
	}
	
	/**
	 * 搜索商品
	 */
	public function search() {
		$this->admin_priv('favourable_manage', ecjia::MSGTYPE_JSON);
		$act_range = !empty($_POST['act_range']) ? $_POST['act_range'] : 0;
		$keyword = !empty($_POST['keyword']) ? trim($_POST['keyword']) : '';
		$where = array();
		if ($act_range == FAR_ALL) {//全部商品
			$arr[0] = array(
				'id'   => 0,
				'name' => RC_Lang::get('favourable::favourable.all_need_not_search')
			);
		} elseif ($act_range == FAR_CATEGORY) {//按分类选择
			if (empty($keyword)) {
				$arr = $this->db_category->category_select(array(), false, 'cat_id AS id, cat_name AS name');
				RC_Loader::load_app_func('category', 'goods');
				$result = cat_list(0, 0, false);
				$arr = array();
				if (!empty($result)) {
					foreach ($result as $key => $row) {
						$arr[$key]['id'] 	= $row['cat_id'];
						$arr[$key]['level'] = $row['level'];
						$arr[$key]['name'] 	= $row['cat_name'];
					}
					$arr = array_merge($arr);
				}
			} else {
				$arr = $this->db_category->category_select(array('cat_name' => array('like' => "%".mysql_like_quote($keyword)."%")), false, 'cat_id AS id, cat_name AS name');
			}
		} elseif ($act_range == FAR_BRAND) {//按品牌选择
			if (!empty($keyword)) {
				$where = array('brand_name' => array('like' => "%".mysql_like_quote($keyword)."%"));
			}
			$arr = $this->db_brand->brand_select($where, false, 'brand_id AS id, brand_name AS name');
		} else {
			if (!empty($keyword)) {
				$where = array('goods_name' => array('like' => "%".mysql_like_quote($keyword)."%"));
			}
			$arr = $this->db_goods->goods_select($where, false, 'goods_id AS id, goods_name AS name');
			if (!empty($arr)) {
				foreach ($arr as $key => $row) {
					$arr[$key]['url'] = RC_Uri::url('goods/admin/preview', 'id='.$row['id']);
				}
			}
		}
		if (empty($arr)) {
			$arr = array(0 => array(
				'id'   => 0,
				'name' => RC_Lang::get('favourable::favourable.search_result_empty')
			));
		}
		$this->showmessage('', ecjia::MSGTYPE_JSON | ecjia::MSGSTAT_SUCCESS, array('content' => $arr));
	}
	
	/*
	 * 取得优惠活动列表
	*/
	private function get_favourable_list() {
		$db_favourable_activity	= RC_Model::model('favourable/favourable_activity_model');
		
		$filter['sort_by']    	= empty($_GET['sort_by']) 	? 'act_id' 				: trim($_GET['sort_by']);
		$filter['sort_order'] 	= empty($_GET['sort_order'])? 'DESC' 				: trim($_GET['sort_order']);
		$filter['keyword']		= empty($_GET['keyword']) 	? '' 					: trim($_GET['keyword']);
		$filter['type'] 	 	= isset($_GET['type']) 		? trim($_GET['type']) 	: '';
		
		/* 过滤条件 */
		$where = array();
		if (!empty($filter['keyword'])) {
			$where['act_name'] = array('like' => "%" . mysql_like_quote($filter['keyword']) . "%");
		}
		$time = RC_Time::gmtime();
	
		$field = 'count(*) as count, SUM(IF(start_time <'.$time.' and end_time > '.$time.', 1, 0)) as on_going';
		$type_count = $db_favourable_activity->favourable_find($where, $field);
	
		if ($filter['type']  == 'on_going') {
			$where['start_time'] = array('elt' => $time);
			$where['end_time'] = array('egt' => $time);
		}
	
		$count = $db_favourable_activity->where($where)->count();
		$page = new ecjia_page($count, 10, 5);
		$option = array(
			'where' => $where,
			'order' => array('sort_order' => 'asc', $filter['sort_by'] => $filter['sort_order']),
			'limit'	=> $page->limit()
		);
		$data = $db_favourable_activity->get_favourable_list($option);
		
		$list = array();
		if (!empty($data)) {
			foreach ($data as $row) {
				$row['start_time']  = RC_Time::local_date('Y-m-d H:i', $row['start_time']);
				$row['end_time']    = RC_Time::local_date('Y-m-d H:i', $row['end_time']);
				$list[] = $row;
			}
		}
		return array('item' => $list, 'page' => $page->show(5), 'desc' => $page->page_desc(), 'count' => $type_count);
	}
}

//end
<?php
defined('IN_ECJIA') or exit('No permission resources.');

/**
 * 满减满赠活动信息
 * @author will
 *
 */
class info_module implements ecjia_interface {
	
	public function run(ecjia_api & $api) {
		
		$ecjia = RC_Loader::load_app_class('api_admin', 'api');
		$ecjia->authadminSession();
		
		$id = _POST('act_id', 0);
		if ($id <= 0) {
			EM_Api::outPut(101);
		}
	
		$result = RC_Model::Model('favourable/favourable_activity_model')->favourable_info($id);
		/* 多商户处理*/
		if (isset($_SESSION['ru_id']) && $_SESSION['ru_id'] > 0 && $result['user_id'] != $_SESSION['ru_id']) {
			EM_Api::outPut(8);
		}
		
		if (!empty($result)) {
			if ($result['act_range'] == 0) {
				$result['label_act_range'] = __('全部商品');
			} elseif ($result['act_range'] == 1) {
				$result['label_act_range'] = __('指定分类');
				if (!empty($result['act_range_ext'])) {
					$db_category = RC_Loader::load_app_model('category_model', 'goods');
					foreach ($result['act_range_ext'] as $key => $val) {
						$image = $db_category->where(array('cat_id' => $val['id']))->get_field('style');
						$result['act_range_ext'][$key]['image'] = !empty($image) ? RC_Upload::upload_url($image) : '';
					}
				}
			} elseif ($result['act_range'] == 2) {
				$result['label_act_range'] = __('指定品牌');
				if (!empty($result['act_range_ext'])) {
					$db_brand = RC_Loader::load_app_model('brand_model', 'goods');
					$db_merchants_brand_db = RC_Loader::load_app_model('merchants_shop_brand_viewmodel', 'goods');
					
					foreach ($result['act_range_ext'] as $key => $val) {
						if (!isset($_SESSION['ru_id'])) {
							$image = $db_brand->where(array('brand_id' => $val['id']))->get_field('brand_logo');
						} else {
							$image = $db_merchants_brand_db->where(array('bid' => $val['id']))->get_field('brandLogo');
						}
						if (strpos($image, 'http://') === false) {
							$result['act_range_ext'][$key]['image']	= !empty($image) ? RC_Upload::upload_url($image) : '';
						} else {
							$result['act_range_ext'][$key]['image'] = $image;
						}
					}
				}
				
			} else {
				$result['label_act_range'] = __('指定商品');
				if (!empty($result['act_range_ext'])) {
					$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
					foreach ($result['act_range_ext'] as $key => $val) {
						$image = $db_goods->where(array('goods_id' => $val['id']))->get_field('original_img');
						if (strpos($image, 'http://') === false) {
							$result['act_range_ext'][$key]['image']	= !empty($image) ? RC_Upload::upload_url($image) : '';
						} else {
							$result['act_range_ext'][$key]['image'] = $image;
						}
					}
				}
			}
			
			$result['gift_items'] = array();
			if ($result['act_type'] == 0) {
				$result['label_act_type'] = __('特惠品');
				if (!empty($result['gift'])) {
					$db_goods = RC_Loader::load_app_model('goods_model', 'goods');
					foreach ($result['gift'] as $val) {
						$info = $db_goods->field(array('goods_name', 'shop_price', 'original_img'))->where(array('goods_id' => $val['id']))->find();
						if (strpos($info['original_img'], 'http://') === false) {
							$image = !empty($info['original_img']) ? RC_Upload::upload_url($info['original_img']) : '';
						} else {
							$image = $info['original_img'];
						}
						$result['gift_items'][] = array(
								'id'	=> $val['id'],
								'name'	=> $info['goods_name'],
								'shop_price' => $info['shop_price'],
								'formatted_shop_price' => price_format($info['shop_price']),
								'price' => $val['price'],
								'image' => $image,
						);
					}
				}
			} elseif ($result['act_type'] == 1) {
				$result['label_act_type'] = __('现金减免');
			} else {
				$result['label_act_type'] = __('价格折扣');
			}
			
			$result['formatted_start_time'] = $result['start_time'];
			$result['formatted_end_time'] = $result['end_time'];
			
			/* 去除不要的字段*/
			unset($result['start_time']);
			unset($result['end_time']);
			unset($result['gift']);
			unset($result['user_rank']);
			unset($result['sort_order']);
			return $result;
		} else {
			EM_Api::outPut(13);
		}
	}
}
// end
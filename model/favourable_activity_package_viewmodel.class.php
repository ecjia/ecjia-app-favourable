<?php
defined('IN_ECJIA') or exit('No permission resources.');

class favourable_activity_package_viewmodel extends Component_Model_View {
	public $table_name = '';
	public $view = array();
	public function __construct() {
		$this->db_config = RC_Config::load_config('database');
		$this->db_setting = 'default';
		$this->table_name = 'favourable_activity';
		$this->table_alias_name = 'fa';
		
		$this->view = array(
			'merchants_shop_information' => array(
				'type' 		=> Component_Model_View::TYPE_LEFT_JOIN,
				'alias' 	=> 'msi',
				'on' 		=> 'msi.user_id = fa.user_id'
		    )
		);	
		parent::__construct();
	}
}

// end
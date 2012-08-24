<?php

define('BGCS_SETTING_SEARCH_ENGINE_UID', 'search_engine_uid');
define('BGCS_SETTING_PAGE_SIZE', 'page_size');

class GoogleXmlSiteSearchAdmin {

	private $option_name = 'bgcs_options';

	public function add_option() {
		add_option($this->option_name, $this->get_option_defaults());
	}

	public function delete_option() {
		delete_option($this->option_name);
	}

	public function admin_menu() {
		add_options_page('Google XML Site Search', 'Google XML Site Search', 'manage_options', 'bgcs', array($this, 'admin_options'));
	}

	public function admin_init() {
		register_setting('bgcs_options', 'bgcs_options', array($this, 'validate_options'));
		add_settings_section('bgcs_main', 'Main Settings', array($this, 'main_general_text'), 'bgcs');
		add_settings_field(BGCS_SETTING_SEARCH_ENGINE_UID, 'Search Engine Unique ID', array($this, 'search_engine_uid'), 'bgcs', 'bgcs_main');
		add_settings_field(BGCS_SETTING_PAGE_SIZE, 'Page size', array($this, 'page_size'), 'bgcs', 'bgcs_main');
	}

	function admin_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br></div>
				<h2>Google XML Site Search</h2>
				<p>Some optional text here explaining the overall purpose of the options and what they relate to etc.</p>
				<form action="options.php" method="post">
				<?php settings_fields('bgcs_options'); ?>
				<?php do_settings_sections('bgcs'); ?>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
				</p>
				</form>
			</div>
		<?php
	}

	public function main_general_text() {
		echo '';
	}

	public function search_engine_uid() {
		$options = $this->get_options();
		echo "<input id='".BGCS_SETTING_SEARCH_ENGINE_UID."' name='bgcs_options[".BGCS_SETTING_SEARCH_ENGINE_UID."]' size='40' type='text' value='{$options[BGCS_SETTING_SEARCH_ENGINE_UID]}' />";
	}

	public function page_size() {
		$options = $this->get_options();
		echo "<input id='".BGCS_SETTING_PAGE_SIZE."' name='bgcs_options[".BGCS_SETTING_PAGE_SIZE."]' size='5' type='text' value='{$options[BGCS_SETTING_PAGE_SIZE]}' />";
	}

	public function validate_options($input) {
		$search_engine_uid = trim($input[BGCS_SETTING_SEARCH_ENGINE_UID]);
		$page_size = trim($input[BGCS_SETTING_PAGE_SIZE]);

		$new_input = $this->get_option_defaults();

		if (!empty($search_engine_uid)) {
			$new_input[BGCS_SETTING_SEARCH_ENGINE_UID] = $search_engine_uid;
		}
		else {
			add_settings_error(BGCS_SETTING_SEARCH_ENGINE_UID, BGCS_SETTING_SEARCH_ENGINE_UID, 'Search Engine UID should not be empty', 'error');
		}

		if (is_numeric($page_size)) {
			$new_input[BGCS_SETTING_PAGE_SIZE] = $page_size;
		}
		else {
			add_settings_error(BGCS_SETTING_PAGE_SIZE, BGCS_SETTING_PAGE_SIZE, 'Page size should be a number', 'error');
		}

		return $new_input;
	}

	public function get_option_defaults() {
		return array(
			BGCS_SETTING_SEARCH_ENGINE_UID => '',
			BGCS_SETTING_PAGE_SIZE => 10
		);
	}

	public function get_options() {
		return get_option($this->option_name);
	}

	public function get_option($key) {
		$options = $this->get_options();
		return $options[$key];
	}
}

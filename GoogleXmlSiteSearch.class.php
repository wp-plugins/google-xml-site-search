<?php

require_once('GoogleXmlSiteSearchAdmin.class.php');

class GoogleXmlSiteSearch {

	private $results;
	private $current_result = -1;
	private $admin;

	function __construct() {
		$this->admin = new GoogleXmlSiteSearchAdmin();
	}

	public function activate() {
		if (function_exists('is_multisite') && is_multisite()) {
			global $wpdb;
			if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
				$old_blog = $wpdb->blogid;
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					$this->_activate();
				}
				switch_to_blog($old_blog);
				return;
			}
		}
		$this->_activate();
	}

	private function _activate() {
		$this->admin->add_option();
	}

	public function deactivate() {
		if (function_exists('is_multisite') && is_multisite()) {
			global $wpdb;
			if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
				$old_blog = $wpdb->blogid;
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					$this->_deactivate();
				}
				switch_to_blog($old_blog);
				return;
			}
		}
		$this->_deactivate();
	}

	public function _deactivate() {
		$this->admin->delete_option();
	}

	public function bgcs_init() {
		$dirname = dirname( plugin_basename( __FILE__ ) );
		if (strstr($dirname, '\\')) {
			$dirname = substr($dirname, strrpos($dirname, '\\'));
		} else {
			$dirname = substr($dirname, strrpos($dirname, '/'));
		}
		load_plugin_textdomain('bgcs', false, $dirname .'/languages/');
	}

	public function action_init() {
		global $wp;
		$wp->add_query_var('gcs');
		$wp->add_query_var('gcsstart');
	}

	public function action_admin_init() {
		$this->admin->admin_init();
	}

	public function action_admin_menu() {
		$this->admin->admin_menu();
	}

	public function filter_get_search_form($form) {
		return preg_replace('/name="s"/', 'name="gcs"', $form);
	}

	public function filter_request($request) {
		if ($request) {
			$query = new WP_Query();
			$query->parse_query($request);
			if ($query->is_front_page() || $query->is_home()) {
				$search_query = $query->get('gcs');
				if ($search_query) {
					$request['s'] = $search_query; // trigger is_search
				}
			}
		}
		return $request;
	}

	public function filter_search_template($template) {
		$gcs_template = locate_template('google-custom-search.php');
		if ($gcs_template) {
			return $gcs_template;
		}
		if (file_exists(plugin_dir_path(__FILE__).'/default-template.php')) {
			return plugin_dir_path(__FILE__).'/default-template.php';
		}
		return $template;
	}

	public function filter_posts_request($request, $query) {
		if ($query->is_search()) {
			// We know it's search, but is it OUR search?
			$search_query = $query->get('gcs');
			if ($search_query) {
				// Reset the SQL query so that the database is not queried
				return '';
			}
		}
		return $request;
	}

	public function filter_the_posts($posts, $query) {
		if ($query->is_search()) {
			// We know it's search, but is it OUR search?
			$search_query = $query->get('gcs');
			$start = $query->get('gcsstart');
			if (!$start || !is_numeric($start)) {
				$start = 0;
			}
			if ($search_query) {
				$this->search($search_query, $start);
				// Return an empty array to avoid errors
				return array();
			}
		}
		return $posts;
	}

	public function search($query, $start = 0) {
		if (empty($query))
			return;

		if (strlen($query) > 100)
			$query = substr($query, 0, 100);

		$query_vars = array(
			'cx' => $this->admin->get_option(BGCS_SETTING_SEARCH_ENGINE_UID),
			'client' => 'google-csbe',
			'output' => 'xml_no_dtd',
			'q' => $query,
			'start' => $start,
			'num' => $this->admin->get_option(BGCS_SETTING_PAGE_SIZE),
			'oe' => 'utf8',
			'ie' => 'utf8'
		);
		$query_vars = apply_filters('bgcs_query_vars', $query_vars);

		$request_uri = 'http://www.google.com/cse';
		$request_uri = apply_filters('bgcs_request_uri', $request_uri);

		$response = wp_remote_get($request_uri . '?' . http_build_query($query_vars));

		$this->rewind_search_results();

		$this->results = (object)array(
			'success' => false,
			'time' => 0,
			'num_results' => 0,
			'num_results_total' => 0,
			'page_size' => $this->admin->get_option(BGCS_SETTING_PAGE_SIZE),
			'items' => array(),
			'xml' => null,
			'query' => $query
		);

		if (!is_wp_error($response)) {
			if ($response['response']['code'] == 200) {
				$contents = $response['body'];

				$xml = new SimpleXMLElement($contents);
				$xml = apply_filters('bgcs_xml', $xml);

				if (!isset($xml->ERROR)) {
					$this->results->success = true;
					$this->results->time = $xml->TM;
					$this->results->num_results_total = $xml->RES->M;
					$this->results->start = (int)$xml->RES['SN'];
					$this->results->end = (int)$xml->RES['EN'];
					$this->results->xml = $xml;
					$this->results->query = $query;

					if (isset($xml->RES->R)) {
						foreach ($xml->RES->R as $item) {
							$this->results->items[] = (object)array(
								'num' => $item['N'],
								'url' => $item->U,
								'title' => $item->T,
								'desc' => $item->S
							);
						}
					}

					$this->results->num_results = count($this->results->items);
					$this->results = apply_filters('bgcs_results', $this->results);
				}
			}
		}
	}

	public function have_search_results() {
		return isset($this->results) && is_object($this->results) && $this->results->num_results > 0 && $this->current_result < ($this->results->num_results - 1);
	}

	public function get_search_query() {
		return $this->results->query;
	}

	public function the_search_result() {
		$this->current_result++;
	}

	public function rewind_search_results() {
		$this->current_result = -1;
	}

	public function get_search_result_title() {
		return $this->results->items[$this->current_result]->title;
	}

	public function get_search_result_content() {
		return $this->results->items[$this->current_result]->desc[0];
	}

	public function get_search_result_link() {
		return $this->results->items[$this->current_result]->url[0];
	}

	public function get_search_result_count() {
		return $this->results->num_results;
	}

	public function get_search_result_page_count() {
		return ceil($this->results->num_results_total / 10);
	}

	public function get_search_result_current_page() {
		return ceil($this->results->start / 10);
	}

	public function get_search_result_page_size() {
		return 10;
	}

	public function get_page_url($page) {
		$vars = array(
			'gcs' => $this->results->query,
			'gcsstart' => 10 * ($page-1)
		);
		$vars = apply_filters_ref_array('bgcs_page_vars', array(&$vars, $page));
		$url = home_url('/?' . http_build_query($vars));
		$url = apply_filters_ref_array('bgcs_page_url', array(&$url, $page));
		return $url;
	}

	public function transform_search_result($echo = true) {
		$xsl_template = locate_template('google-custom-search.xsl');
		$xsl_template = apply_filters('bgcs_xsl_template', $xsl_template);

		$xsl = new DOMDocument();
		$xsl->load($xsl_template);
		$xsl = apply_filters('bgcs_xsl_dom', $xsl);

		$xml = new DOMDocument();
		$xml->loadXML($this->results->xml->asXML());

		$processor = new XSLTProcessor();
		$processor->importStylesheet($xsl);

		$result = $processor->transformToXml($xml);
		$result = apply_filters('bgcs_transform_result', $result);

		if ($echo) {
			echo $result;
		}

		return $result;
	}

}
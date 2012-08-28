<?php
/*
Plugin Name: Google XML Site Search
Description: Adds the capability to use Google Site Search on your blog. The plugin uses the (non-free) XML API of Google Site Search to provide for maximum customization.
Version: 1.1
Author: Bazooka
Author URI: http://bazooka.se/
*/

require_once('GoogleXmlSiteSearch.class.php');
$bgcs = new GoogleXmlSiteSearch();

register_activation_hook(__FILE__, array($bgcs, 'activate'));
register_deactivation_hook(__FILE__, array($bgcs, 'deactivate'));

if (is_admin()) {
	add_action('admin_init', array($bgcs, 'action_admin_init'));
	add_action('admin_menu', array($bgcs, 'action_admin_menu'));
}
else {
	add_action('plugins_loaded', array($bgcs, 'bgcs_init'));
	add_action('init', array($bgcs, 'action_init'));
	add_filter('request', array($bgcs, 'filter_request'));
	add_filter('search_template', array($bgcs, 'filter_search_template'));
	add_filter('posts_request', array($bgcs, 'filter_posts_request'), 10, 2);
	add_filter('the_posts', array($bgcs, 'filter_the_posts'), 10, 2);
	add_filter('get_search_form', array($bgcs, 'filter_get_search_form'));
}

if (!function_exists('have_search_results')) {
	/**
	 * @since 1.0
	 * @return bool Template function to be used on Google Custom Search template. Return TRUE if there were results for the current query, FALSE otherwise.
	 */
	function have_search_results() {
		global $bgcs;
		return $bgcs->have_search_results();
	}
}

if (!function_exists('the_search_query')) {
	/**
	 * @since 1.0
	 * Template function to be used on Google Custom Search template. Use as you would use WordPress' the_post() function, that is, to set up the next search result to be printed.
	 */
	function the_search_query() {
		global $bgcs;
		echo htmlentities($bgcs->get_search_query());
	}
}

if (!function_exists('the_search_result')) {
	/**
	 * @since 1.0
	 * Template function to be used on Google Custom Search template. Use as you would use WordPress' the_post() function, that is, to set up the next search result to be printed.
	 */
	function the_search_result() {
		global $bgcs;
		echo $bgcs->the_search_result();
	}
}

if (!function_exists('rewind_search_results')) {
	/**
	 * @since 1.0
	 * Template function to be used on Google Custom Search template. Use as you would use WordPress' rewind_posts() function, that is, to reset the loop.
	 */
	function rewind_search_results() {
		global $bgcs;
		echo $bgcs->rewind_search_results();
	}
}

if (!function_exists('the_search_result_title')) {
	/**
	 * @since 1.0
	 * Template function to be used on Google Custom Search template. Prints the title of the current search result.
	 */
	function the_search_result_title() {
		global $bgcs;
		echo $bgcs->get_search_result_title();
	}
}

if (!function_exists('the_search_result_content')) {
	/**
	 * @since 1.0
	 * Template function to be used on Google Custom Search template. Prints a snippet of the current search result.
	 */
	function the_search_result_content() {
		global $bgcs;
		echo $bgcs->get_search_result_content();
	}
}

if (!function_exists('the_search_result_link')) {
	/**
	 * @since 1.0
	 * Template function to be used on Google Custom Search template. Prints the URL to the current search result.
	 */
	function the_search_result_link() {
		global $bgcs;
		echo $bgcs->get_search_result_link();
	}
}

if (!function_exists('get_search_result_count')) {
	/**
	 * @since 1.0
	 * @return mixed Returns the number of search results found.
	 */
	function get_search_result_count() {
		global $bgcs;
		return $bgcs->get_search_result_count();
	}
}

if (!function_exists('get_search_result_page_count')) {
	/**
	 * @since 1.0
	 * @return float Returns the number of pages of search results.
	 */
	function get_search_result_page_count() {
		global $bgcs;
		return $bgcs->get_search_result_page_count();
	}
}

if (!function_exists('get_search_result_current_page')) {
	/**
	 * @since 1.0
	 * @return float Returns the current page (1-indexed).
	 */
	function get_search_result_current_page() {
		global $bgcs;
		return $bgcs->get_search_result_current_page();
	}
}

if (!function_exists('get_search_result_page_size')) {
	/**
	 * @since 1.0
	 * @return int Returns the number of items per search result page.
	 */
	function get_search_result_page_size() {
		global $bgcs;
		return $bgcs->get_search_result_page_size();
	}
}

if (!function_exists('has_search_result_next_page')) {
	/**
	 * @since 1.0
	 * @return bool Returns TRUE if there is another page of search results after the current one.
	 */
	function has_search_result_next_page() {
		return get_search_result_current_page() < get_search_result_page_count();
	}
}

if (!function_exists('get_search_result_page_url')) {
	/**
	 * @since 1.0
	 * @param $page Number The page-number (1-indexed) for which to return the URL
	 * @return mixed|string The URL of the page (1-indexed) requested
	 */
	function get_search_result_page_url($page) {
		global $bgcs;
		return $bgcs->get_page_url($page);
	}
}

if (!function_exists('get_search_result_next_page_url')) {
	/**
	 * @since 1.0
	 * @return mixed|string The URL of the next page of search results. You should check if there _is_ another page of search results with @see has_search_result_next_page()
	 * @see has_search_result_next_page()
	 * @see get_search_result_page_url()
	 */
	function get_search_result_next_page_url() {
		return get_search_result_page_url(get_search_result_current_page()+1);
	}
}

if (!function_exists('has_search_result_prev_page')) {
	/**
	 * @since 1.0
	 * @return bool Returns TRUE if the current page (1-indexed) is larger than 1.
	 */
	function has_search_result_prev_page() {
		return get_search_result_current_page() > 1;
	}
}

if (!function_exists('get_search_result_prev_page_url')) {
	/**
	 * @since 1.0
	 * @return mixed|string The URL of the previous page of search results. You should check if there _is_ a previous page of search results with @see has_search_result_prev_page()
	 * @see has_search_result_prev_page()
	 * @see get_search_result_page_url()
	 * */
	function get_search_result_prev_page_url() {
		return get_search_result_page_url(get_search_result_current_page()-1);
	}
}

if (!function_exists('the_search_result_nav')) {
	/**
	 * @since 1.0
	 * Prints a paged navigation for the search results
	 */
	function the_search_result_nav() {
		$first_page = get_search_result_current_page() - 5;
		if ($first_page < 1) {
			$first_page = 1;
		}
		$last_page = $first_page + 9;
		if ($last_page > get_search_result_page_count()) {
			$last_page = get_search_result_page_count();
		}
		$current_page = get_search_result_current_page();
		?>
		<nav id="bgcs-search-navigation" class="bgcs">
			<ul role="navigation">
				<?php if (has_search_result_prev_page()) : ?>
					<li class="nav-item prev-page"><a href="<?php echo get_search_result_prev_page_url() ?>"><span class="meta-nav">&larr;</span> <?php _e('Previous', 'bgcs'); ?></a></li>
				<?php endif; ?>
				<?php for ($i = $first_page; $i <= $last_page; $i++) :?>
					<li class="nav-item<?php echo ($i == $current_page ? ' current-page' : '') ?>"><a href="<?php echo get_search_result_page_url($i) ?>"><?php echo $i ?></a></li>
				<?php endfor; ?>
				<?php if (has_search_result_next_page()) : ?>
					<li class="nav-item next-page"><a href="<?php echo get_search_result_next_page_url() ?>"><?php _e('Next', 'bgcs'); ?> <span class="meta-nav">&rarr;</span></a></li>
				<?php endif; ?>
			</ul>
		</nav>
		<?php
	}
}

if (!function_exists('transform_search_result')) {
	function transform_search_result($echo = true) {
		global $bgcs;
		$bgcs->transform_search_result($echo);
	}
}
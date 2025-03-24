<?php
/*
Plugin Name: Download All URLs CSV
Description: Outputs all publicly accessible URLs from all post types to a downloadable CSV file.
Version: 1.0
Author: P4UL
Author URI: https://www.p4ul.dev
License: DBAD
*/

	// Exit if accessed directly.
	if(!defined('ABSPATH')):
		exit;
	endif;

	/**
	* Process the download request and output the CSV file.
	*/
	function dau_process_download_request() {
		// Check if the download parameter is set and verify the user capability.
		if(isset($_GET['download_urls']) && $_GET['download_urls'] === 'true'):
			if(!current_user_can( 'manage_options')):
				wp_die('You are not allowed to perform this action.');
			endif;

			// Set headers to trigger a CSV file download.
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=urls.csv');

			// Open the output stream.
			$output = fopen('php://output', 'w');

			// Optionally output a header row.
			fputcsv($output, array('URL', 'POST TYPE'));

			// Retrieve all public post types.
			$public_post_types = get_post_types(array('public' => true), 'names');

			// Query all published posts from the public post types.
			$args  = array(
				'post_type'      => $public_post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			);
			$posts = get_posts($args);

			// Loop through the posts and write each URL to the CSV file.
			foreach($posts as $post_id):
				$url = get_permalink($post_id);
				$post_type = get_post_type($post_id);
				fputcsv($output, array($url, $post_type));
			endforeach;

			fclose($output);
			exit;
		endif;
	}
	add_action('init', 'dau_process_download_request');

	/**
	* Add an admin menu page for downloading the CSV.
	*/
	function dau_add_admin_menu() {
		add_options_page(
			'Download URLs',         // Page title.
			'Download URLs',         // Menu title.
			'manage_options',        // Capability.
			'download-all-urls-csv', // Menu slug.
			'dau_admin_page_callback'// Callback function.
		);
	}
	add_action('admin_menu', 'dau_add_admin_menu');

	/**
	* Render the admin page.
	*/
	function dau_admin_page_callback() {
		?>
		<div class="wrap">
			<h1>Download All URLs CSV</h1>
			<p>Click the button below to download a CSV file containing all publicly accessible URLs from all post types.</p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url(admin_url('?download_urls=true')); ?>">
					Download CSV
				</a>
			</p>
		</div>
		<?php
	}
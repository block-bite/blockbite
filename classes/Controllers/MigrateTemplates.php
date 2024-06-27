<?php

namespace Blockbite\Blockbite\Controllers;


use WP_REST_Response;
use WP_Error;
use WP_Query;

use Blockbite\Blockbite\Controllers\Database as DbController;


class MigrateTemplates extends Controller
{


	public static function migrate($request = null)
	{

		$data = $request->get_params();

		$find = $data['find'];
		$replace = $data['replace'];


		// Create directories in current theme folder
		$theme_dir_template = get_template_directory() . '/migrated-template';
		$theme_dir_template_part = get_template_directory() . '/migrated-template-part';
		$message = null;

		if (!file_exists($theme_dir_template)) {
			wp_mkdir_p($theme_dir_template);
		}
		if (!file_exists($theme_dir_template_part)) {
			wp_mkdir_p($theme_dir_template_part);
		}

		// Query posts with post_type wp_template
		$args_template = array(
			'post_type' => 'wp_template',
			'posts_per_page' => -1,
		);
		$templates = new WP_Query($args_template);

		if ($templates->have_posts()) {
			$posts_by_title = array();

			// Group posts by title
			while ($templates->have_posts()) {
				$templates->the_post();
				$post_title = get_the_title();
				$post_modified = get_the_modified_date('Y-m-d H:i:s');

				// Initialize the group if not set
				if (!isset($posts_by_title[$post_title])) {
					$posts_by_title[$post_title] = array();
				}

				// Add post to the group
				$posts_by_title[$post_title][] = array(
					'content' => get_the_content(),
					'modified' => $post_modified,
					'ID' => get_the_ID(),
				);
			}
			wp_reset_postdata();

			// Iterate over each group of posts
			foreach ($posts_by_title as $title => $posts) {
				// Sort the posts by modified date in descending order
				usort($posts, function ($a, $b) {
					return strtotime($b['modified']) - strtotime($a['modified']);
				});

				// Get the latest post
				$latest_post = $posts[0];

				// Create slugified and lowercase filename
				$filename = sanitize_title($title) . '.html';
				$filepath = $theme_dir_template . '/' . $filename;

				// Optionally perform the find and replace operation
				if (strlen($find) > 0 && strlen($replace) > 0) {
					$latest_post['content'] = str_replace('theme":"' . $find, 'theme":"' . $replace, $latest_post['content']);
				}

				// Write content to HTML file
				file_put_contents($filepath, $latest_post['content']);
			}

			$message = 'Template posts exported successfully.';
		} else {
			$message = 'No template posts found to export.';
		}


		// Query posts with post_type wp_template_part
		$args_template_part = array(
			'post_type' => 'wp_template_part',
			'posts_per_page' => -1,
		);
		$template_parts = new WP_Query($args_template_part);

		if ($template_parts->have_posts()) {
			$parts_by_title = array();

			// Group posts by title
			while ($template_parts->have_posts()) {
				$template_parts->the_post();
				$post_title = get_the_title();
				$post_modified = get_the_modified_date('Y-m-d H:i:s');

				// Initialize the group if not set
				if (!isset($parts_by_title[$post_title])) {
					$parts_by_title[$post_title] = array();
				}

				// Add post to the group
				$parts_by_title[$post_title][] = array(
					'content' => get_the_content(),
					'modified' => $post_modified,
					'ID' => get_the_ID(),
				);
			}
			wp_reset_postdata();

			// Iterate over each group of posts
			foreach ($parts_by_title as $title => $parts) {
				// Sort the posts by modified date in descending order
				usort($parts, function ($a, $b) {
					return strtotime($b['modified']) - strtotime($a['modified']);
				});

				// Get the latest post
				$latest_part = $parts[0];

				// Create slugified and lowercase filename
				$filename = sanitize_title($title) . '.html';
				$filepath = $theme_dir_template_part . '/' . $filename;

				// Optionally perform the find and replace operation
				if (strlen($find) > 0 && strlen($replace) > 0) {
					$latest_part['content'] = str_replace('theme":"' . $find, 'theme":"' . $replace, $latest_part['content']);
				}

				// Write content to HTML file
				file_put_contents($filepath, $latest_part['content']);
			}

			$message .= '<br/>Template part posts exported successfully.';
		} else {
			$message .= '<br/>No template part posts found to export.';
		}

		return new WP_REST_Response([
			'status' => 200,
			'message' => $message,
		], 200);
	}
}

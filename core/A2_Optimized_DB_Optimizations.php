<?php

class A2_Optimized_DBOptimizations {
	public $wpdb;

	public function __construct() {
		if ( ! $this->allow_load() ) {
			return;
		}
		$this->hooks();

		global $wpdb;
		$this->wpdb = $wpdb;
	}

	/**
	 * Indicate if Site Health is allowed to load.
	 *
	 * @return bool
	 */
	private function allow_load() {
		global $wp_version;

		return version_compare( $wp_version, '5.2', '>=' );
	}

	/**
	 * Integration hooks.
	 */
	protected function hooks() {
		$toggles = get_option('a2_db_optimizations');
		if ($toggles && isset($toggles['cron_active']) && $toggles['cron_active']) {
			add_action('a2_execute_db_optimizations', [&$this, 'execute_optimizations']);
			if (!wp_next_scheduled('a2_execute_db_optimizations')) {
				wp_schedule_event(time(), 'weekly', 'a2_execute_db_optimizations');
			}
		}
	}

	public static function validate_db_optimization_settings($settings) {
		$final_settings = self::zero_settings();

		foreach ($settings as $key => $value) {
			$final_settings[$key] = intval($value);
		}

		return $final_settings;
	}

	/**
	 * Get the default values for the optimization toggles
	 */
	private static function get_defaults() {
		$default_toggles = [
			'remove_revision_posts' => 0,
			'remove_trashed_posts' => 0,
			'remove_spam_comments' => 1,
			'remove_trashed_comments' => 1,
			'remove_expired_transients' => 1,
			'optimize_tables' => 1
		];

		return $default_toggles;
	}

	private static function zero_settings() {
		$default_toggles = [
			'remove_revision_posts' => 0,
			'remove_trashed_posts' => 0,
			'remove_spam_comments' => 0,
			'remove_trashed_comments' => 0,
			'remove_expired_transients' => 0,
			'optimize_tables' => 0
		];

		return $default_toggles;
	}

	/**
	 * Update a Wordpress setting in the toggles array.
	 *
	 * @param string $setting  The setting to update in the toggles array
	 * @param bool $value      Update the setting to true or false
	 */
	public static function set($setting, $value) {
		$toggles = get_option('a2_db_optimizations');
		$defaults = self::get_defaults();
		$combined = wp_parse_args($toggles, $defaults);
		$combined[$setting] = $value;
		update_option('a2_db_optimizations', $combined);
	}

	/**
	 * Execute optimizations that have been enabled by the user
	 */
	public function execute_optimizations() {
		$toggles = get_option('a2_db_optimizations');

		if ($toggles['remove_revision_posts']) {
			$this->remove_revisions_posts();
		}
		if ($toggles['remove_trashed_posts']) {
			$this->remove_trashed_posts();
		}
		if ($toggles['remove_spam_comments']) {
			$this->remove_spam_comments();
		}
		if ($toggles['remove_trashed_comments']) {
			$this->remove_trashed_comments();
		}
		if ($toggles['remove_expired_transients']) {
			$this->remove_expired_transients();
		}
		if ($toggles['optimize_tables']) {
			$this->optimize_tables();
		}
	}

	/**
	 * Remove "revisions" posts from the DB
	 */
	public function remove_revisions_posts() {
		$post_table = $this->wpdb->posts;
		$query = 'SELECT ID FROM ' . $post_table . ' WHERE post_type = "revision"';
		$ids = $this->wpdb->get_col($query);
		if (!$ids) {
			return;
		}

		foreach ($ids as $id) {
			wp_delete_post_revision($id);
		}
	}

	/**
	 * Remove trashed posts from the DB
	 */
	public function remove_trashed_posts() {
		$post_table = $this->wpdb->posts;
		$query = 'SELECT ID FROM ' . $post_table . ' WHERE post_status = "trash"';
		$ids = $this->wpdb->get_col($query);
		if (!$ids) {
			return;
		}

		foreach ($ids as $id) {
			wp_delete_post($id, true);
		}
	}

	/**
	 * Remove spam comments from the DB
	 */
	public function remove_spam_comments() {
		$comment_table = $this->wpdb->comments;
		$query = 'SELECT comment_ID FROM ' . $comment_table . ' WHERE comment_approved = "spam"';
		$ids = $this->wpdb->get_col($query);
		if (!$ids) {
			return;
		}

		foreach ($ids as $id) {
			wp_delete_comment($id, true);
		}
	}

	/**
	 * Remove trashed comments from the DB
	 */
	public function remove_trashed_comments() {
		$comment_table = $this->wpdb->comments;
		$query = 'SELECT comment_ID FROM ' . $comment_table . ' WHERE comment_approved = "trash"';
		$ids = $this->wpdb->get_col($query);
		if (!$ids) {
			return;
		}

		foreach ($ids as $id) {
			wp_delete_comment($id, true);
		}
	}

	/**
	 * Delete expired transients from the DB
	 */
	public function remove_expired_transients() {
		$options_table = $this->wpdb->options;
		$query = 'SELECT option_name FROM ' . $options_table . ' WHERE option_name LIKE %s AND option_value < %d';
		$time = $_SERVER['REQUEST_TIME'];
		$transients = $this->wpdb->get_col($this->wpdb->prepare($query, $this->wpdb->esc_like( '_transient_timeout' ) . '%', $time));
		if (!$transients) {
			return;
		}

		foreach ($transients as $transient) {
			delete_transient(str_replace( '_transient_timeout_', '', $transient));
		}
	}

	/**
	 * Optimize Wordpress tables
	 */
	public function optimize_tables() {
		$query = 'SHOW TABLES';
		$tables = $this->wpdb->get_results($query);

		foreach ($tables as $obj) {
			$pair = json_decode(json_encode($obj), true);
			foreach ($pair as $table => $name) {
				$opt_query = 'OPTIMIZE TABLE ' . $name;
				$this->wpdb->query($opt_query);
			}
		}
	}
}

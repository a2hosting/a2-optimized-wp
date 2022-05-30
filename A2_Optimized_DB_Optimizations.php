<?php

//@TODO: Button run optimizations. deactivate hook to remove cron task

class A2_Optimized_DBOptimizations {
	public $wpdb;
	public const WP_SETTING = 'a2_db_optimizations';
	public const REMOVE_REVISION_POSTS = 'remove_revision_posts';
	public const REMOVE_TRASHED_POSTS = 'remove_trashed_posts';
	public const REMOVE_SPAM_COMMENTS = 'remove_spam_comments';
	public const REMOVE_TRASHED_COMMENTS = 'remove_trashed_comments';
	public const REMOVE_EXPIRED_TRANSIENTS = 'remove_expired_transients';
	public const OPTIMIZE_TABLES = 'optimize_tables';
	public const EXECUTE_OPTIMIZATIONS_HOOK = 'a2_execute_db_optimizations';
	public const CRON_ACTIVE = 'cron_active';

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
		$toggles = get_option(self::WP_SETTING);
		if ($toggles && isset($toggles[self::CRON_ACTIVE]) && $toggles[self::CRON_ACTIVE]) {
			add_action(self::EXECUTE_OPTIMIZATIONS_HOOK, [&$this, 'execute_optimizations']);
			if (!wp_next_scheduled(self::EXECUTE_OPTIMIZATIONS_HOOK)) {
				wp_schedule_event(time(), 'weekly', self::EXECUTE_OPTIMIZATIONS_HOOK);
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
			self::REMOVE_REVISION_POSTS => 0,
			self::REMOVE_TRASHED_POSTS => 0,
			self::REMOVE_SPAM_COMMENTS => 1,
			self::REMOVE_TRASHED_COMMENTS => 1,
			self::REMOVE_EXPIRED_TRANSIENTS => 1,
			self::OPTIMIZE_TABLES => 1
		];

		return $default_toggles;
	}

	private static function zero_settings() {
		$default_toggles = [
			self::REMOVE_REVISION_POSTS => 0,
			self::REMOVE_TRASHED_POSTS => 0,
			self::REMOVE_SPAM_COMMENTS => 0,
			self::REMOVE_TRASHED_COMMENTS => 0,
			self::REMOVE_EXPIRED_TRANSIENTS => 0,
			self::OPTIMIZE_TABLES => 0
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
		$toggles = get_option(self::WP_SETTING);
		$defaults = self::get_defaults();
		$combined = wp_parse_args($toggles, $defaults);
		$combined[$setting] = $value;
		update_option(self::WP_SETTING, $combined);
	}

	/**
	 * Execute optimizations that have been enabled by the user
	 */
	public function execute_optimizations() {
		$toggles = get_option(self::WP_SETTING);

		if ($toggles[self::REMOVE_REVISION_POSTS]) {
			$this->remove_revisions_posts();
		}
		if ($toggles[self::REMOVE_TRASHED_POSTS]) {
			$this->remove_trashed_posts();
		}
		if ($toggles[self::REMOVE_SPAM_COMMENTS]) {
			$this->remove_spam_comments();
		}
		if ($toggles[self::REMOVE_TRASHED_COMMENTS]) {
			$this->remove_trashed_comments();
		}
		if ($toggles[self::REMOVE_EXPIRED_TRANSIENTS]) {
			$this->remove_expired_transients();
		}
		if ($toggles[self::OPTIMIZE_TABLES]) {
			$this->remove_optimize_tables();
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

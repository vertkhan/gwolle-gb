<?php

/*
 * Adds a dashboard widget to show the latest entries.
 */


function gwolle_gb_dashboard() {

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		return;
	}

	// Only get new and unchecked entries
	$entries = gwolle_gb_get_entries(array(
			'num_entries' => 5,
			'checked' => 'unchecked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		));

	if ( is_array($entries) && !empty($entries) ) {

		// List of guestbook entries
		echo '<div class="gwolle-gb-dashboard gwolle-gb">';
		$rowOdd = false;
		foreach ( $entries as $entry ) {
			$class = '';
			// rows have a different color.
			if ($rowOdd) {
				$rowOdd = false;
				$class = ' alternate';
			} else {
				$rowOdd = true;
				$class = '';
			}

			// Attach 'spam' to class if the entry is spam
			if ( $entry->get_isspam() === 1 ) {
				$class .= ' spam';
			} else {
				$class .= ' nospam';
			}

			// Attach 'trash' to class if the entry is in trash
			if ( $entry->get_istrash() === 1 ) {
				$class .= ' trash';
			} else {
				$class .= ' notrash';
			}

			// Attach 'checked/unchecked' to class
			if ( $entry->get_ischecked() === 1 ) {
				$class .= ' checked';
			} else {
				$class .= ' unchecked';
			}

			// Attach 'visible/invisible' to class
			if ( $entry->get_isspam() === 1 || $entry->get_istrash() === 1 || $entry->get_ischecked() === 0 ) {
				$class .= ' invisible';
			} else {
				$class .= ' visible';
			}

			// Add admin-entry class to an entry from an admin
			$author_id = $entry->get_author_id();
			$is_moderator = gwolle_gb_is_moderator( $author_id );
			if ( $is_moderator ) {
				$class .= ' admin-entry';
			} ?>


			<div id="entry_<?php echo $entry->get_id(); ?>" class="comment depth-1 comment-item <?php echo $class; ?>">
				<div class="dashboard-comment-wrap">
					<h4 class="comment-meta">
						<?php // Author info ?>
						<cite class="comment-author"><?php echo gwolle_gb_get_author_name_html($entry); ?></cite>
					</h4>

					<?php
					// Optional Icon column where CSS is being used to show them or not
					/*
					if ( get_option('gwolle_gb-showEntryIcons', 'true') === 'true' ) { ?>
						<div class="entry-icons">
							<span class="visible-icon"></span>
							<span class="invisible-icon"></span>
							<span class="spam-icon"></span>
							<span class="trash-icon"></span>
							<span class="gwolle_gb_ajax"></span>
						</div><?php
					} */

					// Date column
					echo '
						<div class="date">' . date_i18n( get_option('date_format'), $entry->get_date() ) . ', ' .
							date_i18n( get_option('time_format'), $entry->get_date() ) .
						'</div>'; ?>

					<blockquote class="excerpt">
						<p>
						<?php
						// Content / Excerpt
						$entry_content = gwolle_gb_get_excerpt( $entry->get_content(), 16 );
						if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
							$entry_content = convert_smilies($entry_content);
						}
						echo $entry_content; ?>
						</p>
					</blockquote><?php

					// Actions, to be made with AJAX
					?>
					<p class="row-actions" id="entry-actions-<?php echo $entry->get_id(); ?>">
						<span class="gwolle_gb_edit">
							<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php&entry_id=<?php echo $entry->get_id(); ?>" title="<?php _e('Edit entry', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Edit', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="gwolle_gb_check">
							&nbsp;|&nbsp;
							<a id="check_<?php echo $entry->get_id(); ?>" href="#" class="vim-a" title="<?php _e('Check entry', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Check', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="gwolle_gb_uncheck">
							&nbsp;|&nbsp;
							<a id="uncheck_<?php echo $entry->get_id(); ?>" href="#" class="vim-u" title="<?php _e('Uncheck entry', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Uncheck', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="gwolle_gb_spam">
							&nbsp;|&nbsp;
							<a id="spam_<?php echo $entry->get_id(); ?>" href="#" class="vim-s vim-destructive" title="<?php _e('Mark entry as spam.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Spam', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="gwolle_gb_unspam">
							&nbsp;|&nbsp;
							<a id="unspam_<?php echo $entry->get_id(); ?>" href="#" class="vim-a" title="<?php _e('Mark entry as not-spam.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Not spam', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="gwolle_gb_trash">
							&nbsp;|&nbsp;
							<a id="trash_<?php echo $entry->get_id(); ?>" href="#" class="vim-d vim-destructive" title="<?php _e('Move entry to trash.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Trash', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="gwolle_gb_untrash">
							&nbsp;|&nbsp;
							<a id="untrash_<?php echo $entry->get_id(); ?>" href="#" class="vim-d" title="<?php _e('Recover entry from trash.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Untrash', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="gwolle_gb_ajax">
							&nbsp;|&nbsp;
							<a id="ajax_<?php echo $entry->get_id(); ?>" href="#" class="ajax vim-d vim-destructive" title="<?php _e('Please wait...', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Wait...', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
					</p>
				</div>
			</div>
			<?php

		} ?>

		</div>
		<p class="textright">
			<a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="button"><?php _e('Refresh', GWOLLE_GB_TEXTDOMAIN); ?></a>
			<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=all" class="button button-primary"><?php _e('View all', GWOLLE_GB_TEXTDOMAIN); ?></a>
			<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked" class="button button-primary"><?php _e('View new', GWOLLE_GB_TEXTDOMAIN); ?></a>
		</p><?php
	} else {
		echo '<p>' . __('No new and unchecked guestbook entries.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
	}
}


// Add the widget
function gwolle_gb_dashboard_setup() {
	wp_add_dashboard_widget('gwolle_gb_dashboard', __('Guestbook (new entries)', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_dashboard');
}
add_action('wp_dashboard_setup', 'gwolle_gb_dashboard_setup');



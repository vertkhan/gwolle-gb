<?php

/* Add the feed. */
function gwolle_gb_rss_init(){
	add_feed('gwolle_gb', 'gwolle_gb_rss');
}
add_action('init', 'gwolle_gb_rss_init');


/* Show the XML Feed */
function gwolle_gb_rss() {

	// Only show the first page of entries.
	$entriesPerPage = (int) get_option('gwolle_gb-entriesPerPage', 20);

	/* Get the entries for the RSS Feed */
	$entries = gwolle_gb_get_entries(
		array(
			'offset'      => 0,
			'num_entries' => $entriesPerPage,
			'checked'     => 'checked',
			'trash'       => 'notrash',
			'spam'        => 'nospam'
		)
	);

	/* Get the time of the last entry, else of the last edited post */
	if ( is_array($entries) && !empty($entries) ) {
		$lastbuild = gmdate( 'D, d M Y H:i:s', $entries[0]->get_date() );
	} else {
		$lastbuild = mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified('GMT'), false);
	}

	/* Uses intermittent meta_key to determine the permalink. See actions.php */
	$the_query = new WP_Query( array(
		'post_type' => 'any',
		'ignore_sticky_posts' => true,
		'meta_query' => array(
			array(
				'key' => 'gwolle_gb_read',
				'value' => 'true',
			),
		)
	));
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$permalink = get_the_permalink();
			break; // only one post is needed.
		endwhile;
		wp_reset_postdata();
	} else {
		$permalink = get_bloginfo('url');
	}

	/* Build the XML content */
	header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
	echo '<?xml version="1.0" encoding="' . get_option('blog_charset') . '"?' . '>';
	?>

	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:wfw="http://wellformedweb.org/CommentAPI/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
		xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
		<?php do_action('rss2_ns'); ?>>

		<channel>
			<title><?php bloginfo_rss('name'); echo " - " . __('Guestbook Feed', GWOLLE_GB_TEXTDOMAIN); ?></title>
			<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
			<link><?php echo $permalink; ?></link>
			<description><?php bloginfo_rss('description'); echo " - " . __('Guestbook Feed', GWOLLE_GB_TEXTDOMAIN); ?></description>
			<lastBuildDate><?php echo $lastbuild; ?></lastBuildDate>
			<language><?php echo get_option('rss_language'); ?></language>
			<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
			<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
			<?php do_action('rss2_head'); ?>

			<?php
			if ( is_array($entries) && !empty($entries) ) {
				foreach ( $entries as $entry ) { ?>
					<item>
						<title><?php _e('Guestbook Entry by', GWOLLE_GB_TEXTDOMAIN); echo " " . trim( $entry->get_author_name() ); ?></title>
						<link><?php echo $permalink; ?></link>
						<pubDate><?php echo gmdate( 'D, d M Y H:i:s', $entry->get_date() ); ?></pubDate>
						<dc:creator><?php echo trim( $entry->get_author_name() ); ?></dc:creator>
						<guid isPermaLink="false"><?php echo $permalink; ?></guid>
						<description><![CDATA[<?php echo wp_trim_words( $entry->get_content(), 12, '...' ) ?>]]></description>
						<content:encoded><![CDATA[<?php echo wp_trim_words( $entry->get_content(), 25, '...' ) ?>]]></content:encoded>
						<?php rss_enclosure(); ?>
						<?php do_action('rss2_item'); ?>
					</item>
					<?php
				}
			} ?>
		</channel>
	</rss>
	<?php
}

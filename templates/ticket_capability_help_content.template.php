<?php
/**
 * This is the template for the ticket capability help content
 */
?>
<p><strong><?php _e('What is this field for?', 'event_espresso' ); ?></strong></p>
<p>
	<?php _e( 'This field is introduced as a feature that is part of the WP User Integration addon.  It enables you to set restrictions on who can purchase the ticket option.  This is an excellent way to create "Member Only" type discounts to people visiting your site.', 'event_espresso' ); ?>
</p>
<p><strong><?php _e('How do I use it?', 'event_espresso'); ?></strong></p>
<p>
	<?php printf( __( 'Creating these type of restrictions utilizes the %1$sRoles and Capabilities feature of WordPress%2$s.  In this field, you indicate the capability that a visitor must have as a part of their user profile when logged in and viewing the ticket options.  For instance if you have "manage_options" in this field, then the visitor must be logged in and have the "manage_options" capability assigned to their user (or to the role that is assigned to their user).', 'event_espresso' ), '<a href="http://codex.wordpress.org/Roles_and_Capabilities" title="' . __('Click here to read about roles and capabilities in WordPress.', 'event_espresso') . '">', '</a>' ); ?>
</p>


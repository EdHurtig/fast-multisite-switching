<?php
/**
 * Plugin Name: Fast Site Switching
 * Plugin URI: http://hurtigtechnologies.com/plugins/fast-multisite-switching
 * Description: Allows you to quickly switch between all sites in your multisite network
 * Version: 1.0
 * Author: Eddie Hurtig
 * Author URI: http://hurtigtechnologies.com
 * License: GPL2
 */

/**
 * Class MultiSiteFastSwitcher
 *
 * Add a Search field to the My Sites Page to allow you to quickly filter site's in your multisite network and then
 * switch to them.  Adds all the sites to the My Sites Page
 *
 * @author Eddie Hurtig <hurtige@ccs.neu.edu>
 */
class MultiSiteFastSwitcher {

	/**
	 * Constructor (only registers filters and actions right now)
	 */
	function __construct() {
		add_filter( 'get_blogs_of_user', array( &$this, 'show_all_sites_for_super_admins' ) );
		add_action( 'myblogs_allblogs_options', array( &$this, 'add_search_field' ) );
		add_filter( 'myblogs_blog_actions', array( &$this, 'add_site_slug' ), 10, 2 );
	}

	/**
	 * This function forces all blogs to be shown on the My Sites Page for Super Admins.  This makes site switching easier
	 *
	 * @param array $blogs An array of blog objects belonging to the user.
	 *
	 * @return array A list of the user's blogs. An empty array if the user doesn't exist
	 *               or belongs to no blogs.
	 */
	function show_all_sites_for_super_admins( $blogs ) {
		if ( function_exists( 'get_current_screen' ) ) {
			if ( is_object( get_current_screen() ) && get_current_screen()->base == 'my-sites' && is_super_admin() ) {
				// If the lock is set then lets stop ourselves from an infinite loop
				if ( isset( $GLOBALS['my_sites_for_super_admins_lock'] ) ) {
					// Release the lock so that it works next time
					unset( $GLOBALS['my_sites_for_super_admins_lock'] );

					return $blogs;
				} else {
					// This prevents an infinite loop because we are calling get_blogs_of_user() within it's own filter
					$GLOBALS['my_sites_for_super_admins_lock'] = 1;

					return get_blogs_of_user( get_user_by( 'login', 'localbackupadmin' )->ID );
				}
			}
		}

		return $blogs;
	}

	/**
	 * Adds (echos) a field to the My Site's Page that enables Users to search for a site in realtime and then hit enter to go to
	 * the dashboard of that site
	 */
	function add_search_field() {
		?>

		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Quick Select' ); ?></th>
				<td>
					<input type="text" id="fast-multisite-switching-search" placeholder="Start Typing To Narrow Results, Hit Enter to Go" style="width:375px;padding-top:6px;" />
				</td>
			</tr>
		</table>

	<?php
	}

	/**
	 * Adds the Blog's slug to the My-Sites Page to make searching for a site easier and to provide more useful information
	 *
	 * @param $row       The Row of the My Site's page that we are working with
	 * @param $user_blog The Blog that we are adding the slug for
	 *
	 * @return string The new Text for the My Site's Tile representing $user_blog
	 */
	function add_site_slug( $row, $user_blog ) {
		return '<i>' . str_replace( '/', '', $user_blog->path ) . '</i><br>' . $row;
	}

}

if ( is_multisite() ) {
	// This is a temporary override to prevent this plugin from running on gigantic networks until I add some better
	// functionality
	if ( ! is_large_network() || get_site_option( 'hurtigtech_fms_large_network_override', false ) ) {
		$hurtigtech_multisite_fast_switcher = new MultiSiteFastSwitcher();
	}
}
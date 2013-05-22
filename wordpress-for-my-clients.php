<?php
/*
 * Plugin Name: WordPress for my Clients
 * Plugin URI: http://www.dreamsonline.net/wordpress-plugins/wordpress-for-my-clients/
 * Description: Helps customize WordPress for your clients by hiding non essential wp-admin components and by adding support for custom login logo and favicon for website and admin pages.
 * Version: 2.0
 * Author: Dreams Online Themes
 * Author URI: http://www.dreamsonline.net/wordpress-themes/
 * Author Email: hello@dreamsmedia.in
 *
 * @package WordPress
 * @subpackage DOT_WPFMC
 * @author Harish
 * @since 2.0
 *
 * License:

  Copyright 2013 "WordPress for my Clients WordPress Plugin" (hello@dreamsmedia.in)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( ! class_exists( 'DOT_WPFMC' ) ) {


	class DOT_WPFMC {

		/*--------------------------------------------*
		 * Constructor
		 *--------------------------------------------*/

		/**
		 * Initializes the plugin by setting localization, filters, and administration functions.
		 */
		function __construct() {

			// Load text domain
			add_action( 'init', array( $this, 'load_localisation' ), 0 );

			// Adding Plugin Menu
			add_action( 'admin_menu', array( &$this, 'dot_wpfmc_menu' ) );

			 // Load our custom assets.
        	add_action( 'admin_enqueue_scripts', array( &$this, 'dot_wpfmc_assets' ) );

			// Register Settings
			add_action( 'admin_init', array( &$this, 'dot_wpfmc_settings' ) );

			// Hook onto the action 'admin_menu' for our function to remove menu items
			add_action( 'admin_menu', array( &$this, 'dot_wpfmc_remove_menus' ) );

			// Hook onto the action 'admin_menu' for our function to remove dashboard widgets
			add_action( 'admin_menu', array( &$this, 'dot_wpfmc_remove_dashboard_widgets' ) );

			// Change Login header URL
			add_filter( 'login_headerurl', array( &$this, 'dot_wpfmc_login_headerurl' ) );

			// Change Login header Title
			add_filter( 'login_headertitle', array( &$this, 'dot_wpfmc_login_headertitle' ) );

			// Change the default Login page Logo
			add_action('login_head', array( &$this, 'dot_wpfmc_login_logo' ) );

			// Add Favicon to website frontend
			add_action('wp_head', array( &$this, 'dot_wpfmc_favicon_frontend' ) );

			// Add Favicon to website backend
			add_action('admin_head', array( &$this, 'dot_wpfmc_favicon_backend' ) );
			add_action('login_head', array( &$this, 'dot_wpfmc_favicon_backend' ) );

		} // end constructor

		/*--------------------------------------------*
		 * Localisation | Public | 1.0 | Return : void
		 *--------------------------------------------*/

		public function load_localisation ()
		{
			load_plugin_textdomain( 'dot_wpfmc', false, basename( dirname( __FILE__ ) ) . '/languages' );

		} // End load_localisation()

		/**
		 * Defines constants for the plugin.
		 */
		function constants() {
			define( 'DOT_WPFMC_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		}

		/*--------------------------------------------*
		 * Admin Menu
		 *--------------------------------------------*/

		function dot_wpfmc_menu()
		{
			$page_title = __('WordPress for my Clients', 'dot_wpfmc');
			$menu_title = __('WP for my Clients', 'dot_wpfmc');
			$capability = 'manage_options';
			$menu_slug = 'dot_wpfmc';
			$function =  array( &$this, 'dot_wpfmc_menu_contents');
			add_options_page($page_title, $menu_title, $capability, $menu_slug, $function);

		}	//dot_wpfmc_menu

		/*--------------------------------------------*
		 * Load Necessary JavaScript Files
		 *--------------------------------------------*/

		function dot_wpfmc_assets() {
		    if (isset($_GET['page']) && $_GET['page'] == 'dot_wpfmc') {

    			wp_enqueue_style( 'thickbox' ); // Stylesheet used by Thickbox
   				wp_enqueue_script( 'thickbox' );
    			wp_enqueue_script( 'media-upload' );

		        wp_register_script('dot_wpfmc_admin', WP_PLUGIN_URL.'/wp-for-my-clients/js/dot_wpfmc_admin.js', array( 'thickbox', 'media-upload' ));
		        wp_enqueue_script('dot_wpfmc_admin');
		    }
		} //dot_wpfmc_assets

		/*--------------------------------------------*
		 * Settings & Settings Page
		 *--------------------------------------------*/

		public function dot_wpfmc_settings() {

			// Settings
			register_setting( 'dot_wpfmc_settings', 'dot_wpfmc_settings', array(&$this, 'settings_validate') );

			// General Settings
			add_settings_section( 'general', __( 'General Settings', 'dot_wpfmc' ), array( &$this, 'section_general' ), 'dot_wpfmc_settings' );

			add_settings_field( 'remove_menus', __( 'Remove Admin Menus', 'dot_wpfmc' ), array( &$this, 'section_remove_menus' ), 'dot_wpfmc_settings', 'general' );

			add_settings_field( 'show_widgets', __( 'Show Dashboard Widgets', 'dot_wpfmc' ), array( &$this, 'section_show_dashboard_widgets' ), 'dot_wpfmc_settings', 'general' );

			// Logo Settings
			add_settings_section( 'login_logo', __( 'Login Logo Settings', 'dot_wpfmc' ), array( &$this, 'section_login_logo' ), 'dot_wpfmc_settings' );

			add_settings_field( 'login_logo_url', __( 'Upload Login Logo', 'dot_wpfmc' ), array( &$this, 'section_login_logo_url' ), 'dot_wpfmc_settings', 'login_logo' );

			add_settings_field( 'login_logo_height', __( 'Set Logo Height', 'dot_wpfmc' ), array( &$this, 'section_login_logo_height' ), 'dot_wpfmc_settings', 'login_logo' );

			// Custom Favicon
			add_settings_section( 'favicon', __( 'Custom Favicon & Apple touch icon', 'dot_wpfmc' ), array( &$this, 'section_favicon' ), 'dot_wpfmc_settings' );

			add_settings_field( 'favicon_frontend_url', __( 'Favicon for Website', 'dot_wpfmc' ), array( &$this, 'section_favicon_frontend_url' ), 'dot_wpfmc_settings', 'favicon' );

			add_settings_field( 'favicon_backend_url', __( 'Favicon for Admin', 'dot_wpfmc' ), array( &$this, 'section_favicon_backend_url' ), 'dot_wpfmc_settings', 'favicon' );

			add_settings_field( 'apple_icon_frontend_url', __( 'Apple Touch Icon for Website', 'dot_wpfmc' ), array( &$this, 'section_apple_icon_frontend_url' ), 'dot_wpfmc_settings', 'favicon' );

			add_settings_field( 'apple_icon_backend_url', __( 'Apple Touch Icon for Admin', 'dot_wpfmc' ), array( &$this, 'section_apple_icon_backend_url' ), 'dot_wpfmc_settings', 'favicon' );

			add_settings_field( 'apple_icon_style', __( 'Basic Apple Touch Icon', 'dot_wpfmc' ), array( &$this, 'section_apple_icon_style' ), 'dot_wpfmc_settings', 'favicon' );


		}	//dot_wpfmc_settings


		/*--------------------------------------------*
		 * Settings & Settings Page
		 * dot_wpfmc_menu_contents
		 *--------------------------------------------*/

		public function dot_wpfmc_menu_contents() {
		?>
			<div class="wrap">
				<!--<div id="icon-freshdesk-32" class="icon32"><br></div>-->
				<div id="icon-options-general" class="icon32"><br></div>
				<h2><?php _e('WordPress for My Clients Settings', 'dot_wpfmc'); ?></h2>

				<form method="post" action="options.php">
					<?php //wp_nonce_field('update-options'); ?>
					<?php settings_fields('dot_wpfmc_settings'); ?>
					<?php do_settings_sections('dot_wpfmc_settings'); ?>
					<p class="submit">
						<input name="Submit" type="submit" class="button-primary" value="<?php _e('Save Changes', 'dot_wpfmc'); ?>" />
					</p>
				</form>
			</div>

		<?php
		}	//dot_wpfmc_menu_contents

		function section_general() 	{

			//_e( 'Choose which Admin menu to hide', 'dot_wpfmc' );
		}

		function section_remove_menus() {

			$options = get_option( 'dot_wpfmc_settings' );
			if( !isset($options['hide_post']) ) $options['hide_post'] = '0';
			if( !isset($options['hide_tools']) ) $options['hide_tools'] = '0';
			if( !isset($options['hide_comments']) ) $options['hide_comments'] = '0';
			if( !isset($options['hide_media']) ) $options['hide_media'] = '0';

			echo '<input type="hidden" name="dot_wpfmc_settings[hide_post]" value="0" />
			<label><input type="checkbox" name="dot_wpfmc_settings[hide_post]" value="1"'. (($options['hide_post']) ? ' checked="checked"' : '') .' />
			 Remove Posts from Admin Menu</label><br />';

			echo '<input type="hidden" name="dot_wpfmc_settings[hide_tools]" value="0" />
			<label><input type="checkbox" name="dot_wpfmc_settings[hide_tools]" value="1"'. (($options['hide_tools']) ? ' checked="checked"' : '') .' />
			 Remove Tools from Admin Menu</label><br />';

			echo '<input type="hidden" name="dot_wpfmc_settings[hide_comments]" value="0" />
			<label><input type="checkbox" name="dot_wpfmc_settings[hide_comments]" value="1"'. (($options['hide_comments']) ? ' checked="checked"' : '') .' />
			 Remove Comments from Admin Menu</label><br />';

			echo '<input type="hidden" name="dot_wpfmc_settings[hide_media]" value="0" />
			<label><input type="checkbox" name="dot_wpfmc_settings[hide_media]" value="1"'. (($options['hide_media']) ? ' checked="checked"' : '') .' />
			 Remove Media from Admin Menu</label>';

		}

		function section_show_dashboard_widgets() {

			$options = get_option( 'dot_wpfmc_settings' );
			if( !isset($options['show_quick_press']) ) $options['show_quick_press'] = '0';
			if( !isset($options['show_recent_drafts']) ) $options['show_recent_drafts'] = '0';

			echo '<input type="hidden" name="dot_wpfmc_settings[show_quick_press]" value="0" />
			<label><input type="checkbox" name="dot_wpfmc_settings[show_quick_press]" value="1"'. (($options['show_quick_press']) ? ' checked="checked"' : '') .' />
			 Show Quick Press Dashboard Widget</label><br />';

			echo '<input type="hidden" name="dot_wpfmc_settings[show_recent_drafts]" value="0" />
			<label><input type="checkbox" name="dot_wpfmc_settings[show_recent_drafts]" value="1"'. (($options['show_recent_drafts']) ? ' checked="checked"' : '') .' />
			 Show Recent Drafts Dashboard Widget</label>';

		}

		function section_login_logo() 	{


		}

		function section_login_logo_url() 	{
		    $options = get_option( 'dot_wpfmc_settings' );
		    ?>
		    <span class='upload'>
		        <input type='text' id='dot_wpfmc_settings[login_logo_url]' class='regular-text text-upload' name='dot_wpfmc_settings[login_logo_url]' value='<?php echo esc_url( $options["login_logo_url"] ); ?>'/>
		        <input type='button' class='button button-upload' value='Upload an image'/></br>
		        <img style='max-width: 300px; display: block;' src='<?php echo esc_url( $options["login_logo_url"] ); ?>' class='preview-upload' />
		    </span>
		    <?php
		}

		function section_login_logo_height() 	{
		    $options = get_option( 'dot_wpfmc_settings' );

		    ?>
		        <input type='text' id='dot_wpfmc_settings[login_logo_height]' class='text' name='dot_wpfmc_settings[login_logo_height]' value='<?php echo $options["login_logo_height"]; ?>'/> px
		    <?php
		}


		function section_favicon() 	{


		}

		function section_favicon_frontend_url() {
		    $options = get_option( 'dot_wpfmc_settings' );
		    ?>
		    <span class='upload'>
		        <input type='text' id='dot_wpfmc_settings[favicon_frontend_url]' class='regular-text text-upload' name='dot_wpfmc_settings[favicon_frontend_url]' value='<?php echo esc_url( $options["favicon_frontend_url"] ); ?>'/>
		        <input type='button' class='button button-upload' value='Upload an image'/></br>
		        <img style='max-width: 300px; display: block;' src='<?php echo esc_url( $options["favicon_frontend_url"] ); ?>' class='preview-upload' />
		    </span>
		    <?php
		}

		function section_favicon_backend_url() {
		    $options = get_option( 'dot_wpfmc_settings' );
		    ?>
		    <span class='upload'>
		        <input type='text' id='dot_wpfmc_settings[favicon_backend_url]' class='regular-text text-upload' name='dot_wpfmc_settings[favicon_backend_url]' value='<?php echo esc_url( $options["favicon_backend_url"] ); ?>'/>
		        <input type='button' class='button button-upload' value='Upload an image'/></br>
		        <img style='max-width: 300px; display: block;' src='<?php echo esc_url( $options["favicon_backend_url"] ); ?>' class='preview-upload' />
		    </span>
		    <?php
		}

		function section_apple_icon_frontend_url() {
		    $options = get_option( 'dot_wpfmc_settings' );
		    ?>
		    <span class='upload'>
		        <input type='text' id='dot_wpfmc_settings[apple_icon_frontend_url]' class='regular-text text-upload' name='dot_wpfmc_settings[apple_icon_frontend_url]' value='<?php echo esc_url( $options["apple_icon_frontend_url"] ); ?>'/>
		        <input type='button' class='button button-upload' value='Upload an image'/></br>
		        <img style='max-width: 300px; display: block;' src='<?php echo esc_url( $options["apple_icon_frontend_url"] ); ?>' class='preview-upload' />
		    </span>
		    <?php
		}

		function section_apple_icon_backend_url() {
		    $options = get_option( 'dot_wpfmc_settings' );
		    ?>
		    <span class='upload'>
		        <input type='text' id='dot_wpfmc_settings[apple_icon_backend_url]' class='regular-text text-upload' name='dot_wpfmc_settings[apple_icon_backend_url]' value='<?php echo esc_url( $options["apple_icon_backend_url"] ); ?>'/>
		        <input type='button' class='button button-upload' value='Upload an image'/></br>
		        <img style='max-width: 300px; display: block;' src='<?php echo esc_url( $options["apple_icon_backend_url"] ); ?>' class='preview-upload' />
		    </span>
		    <?php
		}

		function section_apple_icon_style() {

			$options = get_option( 'dot_wpfmc_settings' );
			if( !isset($options['apple_icon_style']) ) $options['apple_icon_style'] = '0';

			echo '<input type="hidden" name="dot_wpfmc_settings[apple_icon_style]" value="0" />
			<label><input type="checkbox" name="dot_wpfmc_settings[apple_icon_style]" value="1"'. (($options['apple_icon_style']) ? ' checked="checked"' : '') .' />
			 Disable Curved Border & reflective shine for Apple touch icon</label><br />';
		}

		/*--------------------------------------------*
		 * Settings Validation
		 *--------------------------------------------*/

		function settings_validate($input) {

			return $input;
		}


		// Add Favicon to website frontend
		function dot_wpfmc_favicon_frontend() {
			$options =  get_option('dot_wpfmc_settings');

			if( $options['favicon_frontend_url'] != "" ) {
		        echo '<link rel="shortcut icon" href="'.  esc_url( $options["favicon_frontend_url"] )  .'"/>'."\n";
		    }

		    if( $options['apple_icon_frontend_url'] != "" ) {

		    	if ( $options['apple_icon_style'] == '0') {

		        	echo '<link rel="apple-touch-icon" href="'.  esc_url( $options["apple_icon_frontend_url"] )  .'"/>'."\n";

		    	}
		    	else {

		    		echo '<link rel="apple-touch-icon-precomposed" href="'.  esc_url( $options["apple_icon_frontend_url"] )  .'"/>'."\n";

		    	}
		    }
		}


		// Add Favicon to website backend
		function dot_wpfmc_favicon_backend() {
			$options =  get_option('dot_wpfmc_settings');

			if( $options['favicon_backend_url'] != "" ) {
		        echo '<link rel="shortcut icon" href="'.  esc_url( $options["favicon_backend_url"] )  .'"/>'."\n";
		    }

		    if( $options['apple_icon_backend_url'] != "" ) {

		    	if ( $options['apple_icon_style'] == '0') {

		        	echo '<link rel="apple-touch-icon" href="'.  esc_url( $options["apple_icon_backend_url"] )  .'"/>'."\n";

		    	}
		    	else {

		    		echo '<link rel="apple-touch-icon-precomposed" href="'.  esc_url( $options["apple_icon_backend_url"] )  .'"/>'."\n";

		    	}
		    }
		}


		function dot_wpfmc_login_logo() {

			$options = get_option( 'dot_wpfmc_settings' );
			//if( !isset($options['login_logo_url']) ) $options['login_logo_url'] = '0';
			//if( !isset($options['login_logo_url_height']) ) $options['login_logo_url_height'] = 'auto';

			if( $options['login_logo_url'] != "" ) {
				echo '<style type="text/css">
	        	h1 a { background-image:url('.esc_url( $options["login_logo_url"] ).') !important; 	height:'.sanitize_text_field( $options["login_logo_height"] ).'px !important; background-size: auto auto !important; }
	        		</style>';
	    	}
		}


		/*--------------------------------------------*
		 * Remove Admin Menus
		 *--------------------------------------------*/

		function dot_wpfmc_remove_menus() {

			$options = get_option('dot_wpfmc_settings');

			// Links page
			remove_menu_page( 'link-manager.php' );


			// Posts Menu
			if ( $options['hide_post'] == '1') {
			    remove_menu_page('edit.php');
			}

			// Tools Menu
			if ( $options['hide_tools'] == '1') {
			    remove_menu_page('tools.php');
			}

			// Comments Menu
			if ( $options['hide_comments'] == '1') {
			    remove_menu_page('edit-comments.php');
			}

			// Media Menu
			if ( $options['hide_media'] == '1') {
			    remove_menu_page('upload.php');
			}

		}

		/*--------------------------------------------*
		 * Remove Dashboard Widgets
		 *--------------------------------------------*/

		function dot_wpfmc_remove_dashboard_widgets() {

			remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'core' );
			remove_meta_box( 'dashboard_plugins', 'dashboard', 'core' );
			remove_meta_box( 'dashboard_primary', 'dashboard', 'core' );
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'core' );

			$options = get_option('dot_wpfmc_settings');

			// Quick Press Widget
			if ( $options['show_quick_press'] == '0') {
			    remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
			}

			// Recent Drafts Widget
			if ( $options['show_recent_drafts'] == '0') {
			    remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
			}

		}

		function dot_wpfmc_login_headertitle( $title ) {
			return get_bloginfo( 'name' );
		}

		function dot_wpfmc_login_headerurl( $url ) {
			return home_url();
		}


	} // End Class


	// Initiation call of plugin
	$dot_wpfmc = new DOT_WPFMC(__FILE__);

}



?>
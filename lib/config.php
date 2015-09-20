<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2015 - Jean-Sebastien Morisset - http://wpsso.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbConfig' ) ) {

	class WpssoRrssbConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssorrssb' => array(
					'version' => '1.0.6',	// plugin version
					'short' => 'WPSSO RRSSB',
					'name' => 'WPSSO Ridiculously Responsive Social Sharing Buttons (WPSSO RRSSB)',
					'desc' => 'WPSSO extension to add Ridiculously Responsive (SVG) Social Sharing Buttons in your content, excerpts, CSS sidebar, widget, shortcode, etc.',
					'slug' => 'wpsso-rrssb',
					'base' => 'wpsso-rrssb/wpsso-rrssb.php',
					'update_auth' => 'tid',
					'img' => array(
						'icon_small' => 'images/icon-128x128.png',
						'icon_medium' => 'images/icon-256x256.png',
					),
					'url' => array(
						// wordpress.org
						'download' => 'https://wordpress.org/plugins/wpsso-rrssb/',
						'review' => 'https://wordpress.org/support/view/plugin-reviews/wpsso-rrssb?filter=5&rate=5#postform',
						'readme' => 'https://plugins.svn.wordpress.org/wpsso-rrssb/trunk/readme.txt',
						'wp_support' => 'https://wordpress.org/support/plugin/wpsso-rrssb',
						// wpsso.com
						'update' => 'http://wpsso.com/extend/plugins/wpsso-rrssb/update/',
						'purchase' => 'http://wpsso.com/extend/plugins/wpsso-rrssb/',
						'changelog' => 'http://wpsso.com/extend/plugins/wpsso-rrssb/changelog/',
						'codex' => 'http://wpsso.com/codex/plugins/wpsso-rrssb/',
						'faq' => 'http://wpsso.com/codex/plugins/wpsso-rrssb/faq/',
						'notes' => '',
						'feed' => 'http://wpsso.com/category/application/wordpress/wp-plugins/wpsso-rrssb/feed/',
						'pro_support' => 'http://wpsso-rrssb.support.wpsso.com/',
					),
					'lib' => array(
						'submenu' => array (
							'wpssorrssb-separator-0' => 'RRSSB Extension',
							'sharing-buttons' => 'Sharing Buttons',
							'sharing-styles' => 'Sharing Styles',
						),
						'website' => array(
							'email' => 'Email', 
							'facebook' => 'Facebook', 
							'gplus' => 'GooglePlus', 
							'twitter' => 'Twitter', 
							'pinterest' => 'Pinterest', 
							'linkedin' => 'LinkedIn', 
							'reddit' => 'Reddit', 
							'pocket' => 'Pocket', 
							'tumblr' => 'Tumblr', 
							'vk' => 'VK', 
						),
						'shortcode' => array(
							'sharing' => 'Sharing Shortcode',
						),
						'widget' => array(
							'sharing' => 'Sharing Widget',
						),
						'gpl' => array(
							'admin' => array(
								'sharing' => 'Sharing Admin',
							),
							'ecom' => array(
								'woocommerce' => 'WooCommerce',
							),
							'forum' => array(
								'bbpress' => 'bbPress',
							),
							'social' => array(
								'buddypress' => 'BuddyPress',
							),
						),
						'pro' => array(
							'admin' => array(
								'sharing' => 'Sharing Admin',
							),
							'ecom' => array(
								'woocommerce' => 'WooCommerce',
							),
							'forum' => array(
								'bbpress' => 'bbPress',
							),
							'social' => array(
								'buddypress' => 'BuddyPress',
							),
						),
					),
				),
			),
		);

		public static function set_constants( $plugin_filepath ) { 
			define( 'WPSSORRSSB_FILEPATH', $plugin_filepath );						
			define( 'WPSSORRSSB_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_filepath ) ) ) );
			define( 'WPSSORRSSB_PLUGINBASE', self::$cf['plugin']['wpssorrssb']['base'] );	// wpsso-rrssb/wpsso-rrssb.php
			define( 'WPSSORRSSB_TEXTDOM', self::$cf['plugin']['wpssorrssb']['slug'] );	// wpsso-rrssb
			define( 'WPSSORRSSB_URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );
			self::set_variable_constants();
		}

		public static function set_variable_constants() { 
			foreach ( self::get_variable_constants() as $name => $value )
				if ( ! defined( $name ) )
					define( $name, $value );
		}

		public static function get_variable_constants() { 
			$var_const = array();

			$var_const['WPSSORRSSB_SHARING_SHORTCODE'] = 'rrssb';

			/*
			 * WPSSO RRSSB hook priorities
			 */
			$var_const['WPSSORRSSB_SOCIAL_PRIORITY'] = 100;
			$var_const['WPSSORRSSB_FOOTER_PRIORITY'] = 100;

			foreach ( $var_const as $name => $value )
				if ( defined( $name ) )
					$var_const[$name] = constant( $name );	// inherit existing values

			return $var_const;
		}

		public static function require_libs( $plugin_filepath ) {
			require_once( WPSSORRSSB_PLUGINDIR.'lib/register.php' );
			require_once( WPSSORRSSB_PLUGINDIR.'lib/functions.php' );
			add_filter( 'wpssorrssb_load_lib', array( 'WpssoRrssbConfig', 'load_lib' ), 10, 3 );
		}

		// gpl / pro library loader
		public static function load_lib( $ret = false, $filespec = '', $classname = '' ) {
			if ( $ret === false && ! empty( $filespec ) ) {
				$filepath = WPSSORRSSB_PLUGINDIR.'lib/'.$filespec.'.php';
				if ( file_exists( $filepath ) ) {
					require_once( $filepath );
					if ( empty( $classname ) )
						return 'wpssorrssb'.str_replace( array( '/', '-' ), '', $filespec );
					else return $classname;
				}
			}
			return $ret;
		}
	}
}

?>

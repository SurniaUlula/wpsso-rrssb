<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbStyle' ) ) {

	class WpssoRrssbStyle {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
			}
		}

		public function enqueue_styles() {

			/**
			 * Do not use minified CSS if the DEV constant is defined.
			 */
			$doing_dev      = SucomUtil::get_const( 'WPSSO_DEV' );
			$css_file_ext   = $doing_dev ? 'css' : 'min.css';
			$is_amp         = SucomUtil::is_amp();
			$plugin_version = $this->p->cf[ 'plugin' ][ 'wpssorrssb' ][ 'version' ];

			if ( $is_amp ) {	// No buttons for AMP pages.
				return;
			}

			wp_register_style( 'rrssb', WPSSORRSSB_URLPATH . 'css/ext/rrssb.' . $css_file_ext, array(), $plugin_version );

			wp_enqueue_style( 'rrssb' );

			if ( ! empty( $this->p->options[ 'buttons_use_social_style' ] ) ) {

				if ( ! file_exists( WpssoRrssbSocial::$sharing_css_file ) ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'updating ' . WpssoRrssbSocial::$sharing_css_file );
					}

					WpssoRrssbSocial::update_sharing_css( $this->p->options );
				}

				if ( ! empty( $this->p->options[ 'buttons_enqueue_social_style' ] ) ) {

					$sharing_css_mtime = filemtime( WpssoRrssbSocial::$sharing_css_file );

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'wp_enqueue_style = ' . $this->p->lca . '_rrssb_sharing_css' );
					}

					wp_enqueue_style( $this->p->lca . '_rrssb_sharing_css', WpssoRrssbSocial::$sharing_css_url, false, $sharing_css_mtime );

				} else {

					if ( ! is_readable( WpssoRrssbSocial::$sharing_css_file ) ) {

						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( WpssoRrssbSocial::$sharing_css_file . ' is not readable' );
						}

						if ( is_admin() ) {
							$this->p->notice->err( sprintf( __( 'The %s file is not readable.',
								'wpsso-rrssb' ), WpssoRrssbSocial::$sharing_css_file ) );
						}

					} elseif ( ( $fsize = @filesize( WpssoRrssbSocial::$sharing_css_file ) ) > 0 &&
						$fh = @fopen( WpssoRrssbSocial::$sharing_css_file, 'rb' ) ) {

						echo '<style type="text/css">';
						echo fread( $fh, $fsize );
						echo '</style>',"\n";

						fclose( $fh );
					}
				}

			} elseif ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'buttons_use_social_style option is disabled' );
			}
		}
	}
}

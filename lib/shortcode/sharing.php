<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbShortcodeSharing' ) ) {

	class WpssoRrssbShortcodeSharing {

		private $p;	// Wpsso class object.

		private $shortcode_name = 'rrssb';	// Default shortcode name.

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			$this->shortcode_name = WPSSORRSSB_SHARING_SHORTCODE_NAME;

			if ( $this->p->avail[ 'p_ext' ][ 'rrssb' ] ) {

				$this->check_wpautop();

				$this->add_shortcode();

				$this->p->util->add_plugin_actions( $this, array( 
					'pre_apply_filters_text'   => 1,
				) );
			}
		}

		/**
		 * Make sure wpautop() does not have a higher priority than 10, otherwise it will format the shortcode output
		 * (shortcode filters are run at priority 11).
		 */
		public function check_wpautop() {

			$default_priority = 10;

			foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {

				$filter_priority = has_filter( $filter_name, 'wpautop' );	// Can return a priority of 0.

				if ( false !== $filter_priority && $filter_priority > $default_priority ) {

					remove_filter( $filter_name, 'wpautop' );

					add_filter( $filter_name, 'wpautop' , $default_priority );

					if ( $this->p->debug->enabled ) {

						$this->p->debug->log( 'wpautop() priority changed from ' . $filter_priority . ' to ' . $default_priority );
					}
				}
			}
		}

		/**
		 * Remove our shortcode before applying a text filter.
		 */
		public function action_pre_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			/**
			 * If a shortcode is removed, then re-add it when the text filter is finished executing.
			 */
			if ( $this->remove_shortcode() ) {

				$this->p->util->add_plugin_actions( $this, array( 
					'after_apply_filters_text' => 1,
				) );
			}
		}

		/**
		 * Re-add our shortcode after applying a text filter.
		 */
		public function action_after_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$this->add_shortcode();
		}

		public function add_shortcode() {

			if ( shortcode_exists( $this->shortcode_name ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'cannot add ' . $this->shortcode_name . ' shortcode - already exists' );
				}

				return false;
			}

        		add_shortcode( $this->shortcode_name, array( $this, 'do_shortcode' ) );

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( $this->shortcode_name . ' shortcode added' );
			}

			return true;
		}

		public function remove_shortcode() {

			if ( shortcode_exists( $this->shortcode_name ) ) {

				remove_shortcode( $this->shortcode_name );

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( $this->shortcode_name . ' shortcode removed' );
				}

				return true;

			}

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'cannot remove ' . $this->shortcode_name . ' shortcode - does not exist' );
			}

			return false;
		}

		public function do_shortcode( $atts = array(), $content = null, $tag = '' ) {

			if ( $this->p->debug->enabled ) {

				$this->p->debug->mark();
			}

			if ( SucomUtil::is_amp() ) {	// Returns null, true, or false.

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: buttons not allowed in amp endpoint'  );
				}

				return $content;

			} elseif ( is_feed() ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: buttons not allowed in rss feeds'  );
				}

				return $content;
			}

			$rrssb =& WpssoRrssb::get_instance();

			if ( ! is_array( $atts ) ) {	// Empty string if no shortcode attributes.

				$atts = array();
			}

			$atts = (array) apply_filters( 'wpsso_rrssb_sharing_shortcode_atts', $atts, $content );

			if ( empty( $atts[ 'buttons' ] ) ) {	// Nothing to do.

				return '<!-- ' . $this->shortcode_name . ' shortcode: no buttons attribute -->' . "\n\n";
			}

			$atts[ 'use_post' ]  = SucomUtil::sanitize_use_post( $atts, true );
			$atts[ 'css_class' ] = SucomUtil::sanitize_css_class( empty( $atts[ 'css_class' ] ) ? 'rrssb-shortcode' : $atts[ 'css_class' ] );

			if ( $this->p->debug->enabled ) {

				$this->p->debug->log( 'required call to WpssoPage->get_mod()' );
			}

			$mod = $this->p->page->get_mod( $atts[ 'use_post' ] );

			$type = 'sharing_shortcode_' . $this->shortcode_name;

			$atts[ 'url' ] = empty( $atts[ 'url' ] ) ? $this->p->util->get_sharing_url( $mod ) : $atts[ 'url' ];

			$ids = array_map( 'trim', explode( ',', $atts[ 'buttons' ] ) );

			unset ( $atts[ 'buttons' ] );

			$buttons_html = $rrssb->social->get_html( $ids, $atts, $mod );	// Returns html or an empty string.

			if ( ! empty( $buttons_html ) ) {

				$buttons_html = "\n" . '<!-- wpsso ' . $type . ' begin -->' . "\n" .
					'<div class="wpsso-rrssb wpsso-' . $atts[ 'css_class' ] . '">' . "\n" . 
					$buttons_html . "\n" . 	// Buttons html is trimmed, so add a newline.
					'</div><!-- .wpsso-' . $atts[ 'css_class' ] . ' -->' . "\n" . 
					'<!-- wpsso ' . $type . ' end -->' . "\n\n";
			}

			return $buttons_html;
		}
	}
}

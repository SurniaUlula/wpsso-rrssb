<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2021 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! class_exists( 'WpssoRrssbFilters' ) ) {

	class WpssoRrssbFilters {

		private $p;	// Wpsso class object.
		private $a;	// WpssoRrssb class object.
		private $msgs;	// WpssoRrssbFiltersMessages class object.
		private $upg;	// WpssoRrssbFiltersUpgrade class object.

		/**
		 * Instantiated by WpssoRrssb->init_objects().
		 */
		public function __construct( &$plugin, &$addon ) {

			static $do_once = null;

			if ( true === $do_once ) {

				return;	// Stop here.
			}

			$do_once = true;

			$this->p =& $plugin;
			$this->a =& $addon;

			require_once WPSSORRSSB_PLUGINDIR . 'lib/filters-upgrade.php';

			$this->upg = new WpssoRrssbFiltersUpgrade( $plugin, $addon );

			$this->p->util->add_plugin_filters( $this, array( 
				'option_type'          => 2,
				'save_setting_options' => 3,
				'get_defaults'         => 1,
				'get_md_defaults'      => 1,
			) );

			if ( is_admin() ) {

				require_once WPSSORRSSB_PLUGINDIR . 'lib/filters-messages.php';

				$this->msgs = new WpssoRrssbFiltersMessages( $plugin, $addon );

				$this->p->util->add_plugin_filters( $this, array( 
					'post_document_meta_tabs'   => 3,
					'post_buttons_rows'         => 4,
					'metabox_sso_inside_footer' => 2,
				), $prio = 40 );	// Run after WPSSO Core's own Standard / Premium filters.

				$this->p->util->add_plugin_filters( $this, array( 
					'status_std_features' => 3,
				), $prio = 10, $ext = 'wpssorrssb' );	// Hooks the 'wpssorrssb' filters.
			}
		}

		public function filter_option_type( $type, $base_key ) {

			if ( ! empty( $type ) ) {

				return $type;
			}

			switch ( $base_key ) {

				/**
				 * Integer options that must be 1 or more (not zero).
				 */
				case ( preg_match( '/_button_order$/', $base_key ) ? true : false ):

					return 'pos_int';

				/**
				 * Text strings that can be blank.
				 */
				case 'buttons_force_prot':
				case ( preg_match( '/_(desc|title)$/', $base_key ) ? true : false ):

					return 'ok_blank';
			}

			return $type;
		}

		/**
		 * $network is true if saving multisite network settings.
		 */
		public function filter_save_setting_options( array $opts, $network, $upgrading ) {

			if ( $network ) {

				return $opts;	// Nothing to do.
			}

			/**
			 * Reload the defaults styles if older than WPSSO RRSSB v4.0.0 (options version 30).
			 */
			if ( ! empty( $opts[ 'plugin_wpssorrssb_opt_version' ] ) && $opts[ 'plugin_wpssorrssb_opt_version' ] < 32 ) {

				$defs = $this->p->opt->get_defaults();

				$styles = apply_filters( 'wpsso_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

				foreach ( $styles as $id => $name ) {

					if ( isset( $this->p->options[ 'buttons_css_' . $id ] ) && isset( $defs[ 'buttons_css_' . $id ] ) ) {

						$this->p->options[ 'buttons_css_' . $id ] = $defs[ 'buttons_css_' . $id ];
					}
				}

				$this->p->notice->upd( __( 'The default responsive styles CSS has been reloaded and saved.', 'wpsso-rrssb' ) );
			}

			/**
			 * Update the combined and minified social stylesheet.
			 */
			WpssoRrssbSocial::update_sharing_css( $opts );

			return $opts;
		}

		public function filter_get_defaults( $defs ) {

			/**
			 * Add options using a key prefix array and post type names.
			 */
			$this->p->util->add_post_type_names( $defs, array(
				'buttons_add_to' => 1,
			) );

			$rel_url_path = parse_url( WPSSORRSSB_URLPATH, PHP_URL_PATH );	// Returns a relative URL.

			$styles = apply_filters( 'wpsso_rrssb_styles', $this->p->cf[ 'sharing' ][ 'rrssb_styles' ] );

			foreach ( $styles as $id => $name ) {

				$buttons_css_file = WPSSORRSSB_PLUGINDIR . 'css/' . $id . '.css';

				/**
				 * CSS files are only loaded once (when variable is empty) into defaults to minimize disk I/O.
				 */
				if ( empty( $defs[ 'buttons_css_' . $id ] ) ) {

					if ( ! file_exists( $buttons_css_file ) ) {

						continue;

					} elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) ) {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( 'failed to open the css file ' . $buttons_css_file . ' for reading' );
						}

						if ( is_admin() ) {

							$this->p->notice->err( sprintf( __( 'Failed to open the css file %s for reading.',
								'wpsso-rrssb' ), $buttons_css_file ) );
						}

					} else {

						if ( $this->p->debug->enabled ) {

							$this->p->debug->log( 'reading css file ' . $buttons_css_file );
						}

						$buttons_css_data = fread( $fh, filesize( $buttons_css_file ) );

						fclose( $fh );

						foreach ( array( 'plugin_url_path' => $rel_url_path ) as $macro => $value ) {

							$buttons_css_data = preg_replace( '/%%' . $macro . '%%/', $value, $buttons_css_data );
						}

						$defs[ 'buttons_css_' . $id ] = $buttons_css_data;
					}
				}
			}

			return $defs;
		}

		public function filter_get_md_defaults( $md_defs ) {

			return array_merge( $md_defs, array(
				'email_title'      => '',	// Email Subject
				'email_desc'       => '',	// Email Message
				'twitter_desc'     => '',	// Tweet Text
				'pin_desc'         => '',	// Pinterest Caption
				'linkedin_title'   => '',	// LinkedIn Title
				'linkedin_desc'    => '',	// LinkedIn Caption
				'reddit_title'     => '',	// Reddit Title
				'reddit_desc'      => '',	// Reddit Caption
				'tumblr_title'     => '',	// Tumblr Title
				'tumblr_desc'      => '',	// Tumblr Caption
				'buttons_disabled' => 0,	// Disable Sharing Buttons
			) );
		}

		public function filter_post_document_meta_tabs( $tabs, $mod, $metabox_id ) {

			switch ( $metabox_id ) {

				case $this->p->cf[ 'meta' ][ 'id' ]:	// 'sso' metabox ID.

					if ( $mod[ 'is_public' ] ) {	// Since WPSSO Core v7.0.0.

						SucomUtil::add_after_key( $tabs, 'media', 'buttons', _x( 'Share Buttons', 'metabox tab', 'wpsso-rrssb' ) );
					}

					break;
			}

			return $tabs;
		}

		public function filter_post_buttons_rows( $table_rows, $form, $head, $mod ) {

			$def_cap_title   = $this->p->page->get_caption( 'title', 0, $mod, true, false );

			/**
			 * Disable Buttons Checkbox
			 */
			$form_rows[ 'buttons_disabled' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Disable Sharing Buttons', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-buttons_disabled',
				'content'  => $form->get_checkbox( 'buttons_disabled' ),
			);

			/**
			 * Email
			 */
			$email_caption_max_len  = $this->p->options[ 'email_caption_max_len' ];
			$email_caption_hashtags = $this->p->options[ 'email_caption_hashtags' ];
			$email_caption_text     = $this->p->page->get_caption( 'excerpt', $email_caption_max_len, $mod, true, $email_caption_hashtags, true, 'none' );

			$form_rows[ 'subsection_email' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Email',
			);

			$form_rows[ 'email_title' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Subject', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-email_title',
				'content'  => $form->get_input( 'email_title', 'wide', '', 0, $def_cap_title ),
			);

			$form_rows[ 'email_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Email Message', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-email_desc',
				'content'  => $form->get_textarea( 'email_desc', '', '', $email_caption_max_len, $email_caption_text ),
			);

			/**
			 * Twitter
			 */
			$twitter_caption_type     = empty( $this->p->options[ 'twitter_caption' ] ) ? 'title' : $this->p->options[ 'twitter_caption' ];
			$twitter_caption_max_len  = WpssoRrssbSocial::get_tweet_max_len();
			$twitter_caption_hashtags = $this->p->options[ 'twitter_caption_hashtags' ];
			$twitter_caption_text     = $this->p->page->get_caption( $twitter_caption_type, $twitter_caption_max_len, $mod, true, $twitter_caption_hashtags );

			$form_rows[ 'subsection_twitter' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Twitter',
			);

			$form_rows[ 'twitter_desc' ] = array(
				'th_class' => 'medium',
				'label'    => _x( 'Tweet Text', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-twitter_desc',
				'content'  => $form->get_textarea( 'twitter_desc', '', '', $twitter_caption_max_len, $twitter_caption_text ),
			);

			/**
			 * Pinterest
			 */
			$pin_caption_max_len  = $this->p->options[ 'pin_caption_max_len' ];
			$pin_caption_hashtags = $this->p->options[ 'pin_caption_hashtags' ];
			$pin_caption_text     = $this->p->page->get_caption( 'excerpt', $pin_caption_max_len, $mod, true, $pin_caption_hashtags );

			/**
			 * Get the default Pinterest image pid and URL.
			 */
			$size_name     = 'wpsso-pinterest';
			$media_request = array( 'pid', 'img_url' );
			$pin_media     = $this->p->og->get_media_info( $size_name, $media_request, $mod, array( 'p', 'schema', 'og' ) );

			/**
			 * Get the smaller thumbnail image as a preview image.
			 */
			if ( ! empty( $pin_media[ 'pid' ] ) ) {

				$pin_media[ 'img_url' ] = $this->p->media->get_attachment_image_url( $pin_media[ 'pid' ], 'thumbnail', false );
			}

			$form_rows[ 'subsection_pinterest' ] = array(
				'td_class' => 'subsection',
				'col_span' => '3',
				'header'   => 'h4',
				'label'    => 'Pinterest',
			);

			$form_rows[ 'pin_desc' ] = array(
				'th_class' => 'medium',
				'td_class' => 'top',
				'label'    => _x( 'Pinterest Caption', 'option label', 'wpsso-rrssb' ),
				'tooltip'  => 'post-pin_desc',
				'content'  => $form->get_textarea( 'pin_desc', '', '', $pin_caption_max_len, $pin_caption_text ),
			);

			/**
			 * Other Title / Caption Input
			 */
			foreach ( array(
				'linkedin' => 'LinkedIn',
				'reddit' => 'Reddit',
				'tumblr' => 'Tumblr',
			) as $opt_pre => $name ) {

				$other_caption_max_len  = $this->p->options[ $opt_pre . '_caption_max_len' ];
				$other_caption_hashtags = $this->p->options[ $opt_pre . '_caption_hashtags' ];
				$other_caption_text     = $this->p->page->get_caption( 'excerpt', $other_caption_max_len, $mod, true, $other_caption_hashtags );

				$form_rows[ 'subsection_' . $opt_pre ] = array(
					'td_class' => 'subsection',
					'col_span' => '3',
					'header'   => 'h4',
					'label'    => $name,
				);

				$form_rows[ $opt_pre . '_title' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Title', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'post-' . $opt_pre . '_title',
					'content'  => $form->get_input( $opt_pre . '_title', 'wide', '', 0, $def_cap_title ),
				);

				$form_rows[ $opt_pre . '_desc' ] = array(
					'th_class' => 'medium',
					'label'    => sprintf( _x( '%s Caption', 'option label', 'wpsso-rrssb' ), $name ),
					'tooltip'  => 'post-' . $opt_pre . '_desc',
					'content'  => $form->get_textarea( $opt_pre . '_desc', '', '', $other_caption_max_len, $other_caption_text ),
				);
			}

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}

		public function filter_metabox_sso_inside_footer( $metabox_html, $mod ) {

			if ( empty( $mod[ 'is_public' ] ) ) {

				if ( $this->p->debug->enabled ) {

					$this->p->debug->log( 'exiting early: ' . $mod[ 'name' ] . ' id ' . $mod[ 'id' ] . ' is not public' );
				}

				return $metabox_html;
			}

			$doing_ajax = SucomUtilWP::doing_ajax();

			$metabox_html .= $this->a->social->get_buttons( $text = '', 'admin_edit', $mod );

			/**
			 * The type="text/javascript" attribute is unnecessary for JavaScript resources and creates warnings in the W3C validator.
			 */
			$metabox_html .= <<<EOF
<script>

function runRrssbInit() {

	var rrssbInitCount = 0;

	var rrssbInitExists = setInterval( function() {

		if ( 'function' === typeof rrssbInit ) {

			rrssbInit();

			if ( ++rrssbInitCount > 5 ) {

				clearInterval( rrssbInitExists );
			}
		}

	}, 1000 );
}

runRrssbInit();

</script>
EOF;

			return $metabox_html;
		}

		/**
		 * Filter for 'wpssorrssb_status_std_features'.
		 */
		public function filter_status_std_features( $features, $ext, $info ) {

			if ( ! empty( $info[ 'lib' ][ 'submenu' ][ 'rrssb-styles' ] ) ) {

				$features[ '(sharing) Sharing Stylesheet' ] = array(
					'label_transl' => _x( '(sharing) Sharing Stylesheet', 'lib file description', 'wpsso-rrssb' ),
					'status'       => empty( $this->p->options[ 'buttons_use_social_style' ] ) ? 'off' : 'on',
				);
			}

			if ( ! empty( $info[ 'lib' ][ 'shortcode' ][ 'sharing' ] ) ) {

				$features[ '(sharing) Sharing Shortcode' ] = array(
					'label_transl' => _x( '(sharing) Sharing Shortcode', 'lib file description', 'wpsso-rrssb' ),
					'classname'    => $ext . 'shortcodesharing',
				);
			}

			if ( ! empty( $info[ 'lib' ][ 'widget' ][ 'sharing' ] ) ) {

				$features[ '(sharing) Sharing Widget' ] = array(
					'label_transl' => _x( '(sharing) Sharing Widget', 'lib file description', 'wpsso-rrssb' ),
					'classname'    => $ext . 'widgetsharing',
				);
			}

			return $features;
		}
	}
}

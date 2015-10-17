<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoRrssbSubmenuSharingStyles' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoRrssbSubmenuSharingStyles extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->p->util->add_plugin_filters( $this, array( 
				'messages_tooltip' => 2,	// tooltip messages filter
				'messages_info' => 2,		// info messages filter
			) );
		}

		public function filter_messages_tooltip( $text, $idx ) {
			if ( strpos( $idx, 'tooltip-buttons_' ) !== 0 )
				return $text;

			switch ( $idx ) {
				case 'tooltip-buttons_use_social_css':
					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minimized</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minimized stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-rrssb' ), _x( 'Sharing Styles', 'lib file description', 'wpsso-rrssb' ), WpssoRrssbSharing::$sharing_css_url, WpssoRrssbSharing::$sharing_css_url );
					break;

				case 'tooltip-buttons_enqueue_social_css':
					$text = __( 'Have WordPress enqueue the social stylesheet instead of adding the CSS to in the webpage HTML (default is unchecked). Enqueueing the stylesheet may be desirable if you use a plugin to concatenate all enqueued styles into a single stylesheet URL.', 'wpsso-rrssb' );
					break;
			}
			return $text;
		}

		public function filter_messages_info( $text, $idx ) {
			if ( strpos( $idx, 'info-style-rrssb-' ) !== 0 )
				return $text;

			$lca =  $this->p->cf['lca'];
			$short = $this->p->cf['plugin'][$lca]['short'];

			switch ( $idx ) {

				case 'info-style-rrssb-sharing':

					$notes_url = $this->p->cf['plugin'][$lca]['url']['notes'];
					$text = '<p>'.$short.' uses the \''.$lca.'-rrssb\' class to wrap all sharing buttons, and each button has it\'s own individual class name as well. This tab can be used for CSS common to all sharing button locations.</p>';
					break;

				case 'info-style-rrssb-content':

					$text = '<p>Social sharing buttons &mdash; enabled / added to the content text from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page &mdash; are assigned the \''.$lca.'-rrssb-content\' class.</p> 

					<p>Example:</p><pre>
div.'.$lca.'-rrssb
  .'.$lca.'-rrssb-content
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
					break;

				case 'info-style-rrssb-excerpt':

					$text = '<p>Social sharing buttons &mdash; enabled / added to the excerpt text from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page &mdash; are assigned the \''.$lca.'-rrssb-excerpt\' class.</p> 
					
					<p>Example:</p><pre>
div.'.$lca.'-rrssb
  .'.$lca.'-rrssb-excerpt
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
					break;

				case 'info-style-rrssb-sidebar':

					$text = '<p>Social sharing buttons &mdash; enabled / added to the CSS sidebar from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page &mdash; are assigned the \''.$lca.'-rrssb-sidebar\' ID.</p> 
					
					<p>In order to achieve a vertical display, each un-ordered list (ul) contains a single list item (li).</p>

					<p>Example:</p><pre>
div.'.$lca.'-rrssb 
  #'.$lca.'-rrssb-sidebar
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
					break;

				case 'info-style-rrssb-shortcode':

					$text = '<p>Social sharing buttons added from a shortcode are assigned the \''.$lca.'-rrssb-shortcode\' class by default.</p> 

					<p>Example:</p><pre>
div.'.$lca.'-rrssb 
  .'.$lca.'-rrssb-shortcode
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
					break;

				case 'info-style-rrssb-widget':

					$text = '<p>Social sharing buttons enabled in the '.$short.' widget are assigned the \''.$lca.'-rrssb-widget\' class (along with additional unique CSS ID names).</p> 

					<p>Example:</p><pre>
aside.widget 
  .'.$lca.'-rrssb-widget 
    ul.rrssb-buttons
        li.rrssb-facebook { }</pre>';
					break;

				case 'info-style-rrssb-admin_edit':

					$text = '<p>Social sharing buttons &mdash; enabled / added to the admin editing pages from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page &mdash; are assigned the \''.$lca.'-rrssb-admin_edit\' class.</p> 

					<p>Example:</p><pre>
div.'.$lca.'-rrssb
  .'.$lca.'-rrssb-admin_edit
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
					break;

				case 'info-style-rrssb-woo_short': 

					$text = '<p>Social sharing buttons &mdash; enabled / added to the WooCommerce Short Description text from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page &mdash; are assigned the \''.$lca.'-rrssb-woo_short\' class.</p>

					<p>Example:</p><pre>
div.'.$lca.'-rrssb
  .'.$lca.'-rrssb-woo_short
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
      					break;

				case 'info-style-rrssb-bbp_single': 

					$text = '<p>Social sharing buttons &mdash; enabled / added at the top of bbPress Single Templates from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page &mdash; are assigned the \''.$lca.'-rrssb-bbp_single\' class.</p>

					<p>Example:</p><pre>
div.'.$lca.'-rrssb
  .'.$lca.'-rrssb-bbp_single
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
      					break;

				case 'info-style-rrssb-bp_activity': 

					$text = '<p>Social sharing buttons &mdash; enabled / added to BuddyPress Activities from the '.$this->p->util->get_admin_url( 'sharing-buttons', 'Sharing Buttons' ).' settings page &mdash; are assigned the \''.$lca.'-rrssb-bp_activity\' class.</p>

					<p>Example:</p><pre>
div.'.$lca.'-rrssb
  .'.$lca.'-rrssb-bp_activity
    ul.rrssb-buttons
      li.rrssb-facebook {}</pre>';
      					break;
			}
			return $text;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_sharing_styles',
				_x( 'Social Sharing Styles', 'metabox title', 'wpsso-rrssb' ),
					array( &$this, 'show_metabox_sharing_styles' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_sharing_styles() {
			$metabox = 'sharing-styles';

			if ( file_exists( WpssoRrssbSharing::$sharing_css_file ) &&
				( $fsize = filesize( WpssoRrssbSharing::$sharing_css_file ) ) !== false )
					$css_min_msg = ' <a href="'.WpssoRrssbSharing::$sharing_css_url.'">minimized css is '.$fsize.' bytes</a>';
			else $css_min_msg = '';

			$this->p->util->do_table_rows( array( 
				$this->p->util->get_th( _x( 'Use the Social Stylesheet',
					'option label', 'wpsso-rrssb' ), 'highlight', 'buttons_use_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_use_social_css' ).$css_min_msg.'</td>',

				$this->p->util->get_th( _x( 'Enqueue the Stylesheet',
					'option label', 'wpsso-rrssb' ), null, 'buttons_enqueue_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_css' ).'</td>',
			) );

			$tabs = apply_filters( $this->p->cf['lca'].'_style_tabs',
				WpssoRrssbSharing::$cf['sharing']['style'] );
			$rows = array();
			foreach ( $tabs as $key => $title )
				$rows[$key] = array_merge( $this->get_rows( $metabox, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		protected function get_rows( $metabox, $key ) {
			$rows['buttons_css_'.$key] = '<th class="textinfo">'.$this->p->msgs->get( 'info-style-'.$key ).'</th>'.
			'<td'.( isset( $this->p->options['buttons_css_'.$key.':is'] ) &&
				$this->p->options['buttons_css_'.$key.':is'] === 'disabled' ? ' class="blank"' : '' ).'>'.
			$this->form->get_textarea( 'buttons_css_'.$key, 'tall code' ).'</td>';
			return $rows;
		}
	}
}

?>

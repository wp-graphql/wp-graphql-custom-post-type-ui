<?php
/** 
* Plugin Name: WPGraphQL Custom Post Type UI
* Description: Adds WPGraphQL settings to Custom Post Type UI
* Author: rachelbahl
* Author URI: https://wpgraphqlgal.com
* Version: 1.0
* Text Domain: wp-graphql-custom-post-type-ui
* Requires at least: 5.1.0
* Tested up to: 5.2.0
* Requires PHP: 5.6
* License: GPL-3
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/**
* This class creates settings for Custom Post Type UI to show Custom Post Types in GraphQL	
*/

class WPGraphQL_CPT_UI {
	
	protected $show_in_graphql = false;
	protected $graphql_single_name = '';
	protected $graphql_plural_name = '';
	
	/**
	* Initializes the plugin functionality
	*/
	public function init() {
		add_filter( 'cptui_pre_register_post_type', [ $this, 'add_graphql_settings_to_post_type_registry' ], 10, 3 );
		add_action( 'cptui_post_type_after_fieldsets', [ $this, 'add_graphql_settings' ], 10, 1 );
		add_filter( 'cptui_before_update_post_type', [ $this, 'before_update_post_type' ], 10, 2 );
		add_filter( 'cptui_pre_save_post_type', [ $this, 'save_graphql_settings' ], 10, 2 );
	}
	
	/**
	* Set defaults for post type settings
	*/
	public function add_graphql_settings_to_post_type_registry( $args, $name, $post_type ) {
		$args['show_in_graphql' ] = isset( $post_type['show_in_graphql'] ) ? (bool) $post_type['show_in_graphql'] : false;
		$args['graphql_single_name' ] = ! empty( $post_type['graphql_single_name'] ) ?  $post_type['graphql_single_name'] : null;
		$args['graphql_plural_name' ] = ! empty( $post_type['graphql_plural_name'] ) ?  $post_type['graphql_plural_name'] : null;
		return $args;
	}
	
	/**
	* Capture settings from form submission for saving	
	*/
	public function before_update_post_type( $data ) {
		$this->show_in_graphql = isset( $data[ 'cpt_custom_post_type' ]['show_in_graphql'] ) ? $data[ 'cpt_custom_post_type' ]['show_in_graphql'] : false;
		$this->graphql_single_name = isset( $data[ 'cpt_custom_post_type' ]['graphql_single_name'] ) ? $data[ 'cpt_custom_post_type' ]['graphql_single_name'] : '';
		$this->graphql_plural_name = isset( $data[ 'cpt_custom_post_type' ]['graphql_plural_name'] ) ? $data[ 'cpt_custom_post_type' ]['graphql_plural_name'] : '';
	}
	
	/**
	* Save values from form submission	
	*/
	public function save_graphql_settings( $post_types, $name ) {
		$post_types[ $name ]['show_in_graphql'] = $this->show_in_graphql;
		$post_types[ $name ]['graphql_single_name'] = $this->graphql_single_name;
		$post_types[ $name ]['graphql_plural_name'] = $this->graphql_plural_name;
		return $post_types;
	}
	
	/**
	* Add settings fields to Custom Post Type UI form	
	*/
	public function add_graphql_settings( $ui ) {
		
		$tab = ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' === $_GET['action'] ) ? 'edit' : 'new';
		
		if ( 'edit' === $tab ) {
			$post_types = cptui_get_post_type_data();
			$selected_post_type = cptui_get_current_post_type( false );
			if ( $selected_post_type ) {
				if ( array_key_exists( $selected_post_type, $post_types ) ) {
					$current = $post_types[ $selected_post_type ];
				}
			}
		}
		
		?>
		<div class="cptui-section postbox">
				<button type="button" class="handlediv button-link" aria-expanded="true">
					<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: GraphQL Settings', 'wp-graphql-custom-post-type-ui' ); ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</button>
				<h2 class="hndle">
					<span><?php esc_html_e( 'GraphQL Settings', 'wp-graphql-custom-post-type-ui' ); ?></span>
				</h2>
				<div class="inside">
					<div class="main">
						<table class="form-table cptui-table">
							<?php
							
								$select = array(
								'options' => array(
									array( 'attr' => '0', 'text' => esc_attr__( 'False', 'wp-graphql-custom-post-type-ui' ) ),
									array( 'attr' => '1', 'text' => esc_attr__( 'True', 'wp-graphql-custom-post-type-ui' ), 'default' => 'true' ),
								),
							);
							
							$selected = ( isset( $current ) && ! empty( $current['show_in_graphql'] ) ) ? disp_boolean( $current['show_in_graphql'] ) : '';
							$select['selected'] = ( ! empty( $selected ) && ! empty( $current['show_in_graphql'] ) ) ? $current['show_in_graphql'] : '';
							echo $ui->get_select_input( array(
								'namearray'  => 'cpt_custom_post_type',
								'name'       => 'show_in_graphql',
								'labeltext'  => esc_html__( 'Show in GraphQL', 'wp-graphql-custom-post-type-ui' ),
								'aftertext'  => esc_html__( '(Custom Post Type UI default: true) Whether or not to show this post type data in the WP GraphQL.', 'wp-graphql-custom-post-type-ui' ),
								'selections' => $select,
							) );
							

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'graphql_single_name',
								'labeltext' => esc_html__( 'GraphQL Single Name', 'wp-graphql-custom-post-type-ui' ),
								'aftertext' => esc_attr__( 'Single name for this Post Type in the GraphQL API.', 'wp-graphql-custom-post-type-ui' ),
								'textvalue' => ( isset( $current['graphql_single_name'] ) ) ? esc_attr( $current['graphql_single_name'] ) : '',
							) );

							echo $ui->get_text_input( array(
								'namearray' => 'cpt_custom_post_type',
								'name'      => 'graphql_plural_name',
								'labeltext' => esc_html__( 'GraphQL Plural Name', 'wp-graphql-custom-post-type-ui' ),
								'aftertext' => esc_attr__( 'Plural name for this Post Type in the GraphQL API.', 'wp-graphql-custom-post-type-ui' ),
								'textvalue' => ( isset( $current['graphql_plural_name'] ) ) ? esc_attr( $current['graphql_plural_name'] ) : '',
							) );
							?>
						</table>
					</div>
				</div>
		</div>
		<?php
	}
	
}

add_action( 'plugins_loaded', 'init_wpgraphql_cptui' );

function init_wpgraphql_cptui() {
	$wpgraphql_cpt_ui = new WPGraphQL_CPT_UI();
	$wpgraphql_cpt_ui->init();
}

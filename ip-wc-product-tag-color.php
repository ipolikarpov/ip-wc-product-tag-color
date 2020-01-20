<?php

/**
 *
 * @link              https://polikarpov.dev
 * @since             1.0.0
 * @package           Ip_Wc_Product_Tag_Color
 *
 * @wordpress-plugin
 * Plugin Name:       WC Product Tags colorpicker
 * Plugin URI:        https://polikarpov.dev/ip-wc-product-tag-color
 * Description:       Select backgound and text color for your WooCommerce Product Tags
 * Version:           1.0.0
 * Author:            Ivan Polikarpov
 * Author URI:        https://polikarpov.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ip-wc-product-tag-color
 * Domain Path:       /languages
 * WC tested up to:		3.8.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

register_activation_hook( __FILE__, 'ip_wc_product_tag_color_activation' ); //Activation

/**
 * Check requirements when activate
 * 
 * @since    1.0.0
 */

function ip_wc_product_tag_color_activation() {
	/**
	* Check if WooCommerce is active
	**/
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		set_transient( 'ip_wc_product_tag_color_activated', true, 5 );
	} else {
		set_transient( 'ip_wc_product_tag_color_not_activated', true, 5 );
	}
}

add_action( 'admin_notices', 'ip_wc_product_tag_color_activation_messages' );


/**
 * Check if pugin successfully activated or not and show admin messages
 * 
 * @since    1.0.0
 */

function ip_wc_product_tag_color_activation_messages() {
    if (get_transient( 'ip_wc_product_tag_color_not_activated' ) ) :
    	?>
    	<div class="notice error is-dismissable">
	        <p><?php _e( '<strong>WC Tags Color</strong> requires WooCommerce to be installed and activated!', 'ip-wc-product-tag-color' ); ?></p>
	    </div>
    	<?php
    	deactivate_plugins(plugin_basename( __FILE__ ));
    	delete_transient( 'ip_wc_product_tag_color_not_activated' );
    	unset($_GET['activate']);
    endif;
    

    if (get_transient( 'ip_wc_product_tag_color_activated' ) ) :
    	?>
		<div class="notice updated is-dismissable">
	        <p><?php _e( '<strong>WC Tags Color</strong> is active now!', 'ip-wc-product-tag-color' ); ?></p>
	    </div>
    	<?php
    	delete_transient( 'ip_wc_product_tag_color_activated' );
    	unset($_GET['activate']);
    endif;
}

add_action( 'plugins_loaded', 'ip_wc_product_tag_color_init' );

function ip_wc_product_tag_color_init() {
	add_action( 'admin_enqueue_scripts', 'ip_wc_product_tag_color_enqueue_color_picker' );
	add_action( 'product_tag_add_form_fields', 'ip_wc_product_tag_color_add_fields', 10, 2 );
	add_action( 'product_tag_edit_form_fields', 'ip_wc_product_tag_color_edit_fields', 199 );
	add_action( 'edited_product_tag', 'ip_wc_product_tag_color_save_fields' );  
	add_action( 'create_product_tag', 'ip_wc_product_tag_color_save_fields' );
	add_action( 'admin_footer', 'ip_wc_product_tag_color_admin_footer_script', 99 );
	add_filter( 'term_links-product_tag', 'ip_wc_product_tag_color_styling' , 99, 1 );
}

function ip_wc_product_tag_color_enqueue_color_picker() {
	wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'my-script-handle', plugins_url('my-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
}

function ip_wc_product_tag_color_add_fields( $term ) {
	?>
	<div class="form-field">
		<label for="tagBgColor"><?php _e( 'Background color', 'ip-wc-product-tag-color' ); ?></label>
		<input type="text" name="tagBgColor" id="tagBgColor" class="colorpicker" value="#FFFFFF">
	</div>
	<div class="form-field">
		<label for="tagTxtColor"><?php _e( 'Text color', 'ip-wc-product-tag-color' ); ?></label>
		<input type="text" name="tagTxtColor" id="tagTxtColor" class="colorpicker" value="#000000">
	</div>
	<?php
}

function ip_wc_product_tag_color_edit_fields( $term ) {
	$tagBgColor = get_term_meta( $term->term_id, 'tagBgColor', true ); 
	$tagTxtColor = get_term_meta( $term->term_id, 'tagTxtColor', true ); 
	?>
	<table class="form-table">
		<tbody>
		<tr class="form-field">
			<th>
				<label for="tagBgColor"><?php _e( 'Background color', 'ip-wc-product-tag-color' ); ?></label>
			</th>
			<td>
				<input type="text" name="tagBgColor" id="tagBgColor" class="colorpicker" value="<?php echo esc_attr( $tagBgColor ) ? esc_attr( $tagBgColor ) : '#FFFFFF'; ?>">
			</td>
		</tr>
		<tr class="form-field">
			<th>
				<label for="tagTxtColor"><?php _e( 'Text color', 'ip-wc-product-tag-color' ); ?></label>
			</th>
			<td>
				<input type="text" name="tagTxtColor" id="tagTxtColor" class="colorpicker" value="<?php echo esc_attr( $tagTxtColor ) ? esc_attr( $tagTxtColor ) : '#000000'; ?>">
			</td>
		</tr>
		</tbody>
	</table>
<?php
}

function ip_wc_product_tag_color_save_fields( $term_id ) {
	if ( isset( $_POST['tagBgColor'] ) ) {
		$tagBgColor = $_POST['tagBgColor'];
		update_term_meta( $term_id, 'tagBgColor', $tagBgColor );
	}
	if ( isset( $_POST['tagTxtColor'] ) ) {
		$tagTxtColor = $_POST['tagTxtColor'];
		update_term_meta( $term_id, 'tagTxtColor', $tagTxtColor );
	}		
} 

function ip_wc_product_tag_color_admin_footer_script() {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($){
    	$('input.colorpicker').wpColorPicker();
	});
	</script>
	<?php
}

function ip_wc_product_tag_color_styling($links) {
	global $product;
	$tags = wp_get_object_terms($product->get_id(), 'product_tag');
	$links = array();
	foreach ($tags as $key => $tag) {
		$links[] = '<a href="'.get_term_link($tag).'" class="colored-tag badge" style="background-color: '.get_term_meta( $tag->term_id, 'tagBgColor', true ).'; color: '.get_term_meta( $tag->term_id, 'tagTxtColor', true ).';">' . esc_html( $tag->name ) . '</a>'; 
	}
	return $links;
}

?>
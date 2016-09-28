<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.horiondigital.com
 * @since      1.0.0
 *
 * @package    Acf_Front_End_Editor
 * @subpackage Acf_Front_End_Editor/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Acf_Front_End_Editor
 * @subpackage Acf_Front_End_Editor/public
 * @author     Audrius Rackauskas <audrius@horiondigital.com>
 */
class Acf_Front_End_Editor_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
        if(is_user_logged_in()):
          wp_enqueue_style( $this->plugin_name.'-medium', plugin_dir_url( __FILE__ ) . 'css/medium-editor.min.css', array(), $this->version, 'all' );
        wp_enqueue_style( $this->plugin_name.'-theme', plugin_dir_url( __FILE__ ) . 'css/themes/default.css', array(), $this->version, 'all' );
		  wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/acf-front-end-editor-public.css', array(), $this->version, 'all' );
        endif;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        if(is_user_logged_in()):
            wp_register_script( $this->plugin_name.'-medium', plugin_dir_url( __FILE__ ) . 'js/medium-editor.min.js', array( 'jquery' ), $this->version, false );
     		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/acf-front-end-editor-public.js', array( 'jquery' ), $this->version, false );
            wp_localize_script( $this->plugin_name, 'meta', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'page' => get_queried_object(),
            ));
            wp_enqueue_script( $this->plugin_name.'-medium');
            wp_enqueue_script( $this->plugin_name);
            wp_enqueue_media();
        endif;
	}

    /**
     * Renders text fields and text areas with additional html that allows to target these areas via javascript
     * @param  [String] $value
     * @param  [Int] $post_id
     * @param  [Object] $field 
     * @return [String] returns edited value with additional html
     * 
     * @since    1.0.0
     */
    public function acf_targeter( $value, $post_id, $field ) {
            if(strpos($value, 'http') === 0 || $value == '#' || $value == '' || filter_var($value, FILTER_VALIDATE_EMAIL) || is_admin()) {
                $value = $value;
            } else {
                $key=$field['key'];
                $label=$field['name'];
                $type = 'labas';
                $value = '<d contenteditable data-postid="'.$post_id.'" data-name="'.$label.'" data-key="'.$field['key'].'">'.$value.'</d>';
            }
        return $value;
    }

    /**
     * Renders wysiwyg fields with additional html that allows to target these areas via javascript
     * @param  [String] $value
     * @param  [Int] $post_id
     * @param  [Object] $field 
     * @return [String] returns edited value with additional html
     *
     * @since    1.0.0
     */
    public function acf_wysiwyg_targeter( $value, $post_id, $field ) {
        $key=$field['key'];
        $label=$field['name'];
        $value = '<div contenteditable class="editableHD" data-postid="'.$post_id.'" data-name="'.$label.'" data-key="'.$field['key'].'"><p></p>'.$value.'</div>';
        return $value;
    }

    /**
     * Formats field value to html if there is any
     * @param  [String] $value
     * @param  [Int] $post_id
     * @param  [Object] $field 
     * @return [String] returns edited value
     *
     * @since    1.0.0
     */
    public function my_acf_format_value( $value, $post_id, $field ) {
        $value = html_entity_decode($value);
        return $value;
    }


    /**
     * Registers filters required for ACF field rendering
     * @since 2.0.0
     */
    public function register_filters() {
        if(is_user_logged_in() && !is_admin()):
            add_filter('acf/load_value/type=text',  array( $this, 'acf_targeter'), 10, 3);
            add_filter('acf/load_value/type=textarea', array( $this, 'acf_targeter'), 10, 3);
            add_filter('acf/load_value/type=wysiwyg', array( $this, 'acf_wysiwyg_targeter'), 10, 3);
            add_filter('acf/load_value/type=image', array( $this, 'acf_image_targeter'), 10, 3);
            add_filter('acf/format_value/type=text', array( $this, 'my_acf_format_value'), 10, 3);
            add_filter('acf/format_value/type=textarea', array( $this, 'my_acf_format_value'), 10, 3);
            add_filter('acf/format_value/type=wysiwyg', array( $this, 'my_acf_format_value'), 10, 3);
        endif;
    }

    /**
     * Updates edited ACF fields in the database
     * 
     * @since 1.0.0
     */
    public function update_texts() {
        if ( isset($_REQUEST) ) {
            if(is_user_logged_in()):

            $siteID   = $_REQUEST['siteID'];
            $textArr  = $_REQUEST['textArr'];

            foreach ($textArr as $arr):
                $key  = $arr[0];
                $text = $arr[1];
                $name = $arr[2];
                $postid = $arr[3];
                $obj = get_field_object($name,$postid);
                $type = ($obj['key'] ? 'single' : 'repeater');
                $acf_post = get_post( $obj['parent'] );
                update_field($name, $text, $postid);
            endforeach;
            endif;
        }
        die();
    }
    static function editable_image($value, $post_id = 0, $field){
        if ( $post_id < 1){
            global $post;
            $post_id = $post->ID;
            if ( $post_id < 0 ){
                throw new Exception("No Post ID or invalid Post ID found using " . __FUNCTION__, 1);
                return;
            }
        }
        $field_object = get_field_object($field_name);
        // print_x($field_object);
        if ( !isset($field_object['type']) || $field_object['type'] != 'image' ){
            return;
        }
        if ( !isset( $field_object['return_format'] ) || !$field_object['return_format'] || !isset( $field_object['key'] )){
            throw new Exception("Internal Error");
            return;
        }
        $value = get_field($field_name, $post_id);
        $key = $field_object['key'];
        switch ( $return_format = $field_object['return_format'] ){
            case 'id' :
                $value = wp_get_attachment_image_src( $value, $size );
                $src = $value[0];
            break;
            case 'array' :
                $src = $value['url'];
            break;
            case 'url' :
                $src = $value;
            break;
            default :
              throw new Exception("Return value not supported.");
            break;
        }
        echo '<div class="acf $field_name">';
          echo "<img class='display-$key' src='$src' alt='>";
          echo "<input type='button' class='button edit-image-src editableImage' value='Edit' data-name='$field_name' data-key='$key' data-postid='$post_id' data-attachmentid=''>";
        echo '</div>';
        ?>
        <script>
        (function( $, document ){
            var frame,
                metaBox = $('#meta-box-id.postbox'), // Your meta box id here
                addImgLink = metaBox.find('.upload-custom-img'),
                delImgLink = metaBox.find( '.delete-custom-img'),
                imgContainer = metaBox.find( '.custom-img-container'),
                imgIdInput = metaBox.find( '.custom-img-id' );
            $('input.edit-image-src').on('click', function(){
                
                // Get media attachment details from the frame state
                event.preventDefault();
                
                var source = this;
                var $source = $(this);
                // If the media frame already exists, reopen it.
                if ( frame ) {
                  frame.open();
                  return;
                }
                
                // Create a new media frame
                frame = wp.media({
                  title: 'Select or Upload Media',
                  button: {
                    text: 'Choose'
                  },
                  multiple: false  // Set to true to allow multiple files to be selected
                });

                frame.on( 'select', function() {

                  var attachment = frame.state().get('selection').first();

                  attachment = attachment.toJSON();
                  // console.log(attachment);

                  // console.log(source);

                  // Send the attachment URL to our custom image input field.
                  var key = $source.data('key');
                  var display = $('.display-' + key);
                  if ( display ){
                    display.attr('src', attachment.url);
                  }
                  $source.addClass('imageChanged');
                  $source.data('attachmentid', attachment.id);

                  // Send the attachment id to our hidden input
                  imgIdInput.val( attachment.id );

                  // Hide the add image link
                  addImgLink.addClass( 'hidden' );

                  // Unhide the remove image link
                  delImgLink.removeClass( 'hidden' );

                });

                // Finally, open the modal on click
                frame.open();
            });
        })( jQuery, document, undefined );
        </script>
        <?php
    }
}
/**
 * Get editable image markup
 * 
 * @uses /Acf_Front_End_Editor_Public::editable_image()
 * @package /Acf_Front_End_Editor_Public
 */
function editable_image($field_name, $post_id = 0, $size = "full"){
    Acf_Front_End_Editor_Public::editable_image($field_name, $post_id, $size);
}

function print_x($var){
    echo "<pre class='print-x'>";
    echo "<small>print_r results</small>";
    print_r($var);
    echo "</pre>";
}

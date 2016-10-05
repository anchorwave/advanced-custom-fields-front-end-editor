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
	 * @var      string    $version The current version of this plugin.
	 */
	private $version;

  /**
   * A simple array to cache the real meta key used by repeater field sub-items. Since format_value does not provide this information, we use the load_value hook to cache it first and then retreive it later in format_value hook.
   * 
   * @since     2.1.0
   * @access    public
   * @var       array     $field_cache  An array of (ACF field 'key')->(post meta key)
   * @author jbokhari
   */
  public $field_cache = array();

  /**
   * Initialize the class and set its properties.
   *
   * @since      1.0.0
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
   * Used only to cache the fields real meta_key value which is not easily accessible in the format_value hook. This hook does not alter the $value returned. Editable images are handled through JS and the format_value hook later on.
   * 
   * @param Int $value Value of the field (should be image attachment ID)
   * @param Int $post_id Post ID associated with this field
   * @param Object $field Field object with meta information
   * @return Int $value Returns unaltered $value for the image after completing caching task(s)
   * 
   * @since 2.1.0
   * @author jbokhari
   */
  public function acf_image_targeter( $value, $post_id, $field ) {
      $this->field_cache[$field['key']] = $field['name'];
      return $value;
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
            if(strpos($value, 'http') === 0 || substr($value, 0, 1) == "/" || $value == '#' || $value == '' || filter_var($value, FILTER_VALIDATE_EMAIL) || is_admin()) {
                $value = $value;
            } else {
                $key=$field['key'];
                $label=$field['name'];
                $type = 'labas';
                $value = '<div contenteditable data-postid="'.$post_id.'" data-name="'.$label.'" data-key="'.$field['key'].'">'.$value.'</div>';
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

            add_filter('acf/format_value/type=image', array( $this, 'acf_image_format_value'), 10, 3);
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
    /**
     * Appdnds URL parameters to image urls which is then picked up by JavaScript to make it editable on the front end
     * 
     * @param int|string|mixed[] $value original value returned by ACF, it may already be filtered or filtered again before being exposed on page templates
     * @param int $post_id original value returned by ACF
     * @param array $field Field object
     * 
     * @see wp filter acf/format_value
     * 
     * @todo add/remove image support (here and other areas)
     * @since 2.1.0
     * @author jbokhari
     */
    function acf_image_format_value($value, $post_id, $field){
      // ::Conditions::
         //Check certain things before proceeding, each case returns original value on failure
      // May need to check this logic later
      if ( empty($value) ){
        return $value;
      }
      
      // ignore errors with $real_name not caching
      @$real_name = $this->field_cache[$field['key']];
      if ( !isset($real_name) || empty($real_name) ){
        throw new Exception("Internal Error: Cache was not created");
      }

      // ignore admin, non-logged-in users
      if ( !current_user_can('delete_plugins') || is_admin() ){
        return $value;
      }

      // Prob not necessary, only hooked into image fields...
      if ( !isset($field['type']) || $field['type'] != 'image' ){
          return $value;
      }

      // When the field has yet to be populated, the return format isn't identifiable yet
      // Should images that are not populated be editable? ...TODO add/remove image

      if ( !isset( $field['return_format'] ) || !$field['return_format'] || !isset( $field['key'] )){
          return $value;
      }

      $key = $field['key'];
      switch ( $return_format = $field['return_format'] ){
          case 'id' :
            //not sure how to handle ID return format yet, ignored now
          break;
          case 'array' :
              foreach( get_intermediate_image_sizes() as $size ){
                $value['sizes'][$size] = $value['sizes'][$size] . "?field_key={$key}&post_id={$post_id}&field_name={$real_name}&acfImageEditable";
              }
              $value['url'] = $value['url'] . "?field_key={$key}&post_id={$post_id}&field_name={$real_name}&acfImageEditable";
          break;
          case 'url' :
              $value = $value . "?field_key={$key}&post_id={$post_id}&field_name={$real_name}&acfImageEditable";
          break;
          default :
            // If ACF adds a new return format not supported here
            throw new Exception("Return value not supported.");
          break;
      }
      return $value;
    }
}
if (!function_exists('print_clean') ) :
  /**
   * Debugging function to wrap print_r in <pre> tag and make it pretty
   * 
   * @param $var mixed the variable to print, typically an array or object but could be any type
   * @return void
   * 
   * @since 2.1.0
   * @author jbokhari
   */
  function print_clean($var){
      echo "<pre class='print-x'>";
      echo "<small>print_r results</small>";
      print_r($var);
      echo "</pre>";
  }
endif;


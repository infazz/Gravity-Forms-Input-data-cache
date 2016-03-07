<?php
/**
 * Gravity Forms Input data cache
 *
 * @package GFInputDataCache
 * @author Gleb Makarov <gleb@blueglass.ee>
 * @copyright Copyright (c) 2015 BlueGlass Interactive ОÜ
 * @license GPL-2.0+
 *
 * Plugin Name: Gravity Forms Input data cache
 * Plugin URI: http://blueglass.ee/en/plugins/gravity-forms-input-data-cache/
 * Description: Saves input data to cookie.
 * Version: 0.1
 * Author: BlueGlass
 * Author URI: https://blueglass.ee
 * License: GPL-2.0+
 * Text Domain: gf-input-data-cache
 */


const GF_IDC_VERSION = '0.1';

if(function_exists('gf_do_action')){



	class GFInputDataCache{
		protected $plugin_slug = 'gf-input-data-cache';

		public function __construct() {
			add_action('get_footer', array($this, 'enqueue_scripts'));

			add_action( 'gform_field_advanced_settings', array($this, 'my_advanced_settings'), 10, 2 );
			add_action( 'gform_editor_js', array($this, 'editor_script') );
			add_filter( 'gform_tooltips', array($this, 'add_encryption_tooltips') );
			add_filter( 'gform_field_css_class', array($this, 'custom_class'), 10, 3 );
			add_filter( 'gform_add_field_buttons', array($this, 'add_map_field') );
			add_filter( 'gform_field_type_title', array($this, 'add_field_title'), 10, 2 );
			add_action( 'gform_field_input', array($this, 'set_input_field'), 10, 5 );
			add_action( "gform_editor_js", array($this, 'wps_gform_editor_js') );

			add_action( 'gform_field_standard_settings', array($this, 'my_general_settings'), 10, 2 );

		}

		public function enqueue_scripts(){
			if ( !is_admin() ) {
				wp_enqueue_script('jquery');

				wp_enqueue_script( $this->plugin_slug . '-cookie', plugins_url( 'js/cookie.js', __FILE__ ), array( 'jquery' ), GF_IDC_VERSION );
				wp_enqueue_script( $this->plugin_slug . '-scripts', plugins_url( 'js/scripts.js', __FILE__ ), array( 'jquery' ), GF_IDC_VERSION );
			
			}
		}

		public function my_advanced_settings( $position, $form_id ) {

		    //create settings on position 50 (right after Admin Label)
		    if ( $position == 50 ) {
		        ?>
		        <li class="field_cache_setting field_setting" style="display: list-item;">
		            <label for="field_admin_label">
		                <?php _e("Cache to cookie", "gravityforms"); ?>
		                <?php gform_tooltip("form_field_cache_value") ?>
		            </label>
		            <input type="checkbox" id="field_cache_value" onclick="SetFieldProperty('addToCache', this.checked);" /> remember this field
		        </li>
		        <?php
		    }
		}

		public function editor_script(){
		    ?>
		    <script type='text/javascript'>
		        //adding setting to fields of type "text"
		        fieldSettings["text"] += ", .field_cache_setting";
		        fieldSettings["textarea"] += ", .field_cache_setting";
		        fieldSettings["select"] += ", .field_cache_setting";
		        fieldSettings["phone"] += ", .field_cache_setting";
		        fieldSettings["email"] += ", .field_cache_setting";
		        fieldSettings["multiselect"] += ", .field_cache_setting";
		        fieldSettings["radio"] += ", .field_cache_setting";
		        fieldSettings["checkbox"] += ", .field_cache_setting";
		        fieldSettings["number"] += ", .field_cache_setting";
		        fieldSettings["phone"] += ", .field_cache_setting";
		        fieldSettings["website"] += ", .field_cache_setting";
		        fieldSettings["date"] += ", .field_cache_setting";
		        fieldSettings["address"] += ", .field_cache_setting";
		        fieldSettings["name"] += ", .field_cache_setting";

		        //binding to the load field settings event to initialize the checkbox
		        jQuery(document).bind("gform_load_field_settings", function(event, field, form){
		            jQuery("#field_cache_value").attr("checked", field["addToCache"] == true);
		        });
		    </script>
		    <?php
		}

		public function add_encryption_tooltips( $tooltips ) {
		   $tooltips['form_field_cache_value'] = "<h6>Input cache</h6>Check this box to save user input data to cookie. And next time user visit's site, inut data will be populated automatically.";
		   return $tooltips;
		}

		
		public function add_map_field( $field_groups ) {
		    foreach ( $field_groups as &$group ) {
		        if ( $group['name'] == 'advanced_fields' ) {
		            $group['fields'][] = array(
		                'class'     => 'button',
		                //'data-type' => 'cacheinput',
		                'value'     => __( 'Store in cookies', 'gravityforms' ),
		                'onclick'   => "StartAddField('cacheinput');"
		            );
		            break;
		        }
		    }

		    return $field_groups;
		}

		function add_field_title($field_type){
		  if ($field_type == 'cacheinput') {
		    $title = __('Store in cookies', 'gravityforms');
		  }
		  
		  return $title;
		}



		public function my_general_settings( $position, $form_id ) {
		    //create settings on position 50 (right after Admin Label)
		    if ( $position == 50 ) {
		        ?>
		        <li class="field_cache_title field_setting" style="display: list-item;">
		            <label for="field_admin_label">
		                <?php _e("Name", "gravityforms"); ?>
		            </label>
		            <input type="text" id="field_cache_title" onchange="SetFieldProperty('addToCache_title', this.value);" />
		        </li>
		        <?php
		    }
		}


		public function set_input_field($input, $field, $value, $lead_id, $form_id){
		  	if ( $field["type"] == "cacheinput" ) {
				$input_name = $form_id .'_' . $field["id"];
				$tabindex = GFCommon::get_tabindex();
				$addToCache_title = isset( $field['addToCache_title'] ) ? $field['addToCache_title'] : '';
				return sprintf("<div class='ginput_container'><label for='%s'><input type='checkbox' name='input_%s' id='%s' class='checkbox gform_store_input_data' $tabindex value='%s'> <span>%s</span></label></div>", 'gfstore-data', $field["id"], 'gfstore-data' , esc_html($value), $addToCache_title);
			}

			return $input;
		}

		
		public function wps_gform_editor_js(){
		?>
			<script>
				jQuery(document).ready(function($) {
					//Add all textarea settings to the "TOS" field plus custom "tos_setting"
					// fieldSettings["tos"] = fieldSettings["textarea"] + ", .tos_setting"; // this will show all fields that Paragraph Text field shows plus my custom setting
					// from forms.js; can add custom "tos_setting" as well
					fieldSettings["cacheinput"] = ".label_setting, .default_value_textarea_setting, .css_class_setting, .visibility_setting, .field_cache_title"; //this will show all the fields of the Paragraph Text field minus a couple that I didn’t want to appear.

			        //binding to the load field settings event to initialize the checkbox
			        jQuery(document).bind("gform_load_field_settings", function(event, field, form){
			            jQuery("#field_cache_title").val( field["addToCache_title"] );
			        });
				});
			</script>
		<?php
		}


		function custom_class( $classes, $field, $form ) {
			if ( !is_admin() ) print_r( $field->type . ' ' );
		   	if($field->addToCache == 1){
		        $classes .= ' cache-field_' . $form['id'] . '_' . $field->id;
		    }
		    return $classes;
		}
	}
	
	new GFInputDataCache();



}
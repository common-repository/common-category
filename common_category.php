<?php
/*
Plugin Name: CommonCategory
Plugin URI: http://code.google.com/p/simple-wordpress-plugins/
Description: Make Common Category for all blogs. This plugin is for main_blog only.
Version: 0.1
Author: kousuke.kikuchi
Author URI: http://code.google.com/p/simple-wordpress-plugins/
*/

global $CommonCategory;
$commonCategory = new CommonCategory();
if (! defined('WP_USE_THEMES') && is_site_admin() && is_main_blog()  ) {
    $commonCategory= new CommonCategory();
    add_action('admin_menu',  array($commonCategory, 'add_page'));
}

/* ==================================================
 *   CommonCategory class
   ================================================== */

class CommonCategory{
    private $plugin_dir;
    private $plugin_url;
    private $nonce = -1;

public function getAttr($key) {
    return isset($this->$key) ? $this->$key : NULL;
}

public function __construct() {
    if ( !function_exists('wp_nonce_field') ) {
                $this->nonce = -1;
        } else {
                $this->nonce = 'common-category-config';
        }
    $this->plugin_dir = basename(dirname(__FILE__));
    $this->plugin_url = plugins_url($this->plugin_dir . '/');
    $lang_dir = $this->getAttr('plugin_dir') . ('languages' ? '/' . 'languages' : '');
    load_plugin_textdomain('CommonCategory', false, $lang_dir);
    
}


public function add_page() {
    add_options_page('Common Category', __('Appending Common Category', CommonCategory), 'manage_options', basename(__FILE__), array($this, 'option_page'));
}

public function option_page(){
	global $_POST;
	if (isset($_POST['addcat'])) {
                check_admin_referer($this->nonce); 
		$blogs = $this->get_public_blog_ids();
		$txt = "";
		foreach($blogs as $b){
			switch_to_blog($b);
			$bd = get_blog_details($b, true);
			$id = wp_insert_category($_POST);
			$url = clean_url($bd->siteurl.'/wp-admin/categories.php');
			$txt .= '<p><a target="_blank" href="'.$url.'">'.$url.'</a>(term_id='.$id.')</p>';
			
			restore_current_blog();
		}
?>
		<div class="updated fade">
			<p><strong><?= __('Appended follow blogs', CommonCategory) ?></strong><br />
			<?= $txt ?></p>
			
		</div>
<?php
        }
	include(dirname(__FILE__)."/template-edit.html");
}


private function get_public_blog_ids($ignoreMainBlog = true){
    global $wpdb;
    $r = $wpdb->get_results( $wpdb->prepare(
        "SELECT blog_id FROM $wpdb->blogs WHERE site_id = %d  ORDER BY registered DESC", $wpdb->siteid), ARRAY_A );
    $result = array();
    foreach($r as $rd){
        $id = (int)$rd['blog_id'];
        if( (ignoreMainBlog) && ($id == 1) ){
        }else{
            $result[] = (int)$rd['blog_id'];
        }
    }
    return $result;
}

public function make_nonce_field($action) {
        if ( !function_exists('wp_nonce_field') ) {
                return "<!-- $this->nonce -->";
        } else {
                return wp_nonce_field($action);
        }
}


}

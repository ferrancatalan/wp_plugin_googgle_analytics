<?php
/*
* Plugin Name: 	Easy Google Analytics Tracking Code
* Plugin URI: 	https://www.ferrancatalan.com
* Description: 	Add easily Google analytics tracking code to your website
* Author: 		Ferran Catalan
* Version: 		0.8
* Author URI:	https://www.ferrancatalan.com
* Text Domain:	easy-google-analytics-tracking-code
* Domain Path:	/languages
 */
 
class WP_AddAnalyticsCode{
	
	var $plugin_options_page_title = 'Easy Google Analytics Tracking code';
	var $plugin_options_menue_title = 'Google Analytics Code';
	var $plugin_options_slug = 'add-analytics-code';
	var $admin_slug_settings = 'settings_page';
	var $admin_slug_plugins = 'plugins';
	var $plugin_options_version = '0.8';
	var $plugin_page = 'plugins.php';
	var $options_page = 'options-general.php';
	
		
	function __construct() {
		global $pagenow;
		
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_actions' ) );
		add_action( 'wp_head', array( $this, 'add_google_analytics_code'));	
		
		if('plugins.php' === $pagenow ){
			if(get_option('analytics_data_code','0')==='0' || get_option('analytics_data_code','0') ==='' || get_option('analytics_tracking','0')==='0'|| get_option('analytics_tracking','0')===''){
				add_action('admin_notices', array( $this, 'your_admin_notice'));
			}
		}
		add_action('admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style'));
		add_action('plugins_loaded', array( $this, 'add_analytics_textdomain'));
	}
	

	function add_analytics_textdomain() {
		
		$text_domain	= 'easy-google-analytics-tracking-code';
		$path_languages = basename(dirname(__FILE__)).'/languages/';
		load_plugin_textdomain( $text_domain, FALSE, $path_languages );
	}
	

    function load_custom_wp_admin_style($hook){
		$current_screen = get_current_screen();
		if($current_screen->base === $this->admin_slug_plugins || $current_screen->base === $this->admin_slug_settings.'_'.$this->plugin_options_slug){
			wp_enqueue_style('custom_wp_admin_css', plugins_url('inc/admin-style.css',__FILE__ ));
		}else{
			return;
		}			
    }
	
	function your_admin_notice(){
		$class = 'notice notice-warning';
		
		if(get_option('analytics_data_code','0')=='0' || get_option('analytics_data_code','0') == '' ){
		 $message = __( 'Almost done. Configure your plugin to start tracking your traffic with Google Analytics.',$this->add_google_analytics_code );
		}else{
		 $message = __( 'Google Analytics is not tracking your traffic right now.',$this->add_google_analytics_code );
		}
		
		printf( '<div class="%1$s" style="background-color:#ed750a;">
			<form method="post" action="'.$this->options_page.'?page='.$this->plugin_options_slug.'">
				<div>
					<button class="btn-notice">Go to Settings</button>
					<h4 class="title-notice">%2$s</h4>
				</div>
			</form>
		</div>', esc_attr( $class ), esc_html( $message ) );     
	}
	function admin_menu() {
		if ( function_exists( 'add_options_page' ) AND current_user_can( 'manage_options' ) ) {
			$options_page = add_options_page($this->plugin_options_page_title, $this->plugin_options_menue_title, 'manage_options', $this->plugin_options_slug, array( $this, 'options_page' ));
		}
	}
	
	function plugin_actions($links) {
		$new_links = array();
		$new_links[] = '<a href="'.$this->options_page.'?page='.$this->plugin_options_slug.'">' . __('Settings', 'jquery-add-ganalytics-code') . '</a>';
		return array_merge($new_links, $links);
	}
	
	
	function add_google_analytics_code(){
		$current_user = wp_get_current_user();
		
		if(get_option('analytics_data_code','0')!='0' && get_option('analytics_data_code','0')!= ''){
			$g_admin = get_option('analytics_data_admin_traffic','0');
			$g_editor = get_option('analytics_data_editor_traffic','0');
			$g_404 = get_option('analytics_data_404_traffic','0');
			if($g_admin=='1' &&  is_admin()){
				return;
			}if($g_editor=='1' &&  current_user_can('editor') ){
				return;
			}
			else if($g_404=='1' && is_404()){
				return;
			}else{
				 $tracking_code = get_option('analytics_data_code');
					?>
					<!-- Global site tag (gtag.js) - Google Analytics -->
						<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $tracking_code ?>"></script>
						<script>
						  window.dataLayer = window.dataLayer || [];
						  function gtag(){dataLayer.push(arguments);}
						  gtag('js', new Date());

						  gtag('config', '<?php echo $tracking_code ?>');
						</script>
					<?php
			}
		}		
	}
	
	
	
	function options_page() {
		if(is_admin()){
			if(!empty($_POST['_wpnonce']) && wp_verify_nonce( $_POST['_wpnonce'],$this->plugin_options_slug)){
				if(isset($_POST['action']) && $_POST['action'] === "saveoptions"){
						
						$pattern = '/UA\-([0-9]{8}|[0-9]{9}|[0-9]{10}|[0-9]{11})-\d/';
						
						if(preg_match($pattern,sanitize_text_field($_POST['analytics_data_code']))){
							update_option('analytics_data_code',sanitize_text_field($_POST['analytics_data_code']));
							update_option('analytics_data_admin_traffic',sanitize_text_field($_POST['analytics_data_admin_traffic']));		
							update_option('analytics_data_editor_traffic',sanitize_text_field($_POST['analytics_data_editor_traffic']));		
							update_option('analytics_data_404_traffic',sanitize_text_field($_POST['analytics_data_404_traffic']));		
							update_option('analytics_tracking',sanitize_text_field($_POST['analytics_tracking']));		
							echo '<div class="updated message" style="padding: 10px">Settings updated.</div>';
						}else{
							echo '<div class="error message" style="padding: 10px">Incorrect format UA-XXXXXXXXX-X.</div>';
						}
				}
			} 
		}
    ?>
		
		<div class="wrap">
			<h2><?php echo esc_html($this->plugin_options_page_title); ?></h2>
		</div>
		<div class="postbox-container metabox-holder meta-box-sortables" style="width: 69%">
			<div style="margin:0 5px;">
				<div class="postbox">
					<div class="handlediv"><br></div>
					<div class="inside">
						<div>
						<h2><?php echo esc_html($this->plguin_options_menue_title); ?> <?php echo _e( 'Settings', 'easy-google-analytics-tracking-code' ); ?></h2>
						<form method="post">
							<input type='hidden' name='action' value='saveoptions'  class="regular-text" > 
						<table class="form-table">
								<tbody>
								<tr>
									<th>Enter Analytics code:</th>
									<td>
									<input name="analytics_data_code" id="analytics_data_code" type="text" placeholder="UA-XXXXXXXXX-X" value="<?php echo get_option('analytics_data_code'); ?>" class="regular-text code">
									</td>
								</tr>								
								<tr>
									<th>Options:</th>
									<td>
										<input name="analytics_data_admin_traffic" type="checkbox" id="analytics_data_admin_traffic" value="1" <?php echo checked(get_option( 'analytics_data_admin_traffic' ));  ?>>
										<label for="analytics_data_admin_traffic">Don't track admin user traffic.</label>
									</td>
								</tr>
								<tr>
									<th></th>
									<td>
										<input name="analytics_data_editor_traffic" type="checkbox" id="analytics_data_editor_traffic" value="1" <?php echo checked(get_option( 'analytics_data_editor_traffic' ));  ?>>
										<label for="analytics_data_editor_traffic">Don't track editor user traffic.</label>
									</td>
								</tr>
								<tr>
									<th></th>
									<td>
										<input name="analytics_data_404_traffic" type="checkbox" id="analytics_data_404_traffic" value="1" <?php echo checked(get_option( 'analytics_data_404_traffic' ));  ?>>
										<label for="analytics_data_404_traffic">Don't track 404 page.</label>
									</td>
								</tr>
							</tbody>
							</table>	
							<?php wp_nonce_field($this->plugin_options_slug); ?>
							  <p class="submit"><input name="submit" id="submit" class="button button-primary" value="Guardar cambios" type="submit"></p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="postbox-container side metabox-holder" style="width:29%;">
			<div style="margin:0 5px;">
					<?php 
					if(get_option('analytics_data_code','0')==='0' || get_option('analytics_data_code','0') === ''){?>
						<div class="postbox gerror">
							<h3>Tracking</h3>
							<div class="inside">	
								<p>Almost done! Configure your plugin and add the tracking code of your property to start tracking your traffic with Google Analytics.</p>
					<?php }elseif(get_option('analytics_tracking','0')==='0' || get_option('analytics_tracking','0')===''){ ?>
						<div class="postbox gstop">
							<h3>Tracking</h3>
							<div class="inside">	
								<p>Google Analytics is not tracking your website now. Switch the button if you want to track it.</p>
					<?php }else{ ?>
						<div class="postbox gtracking">
							<h3>Tracking</h3>
							<div class="inside">	
								<p>Google Analytics is tracking your website Switch the button if you want to stop it..</p>
					<?php } ?>
								<label class="switch">
									<script>
									jQuery(document).ready(function(){
										jQuery('.checkbox').click(function(){
											jQuery('.button').trigger('click');
										});
									});
									</script>
									<input type="checkbox"  value="1" name="analytics_tracking" class="checkbox" <?php echo checked(get_option( 'analytics_tracking' )); if(get_option('analytics_data_code','0')=='0' || get_option('analytics_data_code','0') == ''){echo('disabled');} ?>>
									<span class="slider round"></span>
								</label>
							</form>
							</div>
						</div>
			</div>
			<div style="margin:0 5px;">
				<div class="postbox gabout">
					<h3>About</h3>
					<div class="inside">
						<h4><?php echo esc_html($this->plugin_options_page_title); ?> Version <?php echo esc_html($this->plugin_options_version); ?></h4>
						<p>Easy way to add google analytics to every page of your website.</p>
						<ul>
							<li>Google Analytics: <a href="https://analytics.google.com/" target="_blank">more information</a>.</li>
							<li>Developed by: <a href="https://www.ferrancatalan.com/" target="_blank">ferrancatalan</a>.</li>
						</ul>
						
					</div>
				</div>
			</div>
		</div>
		
		<?php
	}	
}
 
$WP_AddAnalyticsCode = new WP_AddAnalyticsCode;
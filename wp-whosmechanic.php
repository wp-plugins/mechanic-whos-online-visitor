<?php
/*
Plugin Name: Mechanic Who's Online Visitor
Plugin URI: http://www.adityasubawa.com/blog/post/62/install-mechanic-whos-online-visitor-wordpress.html
Description: Display Who's Online Visitor on WordPress as a Widgets
Version: 1.3
Author: Aditya Subawa
Author URI: http://www.adityasubawa.com
*/
global $wpdb;
define('WO_TABLE_NAME', $wpdb->prefix . 'mech_whonline');
define('WO_PATH', ABSPATH . 'wp-content/plugins/whosmechanic');
require_once(ABSPATH . 'wp-includes/pluggable.php');

function woadit_install(){
global $wpdb;
if ( $wpdb->get_var('SHOW TABLES LIKE "' . WO_TABLE_NAME . '"') != WO_TABLE_NAME )
{
$sql = "CREATE TABLE IF NOT EXISTS `". WO_TABLE_NAME . "` (";
$sql .= "`id` int(10) NOT NULL auto_increment,";
$sql .= "`ip` varchar(15) NOT NULL default '',";
$sql .= "`timestamp` varchar(15) NOT NULL default '',";
$sql .= "PRIMARY KEY (`id`),";
$sql .= "UNIQUE KEY `id`(`id`)";
$sql .= ") TYPE=MyISAM COMMENT='' AUTO_INCREMENT=1 ;";
$wpdb->query($sql);
 }
}
	 
function woadit_uninstall(){
global $wpdb;
$sql = "DROP TABLE `". WO_TABLE_NAME . "`;";
$wpdb->query($sql);
}
register_activation_hook(__FILE__, 'woadit_install');
register_deactivation_hook(__FILE__, 'woadit_uninstall'); ?>
<?php
class whosmechanic { 

	var $timeout = 600;
	var $count = 0;
	var $error;
	var $i = 0;
	
	function whosmechanic () {
		$this->timestamp = time();
		$this->ip = $this->ipCheck();
		$this->new_user();
		$this->delete_user();
		$this->count_users();
	}
	
	function ipCheck() {
	
			if (getenv('HTTP_CLIENT_IP')) {
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif (getenv('HTTP_X_FORWARDED_FOR')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_X_FORWARDED')) {
			$ip = getenv('HTTP_X_FORWARDED');
		}
		elseif (getenv('HTTP_FORWARDED_FOR')) {
			$ip = getenv('HTTP_FORWARDED_FOR');
		}
		elseif (getenv('HTTP_FORWARDED')) {
			$ip = getenv('HTTP_FORWARDED');
		}
		else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	function new_user() {
		$insert = mysql_query ("INSERT INTO `". WO_TABLE_NAME . "`(timestamp, ip) VALUES ('$this->timestamp', '$this->ip')");
		if (!$insert) {
			$this->error[$this->i] = "Unable to record new visitor\r\n";			
			$this->i ++;
		}
	}
	
	function delete_user() {
		$delete = mysql_query ("DELETE FROM `". WO_TABLE_NAME . "` WHERE timestamp < ($this->timestamp - $this->timeout)");
		if (!$delete) {
			$this->error[$this->i] = "Unable to delete visitors";
			$this->i ++;
		}
	}
	
	function count_users() {
		if (count($this->error) == 0) {
			$count = mysql_num_rows ( mysql_query("SELECT DISTINCT ip FROM `". WO_TABLE_NAME . "`"));
			return $count;
		}
	}
  }

class wp_onlinemechanic extends WP_Widget{
    
    function __construct(){
     $params=array(
            'description' => 'Display online visitor as a widgets', //deskripsi  dari plugin  yang di tampilkan
            'name' => 'Mechanic - Who is Online'  //title dari plugin
        );
        
        parent::__construct('wp_onlinemechanic', '', $params); 
    }
    
    public function form($instance){
	$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
       ?>
<p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
<p><label for="<?php echo $this->get_field_id('author_credit'); ?>"><?php _e('Give credit to plugin author?'); ?><input type="checkbox" class="checkbox" <?php checked( $instance['author_credit'], 'on' ); ?> id="<?php echo $this->get_field_id('author_credit'); ?>" name="<?php echo $this->get_field_name('author_credit'); ?>" /></label></p>
<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=ZMEZEYTRBZP5N&lc=ID&item_name=Aditya%20Subawa&item_number=426267&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" alt="<?_e('Donate')?>" /></a></p>

	   <?php
    }
    
    public function widget($args, $instance){
      extract($args, EXTR_SKIP);
    $authorcredit = isset($instance['author_credit']) ? $instance['author_credit'] : false ; // give plugin author credit
    echo $before_widget;
    $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;;?>
	  <?php
$visitors_online = new whosmechanic();

if (count($visitors_online->error) == 0) {

	if ($visitors_online->count_users() == 1) {
		echo "We have " . $visitors_online->count_users() . " visitor online";
	}
	else {
		echo "We have " . $visitors_online->count_users() . " visitors online";
	}
}
else {
	echo "<b>Users online class errors:</b><br /><ul>\r\n";
	for ($i = 0; $i < count($visitors_online->error); $i ++ ) {
		echo "<li>" . $visitors_online->error[$i] . "</li>\r\n";
	}
	echo "</ul>\r\n";

};
	 if ($authorcredit) { ?>
			<p style="font-size:10px;">
				Plugins by <a href="http://www.adityasubawa.com" title="Bali Web Design">Bali Web Design</a>
			</p>
			<?php }
	echo $after_widget;
  }
}
add_action('widgets_init', 'register_wp_onlinemechanic');
function register_wp_onlinemechanic(){
    register_widget('wp_onlinemechanic');
}
?>
<?php
/*
Plugin Name: MeetupUserActivityWidget
Plugin URI: none
Description: List of meetup user activity in worpress widget
Version: 0.1
Author: Fabrizio Ferraiuolo
Author URI: none

== Changelog ==
* Added category option (July 28, 2009)
= 1.0 =
* First release (August 28, 2009)
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

*/
class MeetupUserActivityWidget extends WP_Widget{

	function MeetupUserActivityWidget(){
		$widget_ops = array('classname' => 'MeetupUserActivityWidget', 'description' => 'User activity widget from Meetup' );
		$this->WP_Widget('MeetupUserActivityWidget', 'MeetupUserActivityWidget', $widget_ops);
	}
   
	function widget($args,$instance){
		extract($args);
		$title = apply_filters('widget_title',$instance['title']);
		$apikey = $instance['apikey'];
		$mulink = $instance['mulink'];
		$muname = $instance['muname'];
		$maxitem = $instance['maxitem'];
		echo $before_widget;        
		if ($title) {
			echo $before_title;
			if ($mulink) echo '<a href="' . $mulink . '">';
			echo $title;
			if ($mulink) echo '</a>';
			echo $after_title;
		}
		if (class_exists('SimplePie')) {
			if (SIMPLEPIE_BUILD >= 20080102221556) { // SimplePie 1.1
				if ( $apikey ) { 
					printUserActivityWidget($apikey, $mulink, $muname, $maxitem);
				} else {
					echo 'No api key specified';
				}
			} else {
				echo 'This plugin requires a newer version of the <a href="http://wordpress.org/extend/plugins/simplepie-core">SimplePie Core</a> plugin to enable important functionality. Please upgrade the plugin to the latest version.';
			}
		} else {
			echo 'This plugin relies on the <a href="http://wordpress.org/extend/plugins/simplepie-core">SimplePie Core</a> plugin to enable important functionality. Please download, install, and activate it, or upgrade the plugin if you\'re not using the latest version.';
		}
		
		echo $after_widget;
		wp_reset_query();
	}
	
	function update($new_instance,$old_instance){
		$instance = $old_instance ;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['apikey'] =  strip_tags($new_instance['apikey']);
		$instance['mulink'] = strip_tags($new_instance['mulink']);
		$instance['muname'] = strip_tags($new_instance['muname']);
		$instance['maxitem'] = strip_tags($new_instance['maxitem']);
		return $instance ;
	}
	
	function form($instance){            
		$instance = wp_parse_args( (array)$instance ,
			array( 'title' => 'Meetup User Activity' ,
				'apikey' => '4471c1742473472166721b5056a' ,
				'mulink' => 'http://www.meetup.com/Terracina5stelle/' ,
				'muname' => 'Terracina a 5 Stelle - Amici di Beppe Grillo' ,
				'maxitem' => 10
			)
		);
		?>
		<p><label for="<? echo $this->get_field_id('title'); ?>"><? _e('Title:'); ?> </label></p>
		<p><input id= "<? echo $this->get_field_id('title'); ?>" name="<? echo $this->get_field_name('title'); ?>" value="<? echo $instance['title']; ?>"/></p>
		<br/>
		
		<p><label for="<? echo $this->get_field_id('apikey'); ?>" ><? _e('Api Key:'); ?> </label></p>
		<p><input id= "<? echo $this->get_field_id('apikey'); ?>" name="<? echo $this->get_field_name('apikey'); ?>" value="<? echo $instance['apikey']; ?>"/></p>
		<br/>
		
		<p><label for="<? echo $this->get_field_id('mulink'); ?>" ><? _e('Meetup page link:'); ?> </label></p>
		<p><input id= "<? echo $this->get_field_id('mulink'); ?>" name="<? echo $this->get_field_name('mulink'); ?>" value="<? echo $instance['mulink']; ?>"/></p>
		<br/>
		
		<p><label for="<? echo $this->get_field_id('muname'); ?>" ><? _e('Meetup name:'); ?> </label></p>
		<p><input id= "<? echo $this->get_field_id('muname'); ?>" name="<? echo $this->get_field_name('muname'); ?>" value="<? echo $instance['muname']; ?>"/></p>
		<br/>
		
		<p><label for="<? echo $this->get_field_id('maxitem'); ?>" ><? _e('Max item viewed:'); ?> </label></p>
		<p><input id= "<? echo $this->get_field_id('maxitem'); ?>" name="<? echo $this->get_field_name('maxitem'); ?>" value="<? echo $instance['maxitem']; ?>"/></p>
		<br/>
		<? 
	}
}

/* Check to see if locations are changed in wp-config */
if ( !defined('WP_CONTENT_URL') ) {
	define('MUW_PLUGINPATH',get_option('siteurl').'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/');
	define('MUW_PLUGINDIR', ABSPATH.'/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/');
} else {
	define('MUW_PLUGINPATH',WP_CONTENT_URL.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
	define('MUW_PLUGINDIR',WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__)).'/');
}

function userActivityScripts(){	
	$muw_widget_path = MUW_PLUGINPATH; 
	?><link rel="stylesheet" type="text/css" href="<?php echo $muw_widget_path; ?>widget.css" /><?php 
} 

/* Add scripts to header */
add_action('wp_head', 'userActivityScripts');

/* Load the widget */
add_action( 'widgets_init', create_function('', 'return register_widget("MeetupUserActivityWidget");') );

function printUserActivityWidget($apikey, $mulink, $muname, $maxitem) {
	
	$muw_widget_path = MUW_PLUGINPATH; 
	$link = "https://api.meetup.com/activity.rss?key=" . $apikey;
	
	$feed = new SimplePie();
	$feed->set_feed_url(array($link));
	$intMaxItem = intval($maxitem);
	if ($intMaxItem > 0) {
		$feed->set_item_limit($intMaxItem+1);
	}
	$success = $feed->init();
	
	if ($success) {
		$feed->handle_content_type();
		?><div style="mu_content"><?php
		foreach ($feed->get_items() as $item) {
			$titolo = str_replace($muname . ": ", "", $item->get_title());
			
			if ($titolo != 'Nuovo RSVP') {
				?><div class="mu_element"><?php
				
				//$favicon = $feed->get_favicon();
				if (!$favicon = $feed->get_favicon()) {
					$favicon = $muw_widget_path . 'favicon.ico';
				}
				?><h4><img src="<?php echo $favicon; ?>" alt="Favicon" class="favicon" /><?php
				if ($item->get_permalink()) echo '<a href="' . $item->get_permalink() . '">'; 
					echo $titolo;
				if ($item->get_permalink()) echo '</a>'; 
				?><br/><span class="mu_date"><?php echo $item->get_date('j M Y, g:i a'); ?></span></h4><?php
				
				echo $item->get_content();
				
				?></div><?php
			}
		}
		?></div><?php
		if ($mulink) {
			?>
			<div>
				<a class="footer_lnk" href="<?php echo $mulink; ?>">Meetup: <?php echo $muname; ?></a>
			</div>
			<?php
		}		
	} else {
		echo 'Error loading widget.' . $success;
	}
}
?>
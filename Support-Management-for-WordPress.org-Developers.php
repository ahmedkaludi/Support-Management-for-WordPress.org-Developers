<?php
/**
* Plugin Name: Support Management for-WordPress org Developers
* Plugin URI: https://magazine3.company
* Description: The main motive is to give flexiblity to wordpress developers to manage wp.org plugins support in one place.
* Version: 1.0
* Author: Developer Mustaq
* Author URI: https://magazine3.company
**/


// function that runs when shortcode is called
function mg3_wporgforum_active_plugins_function() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'org_active_forums';

	if(isset($_GET['deleted_ticket']) && !empty($_GET['deleted_ticket'])) {
	  $wpdb->update( $table_name, array( 'deleted_ticket'=> 1),array('id'=> intval($_GET['deleted_ticket'])));
	  wp_redirect(wp_get_referer());
	}


	if(isset($_GET['m3_forum_action']) && !empty($_GET['m3_forum_action']) ){
		
		require_once 'simplehtmldom/simple_html_dom.php';
		$wporgurl = 'https://wordpress.org/support/plugin/';

		$allBrandScrapUrl = [
			['url' => 'schema-and-structured-data-for-wp/active', 'brand'=> 'SASWP' ],
			['url' => 'accelerated-mobile-pages/active', 'brand'=> 'AMPFORWP' ],
			['url' => 'pwa-for-wp/active', 'brand'=> 'PWAFORWP' ],
			['url' => 'quick-adsense-reloaded/active', 'brand'=> 'WPQuads' ],
			['url' => 'super-progressive-web-apps/active', 'brand'=> 'SUPERPWA'],
			['url' => 'gn-publisher/active', 'brand'=> 'GNPublisher'],
			['url' => 'easy-table-of-contents/active', 'brand'=> 'TOCWP'],
			['url' => 'wp-database-backup/active', 'brand'=> 'Backupforwp'],
			['url' => 'fastspring-integration-for-wp/active', 'brand'=> 'FSINTEGRATIONWP'],
			['url' => 'viator-integration-for-wp/active', 'brand'=> 'ViatorIntegration'],
			['url' => 'core-web-vitals-pagespeed-booster/active', 'brand'=> 'Core Web Vitals'],
			['url' => 'ads-for-wp/active', 'brand'=> 'Ads for WP'],
			['url' => 'amp-blocks/active', 'brand'=> 'AMP Blocks'],
			['url' => 'push-notification/active', 'brand'=> 'Push Notification'],
			['url' => 'critical-css-for-wp/active', 'brand'=> 'Critical Css'],
			['url' => 'super-related-posts/active', 'brand'=> 'Super Related Posts'],
			['url' => 'web-stories-enhancer/active', 'brand'=> 'Web Stories Enhancer'],
			['url' => 'amp-enhancer/active', 'brand'=> 'AMP Enhancer']
			
		];

		$singlebrand = [];
		$allBrandArray = [];
		foreach ($allBrandScrapUrl as $url) {	
			$html = file_get_html($wporgurl.$url['url']);
			foreach($html->find('.topic.type-topic') as $element) {
			     $title =  $element->find('.bbp-topic-permalink',0)->plaintext;
			    $title_href =  $element->find('a',0)->href;
			   	$starte_by = $element->find('.bbp-topic-meta a',0)->plaintext;
			   	$voice_count = $element->find('.bbp-topic-voice-count',0)->plaintext;
			   	$replies = $element->find('.bbp-topic-reply-count',0)->plaintext;
			   	$date = $element->find('.bbp-topic-freshness a',0)->plaintext;
			   	$timestamp = $element->find('.bbp-topic-freshness a',0)->title;
			   	$at = ['at', 'am'];
			   	$radd = ['', ''];
			   	$wp_org_ticket_time = str_replace($at, $radd , $timestamp);
			   	$timestamp = date('Y-m-d H:i:s', strtotime($wp_org_ticket_time));
			   	$lastreplie = $element->find('.bbp-topic-freshness .bbp-topic-meta a',0)->plaintext;
			   	$reolvedTag = 0;
			   	if(isset($element->find('.bbp-topic-permalink .resolved',0)->title) && $element->find('.bbp-topic-permalink .resolved',0)->title != null){		   		
			   		$reolvedTag = 1;
			   	}

			   	$brand = $url['brand'];
				$table_name = $wpdb->prefix . 'org_active_forums';
				$datum = $wpdb->get_results("SELECT * FROM $table_name WHERE title= '".trim($title)."'");
				if($wpdb->num_rows > 0) {	
					// print_r( array( 'voices'=> $voice_count,'replies' => $replies, 'wp_time_ago' => $date, 'started_by' => $starte_by,'resolved' => $reolvedTag,'wp_org_ticket_time' => $timestamp));
					$deleted_ticket = $datum[0]->deleted_ticket;
					if($timestamp > $datum[0]->wp_org_ticket_time){
							$deleted_ticket = 0;
					}
					$wpdb->update( $table_name, 
						array( 
							'voices'=> intval($voice_count),
							'replies' => intval($replies), 
							'wp_time_ago' => $date, 
							'started_by' => trim($starte_by), 
							'deleted_ticket' => intval($deleted_ticket),
							'last_reply_by' => trim($lastreplie),
							'resolved' =>  $reolvedTag,
							'wp_org_ticket_time' => $timestamp
						),
						array(
							'title'=>trim($datum[0]->title)
						)
					);

				}else{
					$saved = $wpdb->insert( 
						$table_name, 
						array( 
							'title' => trim($title), 
							'brand' => $brand, 
							'voices' => intval($voice_count), 
							'replies' => intval($replies),  
							'topic_url' => $title_href,
							'started_by' => $starte_by,
							'deleted_ticket' => 0,
							'wp_time_ago' => $date,
							'last_reply_by' => $lastreplie,
							'resolved' => $reolvedTag,
							'wp_org_ticket_time' => $timestamp,
							'wp_plugin_slug' =>$wporgurl.$url['url'], 
						)
					);
				} 
			}
		}
	wp_redirect(wp_get_referer());

	}

	$count = 0;
	$data = '';
	$tableData='';
	$redirect_uri = add_query_arg ('m3_forum_action', 'refresh_list', get_permalink ()) ;
	$datum = $wpdb->get_results("SELECT * FROM $table_name Where deleted_ticket=0 ORDER BY wp_org_ticket_time DESC");
		if(!empty($datum)){		
		foreach ($datum as $values) {	

			$allBrands = ['Magazine3', 'Ahmed Kaludi', 'WPQuads Support', 'SuperPWA', 'GNPublisher', 'Backup For WP', 'Sanjeev Kumar','integratordev', 'AMP Enhancer'];

			if(in_array(trim($values->last_reply_by ), $allBrands)){
				continue;	
			}
			$resolvedTag = ($values->resolved == 1) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';			
			
			$tableData .= 
				'<tr >
					<td><a class="topic-title" href="'.$values->topic_url.'" target="_blank">'.$resolvedTag.$values->title.'</a><br><span class="auther-link">started by : <a target="_blank" href="https://wordpress.org/support/users/'.$values->started_by.'">'.$values->started_by.'</></span></td>
					<td style="list-style-type:none;"><a target="_blank" href="'.$values->wp_plugin_slug.'">'.$values->brand.'</a></td>
					<td style="list-style-type:none;">'.$values->voices.'</td>
					<td style="list-style-type:none;">'.$values->replies.'</td>
					<td><a class="topic-title" href="'.$values->topic_url.'" target="_blank" style="font-size: 18px;">'.$values->wp_time_ago.'</a><br><span class="auther-link"><a  target="_blank" href="https://wordpress.org/support/users/'.$values->last_reply_by.'">'.$values->last_reply_by.'</td>
					<td><a class="delete-ticket" onclick="deleteTicketOrg('.$values->id.')" href="javascript:void(0);" data-rowid="'.$values->id.'" ><i class="fa fa-trash" aria-hidden="true"></i></a></td>
				</tr>'; 
				$count++;
		}
		
	}else{
		$tableData .= "<tr><td colspan='6'><p style='text-align:center'>No Active tickets found...</p></td></tr>";
	}

	$data .= '<!DOCTYPE html>
	<html>
	<head>
	<META class="m3-feed-refresh-meta" http-equiv="REFRESH" content="120; url='.get_permalink ().'">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>

	<style>
	table#mg3_wp_forum a.topic-title .fa.fa-check {
	    color: #64b450;
	}
	table#mg3_wp_forum {
		border: 1px solid #eee;
		font-family: arial, sans-serif;
		border-collapse: collapse;
		width: 100%;
	}
	
	#mg3_wp_forum td, th {	 
	  text-align: left;
	  padding: 8px;
	}
	#mg3_wp_forum a.bbp-topic-permalink, #mg3_wp_forum .bbp-topic-voice-count, #mg3_wp_forum .bbp-topic-reply-count {
    	font-size: 16px;
	}
	#mg3_wp_fforum table,td, th{
		border: none !important;
	}
	#mg3_wp_forum tr{
	border-top: 1px solid #eee;
    overflow: hidden;
    padding: 8px;
	}
	#mg3_wp_forum tr:nth-child(1) {
	    background: #0073aa;
	    color: #ffff;
	}
	#mg3_wp_forum tr:nth-child(even) {
	  background-color: #fbfbfb;
	}
	#mg3_wp_forum a{
	    text-decoration: none;
	    font-size: 15px;
	    color: #0073aa
	}
	#mg3_wp_forum th{
		font-size: 17px;
	}
	#mg3_wp_forum .auther-link{
		font-size: 11px;
	}
	.button-forum-refresh {
		background-color: #0073aa;
    color: #fff;
    border-radius: 3px;
    padding: 7px 14px;
    font-size: 16px;
    border: #0073aa;
    text-decoration: none;
	}
	.ref-feed-cog {
	    font-size: 25px;
	    padding: 0px 5px;
	}
	</style>
	</head>
	<body>
	
	<table id="mg3_wp_forum"style="max-width:100%">
	<p style="text-align:right;    margin-right: 0 !important;"><a href="'.$redirect_uri.'" class="button-forum-refresh">Fetch Feed</a><a href="javascript:void(0);" class="m3_ticket_autoreload"><i class="ref-feed-cog fa fa-cog" aria-hidden="true"></i></a></p>
	<p class="autoload-input-field-p" style="text-align:right;    margin-right: 0 !important; display:none">Feed Refresh Seconds: <input class="autoload-input-field" value="" type="text" placeholder="Feed Refresh Seconds"><button class="auto-refresh-time">Save</button></p>
	<tr>
		<th>Topic ('.$count.')</th>		
		<th>Plugin</th>		
		<th>voice</th>
		<th>Replies</th>
		<th>Last Post</th>
		<th>Action</th>
	</tr>';		
	$data .= $tableData;

	
	$data .= '</tbody>
  			</table>
  			<script>
				function deleteTicketOrg(id){
				 	if (confirm("Are you sure you want to remove this ticket? (Yes/No)")) {
				 		window.location.href = "wp-active-forums/?deleted_ticket="+id;
				 		return true
				    }
				    return false;					 		
				}
				jQuery(document).ready(function(){
					var defaultRefreshTime = 120;
					var cookieTimeformeta = getCookie("m3_wporg_feed_refresh_time");
					var refreshMeta  = jQuery(".m3-feed-refresh-meta").attr("content");
					const myArray = refreshMeta.split(";");
					var autorefreshDefaultTime = myArray[0];
					jQuery(".autoload-input-field").val(cookieTimeformeta);

					jQuery(".m3-feed-refresh-meta").attr("content", cookieTimeformeta+"; url="+window.location.origin+"/wp-active-forums");

					jQuery(".m3_ticket_autoreload").click(function(){
						
						jQuery(".autoload-input-field-p").toggle();
					});

					jQuery("button.auto-refresh-time").click(function(){
						var mannuVal = jQuery(".autoload-input-field").val();
						if(mannuVal== "" || mannuVal== 0){
							mannuVal = defaultRefreshTime;
						}
						jQuery(".autoload-input-field-p").hide();

						jQuery(".m3-feed-refresh-meta").attr("content", mannuVal+"; url="+window.location.origin+"/wp-active-forums")
						setCookie("m3_wporg_feed_refresh_time", mannuVal, 30);	

					});
				});

				function setCookie(cname, cvalue, exdays) {
				  const d = new Date();
				  d.setTime(d.getTime() + (exdays*24*60*60*1000));
				  let expires = "expires="+ d.toUTCString();
				  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
				}
				function getCookie(cname) {
				  let name = cname + "=";
				  let ca = document.cookie.split(";");
				  for(let i = 0; i < ca.length; i++) {
				    let c = ca[i];
				    while (c.charAt(0) == " ") {
				      c = c.substring(1);
				    }
				    if (c.indexOf(name) == 0) {
				      return c.substring(name.length, c.length);
				    }
				  }
				  return "";
				}
				
  			</script>
  			</html>';
	return  $data;
}
add_shortcode('wp_org_plugins_active_forum','mg3_wporgforum_active_plugins_function');



function my_cron_schedules($schedules){
    if(!isset($schedules["30min"])){
        $schedules["30min"] = array(
            'interval' => 30*60,
            'display' => __('Once every 30 minutes'));
    }
    return $schedules;
}
add_filter('cron_schedules','my_cron_schedules');

if (!wp_next_scheduled('my_task_hook')) {
	wp_schedule_event( time(), '30min', 'my_task_hook' );
}
add_action ( 'my_task_hook', 'my_task_function' );

function my_task_function() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'org_active_forums';

		require_once 'simplehtmldom/simple_html_dom.php';
		$wporgurl = 'https://wordpress.org/support/plugin/';

		$allBrandScrapUrl = [
			['url' => 'schema-and-structured-data-for-wp/active', 'brand'=> 'SASWP' ],
			['url' => 'accelerated-mobile-pages/active', 'brand'=> 'AMPFORWP' ],
			['url' => 'pwa-for-wp/active', 'brand'=> 'PWAFORWP' ],
			['url' => 'quick-adsense-reloaded/active', 'brand'=> 'WPQuads' ],
			['url' => 'super-progressive-web-apps/active', 'brand'=> 'SUPERPWA'],
			['url' => 'gn-publisher/active', 'brand'=> 'GNPublisher'],
			['url' => 'easy-table-of-contents/active', 'brand'=> 'TOCWP'],
			['url' => 'wp-database-backup/active', 'brand'=> 'Backupforwp'],
			['url' => 'fastspring-integration-for-wp/active', 'brand'=> 'FSINTEGRATIONWP'],
			['url' => 'viator-integration-for-wp/active', 'brand'=> 'ViatorIntegration'],
			['url' => 'core-web-vitals-pagespeed-booster/active', 'brand'=> 'Core Web Vitals'],
			['url' => 'ads-for-wp/active', 'brand'=> 'Ads for WP'],
			['url' => 'amp-blocks/active', 'brand'=> 'AMP Blocks'],
			['url' => 'push-notification/active', 'brand'=> 'Push Notification'],
			['url' => 'critical-css-for-wp/active', 'brand'=> 'Critical Css'],
			['url' => 'super-related-posts/active', 'brand'=> 'Super Related Posts'],
			['url' => 'web-stories-enhancer/active', 'brand'=> 'Web Stories Enhancer'],
			['url' => 'amp-enhancer/active', 'brand'=> 'AMP Enhancer']
			
		];

		$singlebrand = [];
		$allBrandArray = [];
		foreach ($allBrandScrapUrl as $url) {	
			$html = file_get_html($wporgurl.$url['url']);
			foreach($html->find('.topic.type-topic') as $element) {
			     $title =  $element->find('.bbp-topic-permalink',0)->plaintext;
			    $title_href =  $element->find('a',0)->href;
			   	$starte_by = $element->find('.bbp-topic-meta a',0)->plaintext;
			   	$voice_count = $element->find('.bbp-topic-voice-count',0)->plaintext;
			   	$replies = $element->find('.bbp-topic-reply-count',0)->plaintext;
			   	$date = $element->find('.bbp-topic-freshness a',0)->plaintext;
			   	$timestamp = $element->find('.bbp-topic-freshness a',0)->title;
			   	$at = ['at', 'am'];
			   	$radd = ['', ''];
			   	$wp_org_ticket_time = str_replace($at, $radd , $timestamp);
			   	$timestamp = date('Y-m-d H:i:s', strtotime($wp_org_ticket_time));
			   	$lastreplie = $element->find('.bbp-topic-freshness .bbp-topic-meta a',0)->plaintext;
			   	$reolvedTag = 0;
			   	if(isset($element->find('.bbp-topic-permalink .resolved',0)->title) && $element->find('.bbp-topic-permalink .resolved',0)->title != null){		   		
			   		$reolvedTag = 1;
			   	}
			   	$brand = $url['brand'];
				$table_name = $wpdb->prefix . 'org_active_forums';
				$datum = $wpdb->get_results("SELECT * FROM $table_name WHERE title= '".trim($title)."'");
				
				if($wpdb->num_rows > 0) {	
					$wpdb->update( $table_name, 
						array( 
							'voices'=> intval($voice_count),
							'replies' => intval($replies), 
							'wp_time_ago' => $date, 
							'started_by' => $starte_by, 
							'last_reply_by' => $lastreplie,
							'resolved' =>  $reolvedTag,
							'wp_org_ticket_time' => $timestamp
						),
						array(
							'title'=>trim($datum[0]->title)
						)
					);

				}else{
					$saved = $wpdb->insert( 
						$table_name, 
						array( 
							'title' => trim($title), 
							'brand' => $brand, 
							'voices' => intval($voice_count), 
							'replies' => intval($replies),  
							'topic_url' => $title_href,
							'started_by' => $starte_by,
							'wp_time_ago' => $date,
							'deleted_ticket' => 0,
							'last_reply_by' => $lastreplie,
							'resolved' => $reolvedTag,
							'wp_org_ticket_time' => $timestamp,
							'wp_plugin_slug' =>$wporgurl.$url['url'], 
						)
					);
				}
			}
		}
}

<?php
/**
* Plugin Name: Support Management for-WordPress org Developers
* Plugin URI: https://magazine3.company
* Description: The main motive is to give flexiblity to wordpress developers to manage wp.org plugins support in one place.
* Version: 1.0
* Author: Developer Mustaq
* Author URI: https://magazine3.company
**/
add_filter( 'page_template', 'wpa3396_page_template' );
function wpa3396_page_template( $page_template )
{
    if ( is_page( 'my-custom-page-slug' ) ) {
        $page_template = dirname( __FILE__ ) . '/forum-table-shortcode.php';
    }
    return $page_template;
}

// function that runs when shortcode is called
function wpb_org_active_plugins_function() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'org_active_forums';

	if(isset($_GET['deleted_ticket']) && !empty($_GET['deleted_ticket'])) {
	  $wpdb->update( $table_name, array( 'deleted_ticket'=> 1),array('id'=> $_GET['deleted_ticket']));
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
			['url' => 'web-stories-enhancer/active', 'brand'=> 'Web Stories Enhancer']
			
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
				$datum = $wpdb->get_results("SELECT * FROM $table_name WHERE title= '".$title."'");
				
				if($wpdb->num_rows > 0) {	
					$wpdb->update( $table_name, array( 'voices'=> $voice_count,'replies' => $replies, 'wp_time_ago' => $date, 'started_by' => $starte_by,'resolved' => $reolvedTag,'wp_org_ticket_time' => $timestamp),array('title'=>$title));

				}else{
					$saved = $wpdb->insert( 
						$table_name, 
						array( 
							'title' => $title, 
							'brand' => $brand, 
							'voices' => $voice_count, 
							'replies' => $replies,  
							'topic_url' => $title_href,
							'started_by' => $starte_by,
							'wp_time_ago' => $date,
							'last_reply_by' => $lastreplie,
							'resolved' => $reolvedTag,
							'wp_org_ticket_time' => $timestamp,
						)
					);
				} 
			}
		}
	}
	$data = '';
	$redirect_uri = add_query_arg ('m3_forum_action', 'refresh_list', get_permalink ()) ;
	$datum = $wpdb->get_results("SELECT * FROM $table_name Where deleted_ticket=0 ORDER BY wp_org_ticket_time DESC");
		if(!empty($datum)){
		$count = 0;
		foreach ($datum as $values) {	
			$allBrands = ['Magazine3', 'Ahmed Kaludi', 'WPQuads Support', 'SuperPWA', 'GNPublisher', 'Backup For WP', 'Sanjeev Kumar','integratordev'];
				$delete_ticket = add_query_arg ('deleted_ticket', $values->id, get_permalink ()) ;

			if(in_array(trim($values->last_reply_by ), $allBrands)){
				continue;	
			}
			$resolvedTag = ($values->resolved == 1) ? '<i class="fa fa-check" aria-hidden="true"></i>' : '';			
			$tableData .= 
				'<tr >
					<td><a class="topic-title" href="'.$values->topic_url.'" target="_blank">'.$resolvedTag.$values->title.'</a><br><span class="auther-link">started by : <a target="_blank" href="https://wordpress.org/support/users/'.$values->started_by.'">'.$values->started_by.'</></span></td>
					<td style="list-style-type:none;">'.$values->brand.'</td>
					<td style="list-style-type:none;">'.$values->voices.'</td>
					<td style="list-style-type:none;">'.$values->replies.'</td>
					<td><a class="topic-title" href="'.$values->topic_url.'" target="_blank" style="font-size: 18px;">'.$values->wp_time_ago.'</a><br><span class="auther-link"><a  target="_blank" href="https://wordpress.org/support/users/'.$values->last_reply_by.'">'.$values->last_reply_by.'</td>
					<td><a class="delete-ticket" onclick="deleteTicketOrg('.$values->id.')" href="javascript:void(0);" data-rowid="'.$values->id.'" ><i class="fa fa-trash" aria-hidden="true"></i></a></td>
				</tr>'; 
				$count++;
		}
		
	}else{
		$tableData .= "<tr><<td colspan='5'><p style='text-align:center'>No Active tickets found...</p></td></tr>";
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
	<p class="autoload-input-field-p" style="text-align:right;    margin-right: 0 !important; display:none">Feed Refresh Time: <input class="autoload-input-field" value="" type="text"><button class="auto-refresh-time">Save</button></p>
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
					var defaultRefreshTime = 2;
					var cookieTimeformeta = getCookie("m3_wporg_feed_refresh_time");
					var refreshMeta  = jQuery(".m3-feed-refresh-meta").attr("content");
					const myArray = refreshMeta.split(";");
					var autorefreshDefaultTime = myArray[0];
					jQuery(".autoload-input-field").val(cookieTimeformeta/60);

					jQuery(".m3-feed-refresh-meta").attr("content", cookieTimeformeta+"; url="+window.location.origin+"/wp-active-forums");

					jQuery(".m3_ticket_autoreload").click(function(){
						
						jQuery(".autoload-input-field-p").toggle();
					});

					jQuery("button.auto-refresh-time").click(function(){
						var mannuVal = jQuery(".autoload-input-field").val();
						if(mannuVal== ""){
							mannuVal = defaultRefreshTime;
						}
						jQuery(".m3-feed-refresh-meta").attr("content", mannuVal*60+"; url="+window.location.origin+"/wp-active-forums")
						setCookie("m3_wporg_feed_refresh_time", mannuVal*60, 30);						
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
add_shortcode('wpb_org_active_plugins','wpb_org_active_plugins_function');

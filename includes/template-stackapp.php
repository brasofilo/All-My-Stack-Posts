<?php
/**
 * Template Name: Stack Q&A's
 *
 * Used by the plugin All Your Stack Posts
 * Version 1.3
 */

# Get plugin utilities and properties
$plugin = B5F_SE_MyQA::get_instance();

# Get page meta data
extract( $plugin->frontend->get_post_meta() );

# StackPHP
require_once $plugin->plugin_path.'includes/config.php';

#Zebra Pagination
require_once $plugin->plugin_path.'includes/Zebra_Pagination.php';
$pagination_zebra = new Zebra_Pagination();
$pagination_zebra->navigation_position(
		isset($_GET['navigation_position']) && in_array($_GET['navigation_position'], array('left', 'right')) 
		? $_GET['navigation_position'] : 'outside'
);

// Retrieve all Stack Exchange sites across all pages.
$response = API::Sites();
$sites = array();
while( $site = $response->Fetch(TRUE) )
{
	$temp = $site->Data();
	$sites[$temp['api_site_parameter']] = $temp;
}

# Selected properties
$site_info = array(
	'name' => $sites[$se_site]['name'],
	'link' => $sites[$se_site]['site_url'],
	'desc' => $sites[$se_site]['audience']
);
$css = $plugin->plugin_url . 'css/style.css';
$css_print = $plugin->plugin_url . 'css/print.css';

# Query site and user
$user = API::Site($se_site)->Users($user_id);
$user_data = $user->Exec()->Fetch();

# Add some items to the next queries
$filter = new Filter();

# Paged results
$current_page = isset($_GET['se_paged']) ? $_GET['se_paged'] : 1;

# QUERY USER QUESTIONS
# # http://api.stackexchange.com/docs/questions-on-users#order=desc&sort=activity&ids=73070&filter=!FrfIAirj9mEiCDnga4.6rwox2c&site=stackoverflow&run=true
if( 'questions' == $q_or_a )
{
	$showing_type = 'Questions';
	$filter->SetIncludeItems(array('answer.title', 'answer.link', 'answer.body'));
	if( 'asc' == $sort_order )
		$request = $user->Questions()->SortByCreation()->Ascending()->Filter('!FrfIAirj9mEiCDnga4.6rwox2c')->Exec()->Page($current_page)->Pagesize($per_page);
	else
		$request = $user->Questions()->SortByCreation()->Descending()->Filter('!FrfIAirj9mEiCDnga4.6rwox2c')->Exec()->Page($current_page)->Pagesize($per_page);
}
# QUERY USER ANSWERS
# # http://api.stackexchange.com/docs/answers-on-users#order=desc&sort=activity&ids=402322&filter=!9j_cPvogJ&site=stackoverflow&run=true
else
{
	$showing_type = 'Answers';
	if( 'asc' == $sort_order )
		$request = $user->Answers()->SortByCreation()->Ascending()->Filter('!--btTJsIW3F3')->Exec()->Page($current_page)->Pagesize($per_page);
	else
		$request = $user->Answers()->SortByCreation()->Descending()->Filter('!--btTJsIW3F3')->Exec()->Page($current_page)->Pagesize($per_page);
}	
# END QUERY

if( !$request->Fetch(false) )
	wp_die(
        'Could not retrieve any data. Please, check the User ID and Site combination.', 
        'Stack Error',  
        array( 
            'response' => 500, 
            'back_link' => true 
        )
    );  

?><!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title><?php 
	printf(
			"%s | %s | %s's %s",
			$site_info['name'],
			$site_info['desc'],
			$user_data['display_name'],
			$showing_type
	); ?></title>
  <link rel='stylesheet' type='text/css' href='<?php echo $css; ?>' />
  <link rel='stylesheet' type='text/css' media="print" href='<?php echo $css_print; ?>' />
</head>
<body>
<?php 
	# Pagination
	$tot_pages = $request->Total();
	$pagination = ceil( $tot_pages / $per_page );
    $pagination_zebra->records($tot_pages);
    $pagination_zebra->records_per_page($per_page);
	$pagination_zebra->variable_name('se_paged');
	$pagination_zebra->labels('&nbsp;','&nbsp;');
	$pagination_zebra->selectable_pages('15');
	$pagination_zebra->padding(false);
		
	# Ascending counter
	$count = 1 + ( ($current_page-1) * $per_page );
	$start_post = $count;
	$end_post = ( $current_page == $pagination ) 
			? $tot_pages : intval($count+$per_page-1);
	
	# Descending counter
	$revert_count = $tot_pages - ( ($current_page-1) * $per_page );
	$revert_start_post = $revert_count;
	$revert_end_post = ( $current_page == $pagination ) 
		? '1' : intval($revert_count-$per_page+1);

	# Counter
	$count_start_order = ( 'asc' == $sort_order ) ? $start_post : $revert_start_post;
	$count_end_order = ( 'asc' == $sort_order ) ? $end_post : $revert_end_post;
	
	# User Profile
	echo $plugin->frontend->get_profile( $user_data, $showing_type, $site_info, $tot_pages );

	# LOOP ANSWERS
	if( 'answers' == $q_or_a )
	{
		while( $answer = $request->Fetch(FALSE) )
		{ 
			$print_count = ( 'asc' == $sort_order ) ? $count : $revert_count;
			# Query Question
			$q =  API::Site($se_site)->Questions($answer['question_id']);
			$qq = $q->Filter('!-.dP0*IiKY0d')->Exec()->Fetch(FALSE);
			
			# Prepare HTML
			$question_body = $plugin->frontend->get_their_question( $qq, $site_info['link'], $referrer );
			$answer_body = $plugin->frontend->get_my_answer( $answer, $site_info['link'], $referrer );
		
			# Output
			echo <<<HTML
			<div class="stacktack stacktack-container" data-site="stackoverflow" style="width: auto;">
				<div class="branding">$print_count</div>
				$question_body
				$answer_body
			</div>
HTML;
			$count++;
			$revert_count--;
		}
	}
	# LOOP QUESTIONS
	else
	{
		while( $question = $request->Fetch(FALSE) )
		{ 
			$print_count = ( 'asc' == $sort_order ) ? $count : $revert_count;
			$plugin->frontend->get_my_question( $question, $print_count, $site_info['link'], $referrer );

			# Output Answers divs
			if( !empty( $question['answers'] ) )
				foreach( $question['answers'] as $qanswer )
					$plugin->frontend->get_their_answers( $qanswer );
			else
				echo '<i>no answers</i>';
			
			# Close Question div
			echo '</div>';
			$count++;
			$revert_count--;
		}
	}
	echo '<sub class="show-type-total"><b>Showing '. $showing_type.':</b> ' . $count_start_order . ' to ' . $count_end_order . '</sub>';
	echo '<div class="no-print">';
	$pagination_zebra->render();
	echo '</div>';
?>
</body>
</html>
<?php
/**
 * Template Name: Stack Q&A's
 *
 * The template used  to demonstrate how to include 
 * 
 * @package Page Template Example
 * @since 	0.1
 * @version	0.1
 */

# Get plugin utilities and properties
$plugin = B5F_SE_MyQA::get_instance();
$sites = $plugin->metabox->b5f_get_se_sites();

# Get page meta data
global $post;
$site = get_post_meta( $post->ID, 'se_site', true );
$user_id = get_post_meta( $post->ID, 'se_user_id', true );
$disable_cache = get_post_meta( $post->ID, 'se_cached', true );
$per_page = get_post_meta( $post->ID, 'se_per_page', true );
$q_or_a = get_post_meta( $post->ID, 'se_post_type', true );

# StackPHP
require_once $plugin->plugin_path.'includes/config.php';

# Selected properties
$site_name = $sites[$site][0];
$site_link = $sites[$site][1];
$site_desc = $sites[$site][2];
$css = $plugin->plugin_url . 'css/style.css';
$css_print = $plugin->plugin_url . 'css/print.css';

# Query site and user
$user = API::Site($site)->Users($user_id);
$user_data = $user->Exec()->Fetch();

# Add some items to the next queries
$filter = new Filter();
//$filter->SetExcludeItems(array('answer.owner'));

# Paged results
$current_page = isset($_GET['se_paged']) ? $_GET['se_paged'] : 1;

# Query user Answers
if( 'questions' == $q_or_a )
{
	$showing_type = 'Questions';
	$filter->SetIncludeItems(array('answer.title', 'answer.link', 'answer.body'));
	$request = $user->Questions()->SortByCreation()->Ascending()->Filter('!gfG0_rPCgOGeBliTwxTD1pl6ZzcYbMMx2tk')->Exec()->Page($current_page)->Pagesize($per_page);
}
else
{
	$showing_type = 'Answers';
	$request = $user->Answers()->SortByCreation()->Ascending()->Filter($filter->GetID())->Exec()->Page($current_page)->Pagesize($per_page);
}	
	//while($answer = $request->Fetch(FALSE))
	//	var_dump($answer);
	//die();
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Stack.PHP - User's Questions</title>
  <link rel='stylesheet' type='text/css' href='<?php echo $css; ?>' />
  <link rel='stylesheet' type='text/css' media="print" href='<?php echo $css_print; ?>' />
</head>
<body>
<div class='user-profile'>
	<div class='gravatar'>
		<img src='<?php echo $user_data['profile_image']; ?>&s=64' />
		</div>
		<?php echo '<strong>'.$user_data['display_name'].'</strong>'; ?><br />
		<?php echo '<b><a href="' . $site_link . '" title="'.$site_desc.'">' . $site_name . '</a></b><br />'; ?>
		<b>Reputation:</b> <kbd><?php echo $user_data['reputation']; ?></kbd><br />
	</div>
	  
  <?php 
	# Pagination
	$tot_pages = $request->Total();
	$pagination = ceil( $tot_pages / $per_page );
	
	echo '<span class="no-print pagination"><b>Pages</b>: ';
	for( $i = 1; $i <= $pagination; $i++ )
	{
		$sep = ( $i == $pagination ) ? '' : ' | ';
		$current_page_class = ( $current_page == $i ) ? ' class="current-page"' : '';
		printf(
				'<a href="?se_paged=%s"%s>%s</a>%s',
				$i,
				$current_page_class,
				$i,
				$sep
		);
	}	
	
	# Post counter
	$count = 1 + ( ($current_page-1) * $per_page );
	$end_post = ( $current_page == $pagination ) ? $tot_pages : intval($count+$per_page-1);
	echo '<br /><sub><b>Showing '. $showing_type.':</b> ' . $count . ' to ' . $end_post . ' from ' . $tot_pages . '</sub></span>';
	
	# Loop
	if( 'answers' == $q_or_a )
	{
		while( $answer = $request->Fetch(FALSE) )
		{ 
			# Query Question
			$q =  API::Site($site)->Questions($answer['question_id']);
			$qq = $q->Filter('!-.dP0*IiKY0d')->Exec()->Fetch(FALSE);
		
			# Set Question properties
			$qtags = !empty($qq['tags']) ? '<span>'.implode('</span><span>', $qq['tags'] ).'</span>' : '';
			$qauthor = $qq['owner']['display_name'];
			$qauthorlink = $qq['owner']['link'];
			$qqbod = isset( $qq['body'] ) ? $qq['body'] : '<i>could not retrieve question body</i>'; 
		
			# Set Answer properties
			$tit = $answer['title'];
			$qdate = date('d/m/Y', $qq['creation_date'] );
			$link = $answer['link'];
			$body = $answer['body'];
			$score = $answer['score'];
			$accepted = ( isset( $answer['is_accepted']) && $answer['is_accepted'] ) ? '- <span class="accepted">Accepted</span>' : '';
			$author = $answer['owner']['display_name'];
			$authorlink = $answer['owner']['link'];
			$date = date('d/m/Y', $answer['creation_date'] );
		
			#Output
			echo <<<HTML
			<div class="stacktack stacktack-container" data-site="stackoverflow" style="width: auto;">
				<div class="branding">$count</div>

				<div class="question-body">
					<a href="$link" target="_blank" class="heading">$tit</a><a href="$qauthorlink" class="user-link">$qauthor</a><span class="user-link"> | $qdate</span>

					<div class="hr"></div>
					$qqbod
					<div class="tags">$qtags</div>
				</div>

				<div class="answer-body">
					<a href="$link" target="_blank" class="heading answer-count">$score votes $accepted</a>

					<a href="$authorlink" class="user-link">$author</a><span class="user-link"> | $date</span>

					$body
				</div>
			</div>
HTML;
			$count++;
		}
	}
	else
	{
		while( $question = $request->Fetch(FALSE) )
		{ 
			# Set Question properties
			$qtags = !empty($question['tags']) ? '<span>'.implode('</span><span>', $question['tags'] ).'</span>' : '';
			$qauthor = $question['owner']['display_name'];
			$qauthorlink = $question['owner']['link'];
			$qqbod = isset( $question['body'] ) ? $question['body'] : '<i>could not retrieve question body</i>'; 
			$qdate = date( 'd/m/Y', $question['creation_date'] );
			$qlink = $question['link'];
			$qtit = $question['title'];
			$qscore = $plugin->metabox->get_score( $question['score'], '| ' );


			#Output Question div
			echo <<<HTML
			<div class="stacktack stacktack-container" data-site="stackoverflow" style="width: auto;">
				<div class="branding">$count</div>

				<div class="question-body">
					<a href="$qlink" target="_blank" class="heading">$qtit</a><a href="$qauthorlink" class="user-link">$qauthor</a><span class="user-link"> | $qdate $qscore</span>

					<div class="hr"></div>
					$qqbod
					<div class="tags">$qtags</div>
				</div>
HTML;
			# Output Answers divs
			foreach( $question['answers'] as $qanswer )
			{
				# Set Answer properties
				$body = $qanswer['body'];
				$score = $plugin->metabox->get_score( $qanswer['score'], '', ' - ' );
				$accepted = ( isset( $qanswer['is_accepted']) && $qanswer['is_accepted'] ) ? '<span class="accepted-text">Accepted</span>' : '';
				$accepted_bg = ( isset( $qanswer['is_accepted']) && $qanswer['is_accepted'] ) ? 'accepted-bg' : '';
				$author = $qanswer['owner']['display_name'];
				$authorlink = isset( $qanswer['owner']['link'] ) ? $qanswer['owner']['link'] : '#';
				$date = date('d/m/Y', $qanswer['creation_date'] );
				if( isset( $qanswer['owner']['profile_image'] ) )
				{
					$avatar_image = $qanswer['owner']['profile_image'] ;
					//if( strpos( $qanswer['owner']['profile_image'], 'gravatar.com' ) === false )
						$avatar_image = str_replace( 's=128', 's=32', $avatar_image );
					$avatar = "<img src='$avatar_image' />";
				}
				else
					$avatar = '';
				echo <<<HTML
				<div class="answer-body">
					<div class="answer-title $accepted_bg">$avatar $score $accepted

					<a href="$authorlink" class="user-link">$author</a><span class="user-link"> | $date</span></div>

					$body
				</div>
HTML;
			}
			
			# Close Question div
			echo <<<HTML
			</div>
HTML;
			$count++;
		}
	}
  
  ?>
</body>
</html>
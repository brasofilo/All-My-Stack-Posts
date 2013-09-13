<?php

class B5F_SE_Frontend
{
	private $plugin_path;
	private $plugin_url;
	public function __construct( $path, $url ) 
	{
		$this->plugin_path = $path;
		$this->plugin_url = $url;
	}

	public function get_post_meta()
	{
		global $post;
		return array(
			'se_site' => get_post_meta( $post->ID, 'se_site', true ),
			'user_id' => get_post_meta( $post->ID, 'se_user_id', true ),
			'disable_cache' => get_post_meta( $post->ID, 'se_cached', true ),
			'per_page' => get_post_meta( $post->ID, 'se_per_page', true ),
			'q_or_a' => get_post_meta( $post->ID, 'se_post_type', true ),
			'sort_order' => get_post_meta( $post->ID, 'se_sort_order', true ),
			'referrer' => get_post_meta( $post->ID, 'se_referrer_id', true )
		);
	}
	
	public function get_profile( $user, $showing_type, $site, $total )
	{
		$reputation = $this->count_format($user['reputation']);
		$total_posts = number_format($total, 0, ',', '.');
		$badges = $this->get_badges( $user );
		$html = <<<HTML
<div class='user-profile'>
	<div class='gravatar'>
		<img src='{$user['profile_image']}&s=78' />
	</div>
	<div id="user-name">{$user['display_name']}</div>
	<div id="user-rep">$badges<kbd>$reputation</kbd> reputation</div>
	<div id="tag-line">$total_posts $showing_type @ <b><a href="{$site['link']}" title="{$site['desc']}">
			{$site['name']}</a></b>
	</div>
</div>
HTML;
		return $html;
	}
	
	
	public function get_badges( $user )
	{
		$badges = $gold = $silver = $bronze = '';
		
		if( $user['badge_counts']['gold'] > 0 ) {
			$val = $user['badge_counts']['gold'];
			$gold = "<span title='$val gold badges'>
				<span class='badge1'></span>
				<span class='badgecount'>$val</span>
			</span>";
		}
		if( $user['badge_counts']['silver'] > 0 ) {
			$val = $user['badge_counts']['silver'];
			$silver = "<span title='$val silver badges'>
				<span class='badge2'></span>
				<span class='badgecount'>$val</span>
			</span>";
		}
		if( $user['badge_counts']['bronze'] > 0 ) {
			$val = $user['badge_counts']['bronze'];
			$bronze = "<span title='$val bronze badges'>
				<span class='badge3'></span>
				<span class='badgecount'>$val</span>
			</span>";
		}
		
		if( !empty( $gold ) || !empty( $silver ) || !empty( $bronze ) )
			$badges = '<div class="badges">' . $gold . $silver . $bronze . '</div>';
		
		return $badges;
	}
	
	public function get_my_question( $question, $print_count, $site_link, $referrer )
	{
		# Set Question properties
		$tags = !empty($question['tags']) 
				? '<span>'.implode('</span><span>', $question['tags'] ).'</span>' : '';
		$author = $question['owner']['display_name'];
		$authorlink = $question['owner']['link'];
		$body= isset( $question['body'] ) 
				? $question['body'] : '<i>could not retrieve question body</i>'; 
		$date = date( 'd/m/Y', $question['creation_date'] );
		$link = $question['link'];
		$short_link = $site_link. '/q/'.$question['question_id'];
		if( !empty($referrer) )
			$short_link .= '/'.$referrer;
		$tit = $question['title'];
		$score = $this->get_score( $question['score'], '| ' );
		$answers_count = ( !empty( $question['answers'] ) ) ? ' | '.count($question['answers']).' answers' : '';


		#Output
		echo <<<HTML
		<div class="stacktack stacktack-container" data-site="stackoverflow" style="width: auto;">
			<div class="branding">$print_count</div>

			<div class="question-body">
				<a href="$short_link" target="_blank" class="heading">$tit</a><a href="$authorlink" class="user-link">$author</a><span class="user-link"> | $date $score $answers_count</span>

				<div class="hr"></div>
				$body
				<div class="tags">$tags</div>
			</div>
HTML;
	}
	
	
	public function get_their_answers( $answer )
	{
		# Set Answer properties
		$body = $answer['body'];
		$score = $this->get_score( $answer['score'], '', ' - ' );
		$accepted = ( isset( $answer['is_accepted']) && $answer['is_accepted'] ) 
				? '<span class="accepted-text">Accepted</span>' : '';
		$accepted_bg = ( isset( $answer['is_accepted']) && $answer['is_accepted'] ) 
				? 'accepted-bg' : '';
		$accepted_arrow = ( isset( $answer['is_accepted']) && $answer['is_accepted'] ) 
				? '<span class="vote-accepted-on"></span>' : '';
		$author = $answer['owner']['display_name'];
		$authorlink = isset( $answer['owner']['link'] ) 
				? $answer['owner']['link'] : '#';
		$date = isset( $answer['creation_date'] ) ? ' | '.date('d/m/Y', $answer['creation_date'] ) : '';
		$avatar = $this->get_avatar( $answer );
		
		# Output
		echo <<<HTML
		<div class="answer-body">

			<div class="answer-title $accepted_bg">$avatar $score

			<a href="$authorlink" class="user-link">$author</a><span class="user-link">$date</span> $accepted_arrow</div>

			$body
		</div>
HTML;
	}

	public function get_their_question( $question, $site_link, $referrer )
	{
		# Set Question properties
		$short_link = $site_link. '/q/'.$question['question_id'];
		if( !empty($referrer) )
			$short_link .= '/'.$referrer;
		
		$tags = !empty($question['tags']) 
				? '<span>'.implode('</span><span>', $question['tags'] ).'</span>' : '';
		
		$date = date('d/m/Y', $question['creation_date'] );
		
		$score = $this->get_score( $question['score'], '| ', '' );
		
		$body = isset( $question['body'] ) 
				? $question['body'] : '<i>could not retrieve question body</i>'; 
		
		$avatar = $this->get_avatar( $question );
		
		# Output
		$html = <<<HTML
			<div class="question-body">
				<a href="{$question['owner']['link']}" class="user-link">$avatar {$question['owner']['display_name']}</a><span class="user-link"> | $date $score</span>
				<a href="$short_link" target="_blank" class="heading">{$question['title']}</a>

				<div class="hr"></div>
				$body
				<div class="tags">$tags</div>
			</div>
HTML;
		return $html;
	}
	
	public function get_my_answer( $answer, $site_link, $referrer )
	{
		# Set Answer properties
		$short_link = $site_link. '/a/'.$answer['answer_id'];
		if( !empty($referrer) )
			$short_link .= '/'.$referrer;
		
		$score = $this->get_score( $answer['score'], '', '' );
		
		$score_accepted_bg = ( isset( $answer['is_accepted']) && $answer['is_accepted'] ) 
				? ' accepted-bg' : '';
		$score_box = '<span class="score-box'.$score_accepted_bg.'">'.$answer['score'].'</span>';
		
		$accepted = ( isset( $answer['is_accepted']) && $answer['is_accepted'] ) 
				? '<span class="vote-accepted-on fleft"></span>' : '';
		$accepted = ''; // DEBUG
		$date = date('d/m/Y', $answer['creation_date'] );
		
		# Output
		$html = <<<HTML
			<div class="answer-body"><p style="min-height: 40px;">
				<a href="$short_link" target="_blank" class="heading answer-count white-count" title="open Answer">
					$accepted $score_box 
					<span class="user-link ul-answer">$date</span>
					</a>

				{$answer['body']}
			</div>
HTML;
		return $html;
	}
	
	
	
	public function get_avatar( $post )
	{
		if( isset( $post['owner']['profile_image'] ) )
		{
			$avatar_image = $post['owner']['profile_image'] ;
			$src = str_replace( 's=128', 's=32', $avatar_image );
			return "<img src='$src' class='se-avatar' />";
		}
		else
			return '';
	}
	
	/**
	 * Zero, one or more votes
	 * @param string $score
	 * @return string
	 */
	public function get_score( $score, $prefix='', $suffix='' )
	{
		switch( $score )
		{
			case '0':
			null:
				$score = '';
			break;
			case '1':
				$score = $prefix.'1 vote'.$suffix;
			break;
			default:
				$score = $prefix . $score . ' votes'.$suffix;
			break;
		}
		return $score;
	}
	
	function count_format($n, $point=',', $sep='.') {
		if ($n < 0) 
			return 0;

		if ($n < 10000)
			return number_format($n, 0, $point, $sep);

		$d = $n < 1000000 ? 1000 : 1000000;

		$f = round($n / $d, 1);

		return number_format($f, $f - intval($f) ? 1 : 0, $sep, $point) . ($d == 1000 ? 'k' : 'M');
	}
}
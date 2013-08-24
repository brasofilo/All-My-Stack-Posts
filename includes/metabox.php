<?php

class B5F_SE_Metabox
{
	public function __construct() 
	{
		add_action( 'add_meta_boxes', array( $this, 'add_custom_box' ) );
		add_action( 'save_post', array( $this, 'save_postdata' ) );
		add_action( 'admin_head-post.php', array( $this, 'script_enqueuer' ) );
	}



	/* Adds a box to the main column on the Post and Page edit screens */
	public function add_custom_box() 
	{
		add_meta_box(
			'b5f_se_metabox_section_id',
			__( 'Stack Exchange - All my posts', 'wpse' ), 
			array( $this, 'inner_custom_box' ),
			'page',
			'side'
		);
	}


	/* Prints the box content */
	public function inner_custom_box($post)
	{
		wp_nonce_field( plugin_basename( __FILE__ ), 'b5f_se_metabox_nonce' );

		# Sites list
		$se_sites = $this->b5f_get_se_sites();
		$se_site_saved = get_post_meta( $post->ID, 'se_site', true);
		if( !$se_site_saved )
			$se_site_saved = 'stackoverflow';
		echo '<p><strong>Stack Site</strong><br /><select name="se_site" id="se_site">';
		foreach ( $se_sites as $key => $label ) 
		{
			printf(
				'<option value="%s" %s> %s</option>',
				esc_attr($key),
				selected( $se_site_saved, $key, false),
				esc_html($label[0])
			);
		}
		echo '</select></p>';
		#
		
		# Post types
		$se_post_types = array(
			'answers'       => __('My Answers', 'wpse'),
			'questions'     => __('My Questions', 'wpse')
		);
		$se_post_type_saved = get_post_meta( $post->ID, 'se_post_type', true);
		if( !$se_post_type_saved )
			$se_post_type_saved = 'questions';
		echo '<p><strong>Type of Posts</strong><br /><select name="se_post_type" id="se_post_type">';
		foreach ( $se_post_types as $key => $label ) 
		{
			printf(
				'<option value="%s" %s> %s</option>',
				esc_attr($key),
				selected( $se_post_type_saved, $key, false),
				esc_html($label)
			);
		}
		echo '</select></p>';
		#
		
		# User ID
		$se_user_id_saved = get_post_meta( $post->ID, 'se_user_id', true);
		if( !$se_user_id_saved )
			$se_user_id_saved = '';
		echo "<p><strong>User ID</strong><br /><input type='text' class='widefat' name='se_user_id' value='" . esc_attr( $se_user_id_saved ) . "' /></p>";
		#
		
		# Posts per page
		$se_per_page = get_post_meta( $post->ID, 'se_per_page', true);
		echo "<p><strong>Posts per page</strong><br /><input type='text' class='widefat' name='se_per_page' value='" . esc_attr( $se_per_page ) . "' /></p>";
		#
		
		# Cache
		$se_cached = get_post_meta( $post->ID, 'se_cached', true);
		printf(
			'<p><strong>Cache results</strong><br /><input name="se_cached" id="se_cached" type="checkbox" %s />',
			checked( $se_cached, 'on', false)
		);
	}

	/* When the post is saved, saves our custom data */
	public function save_postdata( $post_id ) 
	{
		  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			  return;

		  if ( !isset( $_POST['b5f_se_metabox_nonce'] ) ||  !wp_verify_nonce( $_POST['b5f_se_metabox_nonce'], plugin_basename( __FILE__ ) ) )
			  return;

		  if ( isset($_POST['se_site']) )
				update_post_meta( 
						$post_id, 
						'se_site', 
						$_POST['se_site'] 
				);
		  
		  if ( isset($_POST['se_post_type']) )
				update_post_meta( 
						$post_id, 
						'se_post_type', 
						$_POST['se_post_type'] 
				);
		  
		  if ( isset($_POST['se_user_id']) && $_POST['se_user_id'] != "" )
				update_post_meta( 
						$post_id, 
						'se_user_id', 
						stripslashes( strip_tags( $_POST['se_user_id'] ) ) 
				);
		  if ( isset($_POST['se_cached']) && $_POST['se_cached'] != "" )
				update_post_meta( 
						$post_id, 
						'se_cached', 
						$_POST['se_cached'] 
				);
		  else
			  delete_post_meta( $post_id, 'se_cached' );
		  
		  if ( isset($_POST['se_per_page']) && $_POST['se_per_page'] != "" )
		  {
			  $total = intval( stripslashes( strip_tags( $_POST['se_per_page'] ) ) );
			  if( $total > 100 )
				  $total = 100;
				update_post_meta( 
						$post_id, 
						'se_per_page', 
						 $total
				);
		  }
	}

	public function script_enqueuer() 
	{
		global $current_screen;

		if( 'page' == $current_screen->id ) 
		{
			echo <<<HTML
				<script type="text/javascript">
				jQuery(document).ready( function($) {

					/**
					 * Adjust visibility of the meta box at startup
					*/
					if($('#page_template').val() == 'template-stackapp.php') {
						// show the meta box
						$('#b5f_se_metabox_section_id').show();
						$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").show();
					} else {
						// hide your meta box
						$('#b5f_se_metabox_section_id').hide();
						$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").hide();
					}

					// Debug only
					// - outputs the template filename
					// - checking for console existance to avoid js errors in non-compliant browsers
					if (typeof console == "object") 
						console.log ('default value = ' + $('#page_template').val());

					/**
					 * Live adjustment of the meta box visibility
					*/
					$('#page_template').live('change', function(){
							if($(this).val() == 'template-stackapp.php') {
							// show the meta box
							$('#b5f_se_metabox_section_id').show();
							$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").show();
						} else {
							// hide your meta box
							$('#b5f_se_metabox_section_id').hide();
							$("form#adv-settings label[for='b5f_se_metabox_section_id-hide']").hide();
						}

						// Debug only
						if (typeof console == "object") 
							console.log ('live change value = ' + $(this).val());
					});					
				});    
				</script>
HTML;
		} 
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
	
	public function b5f_get_se_sites()
	{
			$se_sites = array (
			'academia' => array(
			  'Academia',
			  'http://academia.stackexchange.com',
			  'Q&A for academics and those enrolled in higher education',
			),
			'android' => array(
			  'Android Enthusiasts',
			  'http://android.stackexchange.com',
			  'Q&A for enthusiasts and power users of the Android operating system',
			),
			'anime' => array(
			  'Anime & Manga',
			  'http://anime.stackexchange.com',
			  'Q&A for anime and manga fans',
			),
			'answers.onstartups' => array(
			  'Answers OnStartups',
			  'http://answers.onstartups.com',
			  'Q&A for entrepreneurs looking to start or run a new business',
			),
			'apple' => array(
			  'Ask Different',
			  'http://apple.stackexchange.com',
			  'Q&A for power users of Apple hardware and software',
			),
			'askubuntu' => array(
			  'Ask Ubuntu',
			  'http://askubuntu.com',
			  'Q&A for Ubuntu users and developers',
			),
			'avp' => array(
			  'Audio-Video Production',
			  'http://avp.stackexchange.com',
			  'Q&A for engineers, producers, editors, and enthusiasts spanning the fields of audio, video, and media creation',
			),
			'bicycles' => array(
			  'Bicycles',
			  'http://bicycles.stackexchange.com',
			  'Q&A for people who build and repair bicycles, people who train cycling, or commute on bicycles',
			),
			'biology' => array(
			  'Biology',
			  'http://biology.stackexchange.com',
			  'Q&A for biology researchers, academics, and students',
			),
			'bitcoin' => array(
			  'Bitcoin',
			  'http://bitcoin.stackexchange.com',
			  'Q&A for Bitcoin crypto-currency enthusiasts',
			),
			'boardgames' => array(
			  'Board & Card Games',
			  'http://boardgames.stackexchange.com',
			  'Q&A for people who like playing board games, designing board games or modifying the rules of existing board games',
			),
			'bricks' => array(
			  'LEGO® Answers',
			  'http://bricks.stackexchange.com',
			  'Q&A for LEGO® and building block enthusiasts',
			),
			'chemistry' => array(
			  'Chemistry',
			  'http://chemistry.stackexchange.com',
			  'Q&A for scientists, academics, teachers and students',
			),
			'chess' => array(
			  'Chess',
			  'http://chess.stackexchange.com',
			  'Q&A for serious players and enthusiasts of chess',
			),
			'chinese' => array(
			  'Chinese Language & Usage',
			  'http://chinese.stackexchange.com',
			  'Q&A for students, teachers, and linguists wanting to discuss the finer points of the Chinese language',
			),
			'christianity' => array(
			  'Christianity',
			  'http://christianity.stackexchange.com',
			  'Q&A for committed Christians, experts in Christianity and those interested in learning more',
			),
			'codegolf' => array(
			  'Programming Puzzles & Code Golf',
			  'http://codegolf.stackexchange.com',
			  'Q&A for programming puzzle enthusiasts and code golfers',
			),
			'codereview' => array(
			  'Code Review',
			  'http://codereview.stackexchange.com',
			  'Q&A for peer programmer code reviews',
			),
			'cogsci' => array(
			  'Cognitive Sciences',
			  'http://cogsci.stackexchange.com',
			  'Q&A for practitioners, researchers, and students in cognitive science, psychology, neuroscience, and psychiatry',
			),
			'cooking' => array(
			  'Seasoned Advice',
			  'http://cooking.stackexchange.com',
			  'Q&A for professional and amateur chefs',
			),
			'crypto' => array(
			  'Cryptography',
			  'http://crypto.stackexchange.com',
			  'Q&A for software developers, mathematicians and others interested in cryptography',
			),
			'cs' => array(
			  'Computer Science',
			  'http://cs.stackexchange.com',
			  'Q&A for students, researchers and practitioners of computer science',
			),
			'cstheory' => array(
			  'Theoretical Computer Science',
			  'http://cstheory.stackexchange.com',
			  'Q&A for theoretical computer scientists and researchers in related fields',
			),
			'dba' => array(
			  'Database Administrators',
			  'http://dba.stackexchange.com',
			  'Q&A for database professionals who wish to improve their database skills and learn from others in  the community',
			),
			'diy' => array(
			  'Home Improvement',
			  'http://diy.stackexchange.com',
			  'Q&A for contractors and serious DIYers',
			),
			'drupal' => array(
			  'Drupal Answers',
			  'http://drupal.stackexchange.com',
			  'Q&A for Drupal developers and administrators',
			),
			'dsp' => array(
			  'Signal Processing',
			  'http://dsp.stackexchange.com',
			  'Q&A for practitioners of the art and science of signal, image and video processing',
			),
			'electronics' => array(
			  'Electrical Engineering',
			  'http://electronics.stackexchange.com',
			  'Q&A for electronics and electrical engineering professionals, students, and enthusiasts',
			),
			'ell' => array(
			  'English Language Learners',
			  'http://ell.stackexchange.com',
			  'Q&A for speakers of other languages learning English',
			),
			'english' => array(
			  'English Language & Usage',
			  'http://english.stackexchange.com',
			  'Q&A for linguists, etymologists, and serious English language enthusiasts',
			),
			'expressionengine' => array(
			  'ExpressionEngine® Answers',
			  'http://expressionengine.stackexchange.com',
			  'Q&A for administrators, end users, developers and designers for ExpressionEngine® CMS',
			),
			'fitness' => array(
			  'Physical Fitness',
			  'http://fitness.stackexchange.com',
			  'Q&A for physical fitness professionals, athletes, trainers, and those providing health-related needs',
			),
			'french' => array(
			  'French Language & Usage',
			  'http://french.stackexchange.com',
			  'Q&A for students, teachers, and linguists wanting to discuss the finer points of the French language',
			),
			'gamedev' => array(
			  'Game Development',
			  'http://gamedev.stackexchange.com',
			  'Q&A for professional and independent game developers',
			),
			'gaming' => array(
			  'Arqade',
			  'http://gaming.stackexchange.com',
			  'Q&A for passionate videogamers on all platforms',
			),
			'gardening' => array(
			  'Gardening & Landscaping',
			  'http://gardening.stackexchange.com',
			  'Q&A for gardeners and landscapers',
			),
			'genealogy' => array(
			  'Genealogy & Family History',
			  'http://genealogy.stackexchange.com',
			  'Q&A for expert genealogists and people interested in genealogy or family history',
			),
			'german' => array(
			  'German Language & Usage',
			  'http://german.stackexchange.com',
			  'Q&A for speakers of German wanting to discuss the finer points of the language and translation',
			),
			'gis' => array(
			  'Geographic Information Systems',
			  'http://gis.stackexchange.com',
			  'Q&A for cartographers, geographers and GIS professionals',
			),
			'graphicdesign' => array(
			  'Graphic Design',
			  'http://graphicdesign.stackexchange.com',
			  'Q&A for professional graphic designers and non-designers trying to do their own graphic design',
			),
			'hermeneutics' => array(
			  'Biblical Hermeneutics',
			  'http://hermeneutics.stackexchange.com',
			  'Q&A for professors, theologians, and those interested in exegetical analysis of biblical texts',
			),
			'history' => array(
			  'History',
			  'http://history.stackexchange.com',
			  'Q&A for historians and history buffs',
			),
			'homebrew' => array(
			  'Homebrewing',
			  'http://homebrew.stackexchange.com',
			  'Q&A for dedicated home brewers and serious enthusiasts',
			),
			'islam' => array(
			  'Islam',
			  'http://islam.stackexchange.com',
			  'Q&A for muslims, experts in Islam, and those interested in learning more about Islam',
			),
			'japanese' => array(
			  'Japanese Language & Usage',
			  'http://japanese.stackexchange.com',
			  'Q&A for students, teachers, and linguists wanting to discuss the finer points of the Japanese language',
			),
			'judaism' => array(
			  'Mi Yodeya',
			  'http://judaism.stackexchange.com',
			  'Q&A for those who base their lives on Jewish law and tradition and anyone interested in learning more',
			),
			'libraries' => array(
			  'Libraries & Information Science',
			  'http://libraries.stackexchange.com',
			  'Q&A for librarians and library professionals',
			),
			'linguistics' => array(
			  'Linguistics',
			  'http://linguistics.stackexchange.com',
			  'Q&A for professional linguists and others with an interest in linguistic research and theory',
			),
			'magento' => array(
			  'Magento',
			  'http://magento.stackexchange.com',
			  'Q&A for users of the Magento e-Commerce platform',
			),
			'martialarts' => array(
			  'Martial Arts',
			  'http://martialarts.stackexchange.com',
			  'Q&A for students and teachers of all martial arts',
			),
			'math' => array(
			  'Mathematics',
			  'http://math.stackexchange.com',
			  'Q&A for people studying math at any level and professionals in related fields',
			),
			'mathematica' => array(
			  'Mathematica',
			  'http://mathematica.stackexchange.com',
			  'Q&A for users of Mathematica',
			),
			'mechanics' => array(
			  'Motor Vehicle Maintenance & Repair',
			  'http://mechanics.stackexchange.com',
			  'Q&A for mechanics and DIY enthusiast owners of cars, trucks, and motorcycles',
			),
			'meta.stackoverflow' => array(
			  'Meta Stack Overflow',
			  'http://meta.stackoverflow.com',
			  'Q&A for the Stack Exchange engine powering these sites',
			),
			'money' => array(
			  'Personal Finance & Money',
			  'http://money.stackexchange.com',
			  'Q&A for people who want to be financially literate',
			),
			'movies' => array(
			  'Movies & TV',
			  'http://movies.stackexchange.com',
			  'Q&A for movie and tv enthusiasts',
			),
			'music' => array(
			  'Musical Practice & Performance',
			  'http://music.stackexchange.com',
			  'Q&A for musicians, students, and enthusiasts',
			),
			'outdoors' => array(
			  'The Great Outdoors',
			  'http://outdoors.stackexchange.com',
			  'Q&A for people who love outdoor activities, excursions, and outdoorsmanship',
			),
			'parenting' => array(
			  'Parenting',
			  'http://parenting.stackexchange.com',
			  'Q&A for parents, grandparents, nannies and others with a parenting role',
			),
			'patents' => array(
			  'Ask Patents',
			  'http://patents.stackexchange.com',
			  'Q&A for people interested in improving and participating in the patent system',
			),
			'philosophy' => array(
			  'Philosophy',
			  'http://philosophy.stackexchange.com',
			  'Q&A for those interested in logical reasoning',
			),
			'photo' => array(
			  'Photography',
			  'http://photo.stackexchange.com',
			  'Q&A for professional, enthusiast and amateur photographers',
			),
			'physics' => array(
			  'Physics',
			  'http://physics.stackexchange.com',
			  'Q&A for active researchers, academics and students of physics',
			),
			'pm' => array(
			  'Project Management',
			  'http://pm.stackexchange.com',
			  'Q&A for project managers',
			),
			'poker' => array(
			  'Poker',
			  'http://poker.stackexchange.com',
			  'Q&A for serious players and enthusiasts of poker',
			),
			'politics' => array(
			  'Politics',
			  'http://politics.stackexchange.com',
			  'Q&A for people interested in governments, policies, and political processes',
			),
			'productivity' => array(
			  'Personal Productivity',
			  'http://productivity.stackexchange.com',
			  'Q&A for people wanting to improve their personal productivity',
			),
			'programmers' => array(
			  'Programmers',
			  'http://programmers.stackexchange.com',
			  'Q&A for professional programmers interested in conceptual questions about software development',
			),
			'quant' => array(
			  'Quantitative Finance',
			  'http://quant.stackexchange.com',
			  'Q&A for finance professionals and academics',
			),
			'raspberrypi' => array(
			  'Raspberry Pi',
			  'http://raspberrypi.stackexchange.com',
			  'Q&A for users and developers of hardware and software for Raspberry Pi',
			),
			'reverseengineering' => array(
			  'Reverse Engineering',
			  'http://reverseengineering.stackexchange.com',
			  'Q&A for researchers and developers who explore the principles of a system through analysis of its structure, function, and operation',
			),
			'robotics' => array(
			  'Robotics',
			  'http://robotics.stackexchange.com',
			  'Q&A for professional robotic engineers, hobbyists, researchers and students',
			),
			'rpg' => array(
			  'Role-playing Games',
			  'http://rpg.stackexchange.com',
			  'Q&A for gamemasters and players of tabletop, paper-and-pencil role-playing games',
			),
			'russian' => array(
			  'Russian Language & Usage',
			  'http://russian.stackexchange.com',
			  'Q&A for students, teachers, and linguists wanting to discuss the finer points of the Russian language',
			),
			'salesforce' => array(
			  'Salesforce',
			  'http://salesforce.stackexchange.com',
			  'Q&A for Salesforce administrators, implementation experts, developers and anybody in-between',
			),
			'scicomp' => array(
			  'Computational Science',
			  'http://scicomp.stackexchange.com',
			  'Q&A for scientists using computers to solve scientific problems',
			),
			'scifi' => array(
			  'Science Fiction & Fantasy',
			  'http://scifi.stackexchange.com',
			  'Q&A for science fiction and fantasy enthusiasts',
			),
			'security' => array(
			  'IT Security',
			  'http://security.stackexchange.com',
			  'Q&A for IT security professionals',
			),
			'serverfault' => array(
			  'Server Fault',
			  'http://serverfault.com',
			  'Q&A for professional system and network administrators',
			),
			'sharepoint' => array(
			  'SharePoint',
			  'http://sharepoint.stackexchange.com',
			  'Q&A for SharePoint enthusiasts',
			),
			'skeptics' => array(
			  'Skeptics',
			  'http://skeptics.stackexchange.com',
			  'Q&A for scientific skepticism',
			),
			'smugmug' => array(
			  'SmugMug',
			  'http://smugmug.stackexchange.com',
			  'Q&A for SmugMug developers and end users',
			),
			'spanish' => array(
			  'Spanish Language & Usage',
			  'http://spanish.stackexchange.com',
			  'Q&A for students, teachers, and linguists wanting to discuss the finer points of the Spanish language',
			),
			'sports' => array(
			  'Sports',
			  'http://sports.stackexchange.com',
			  'Q&A for participants in team and individual sport activities',
			),
			'sqa' => array(
			  'Software Quality Assurance & Testing',
			  'http://sqa.stackexchange.com',
			  'Q&A for software quality control experts, automation engineers, and software testers',
			),
			'stackapps' => array(
			  'Stack Apps',
			  'http://stackapps.com',
			  'Q&A for apps, scripts, and development with the Stack Exchange API',
			),
			'stackoverflow' => array(
			  'Stack Overflow',
			  'http://stackoverflow.com',
			  'Q&A for professional and enthusiast programmers',
			),
			'stats' => array(
			  'Cross Validated',
			  'http://stats.stackexchange.com',
			  'Q&A for statisticians, data analysts, data miners and data visualization experts',
			),
			'superuser' => array(
			  'Super User',
			  'http://superuser.com',
			  'Q&A for computer enthusiasts and power users',
			),
			'sustainability' => array(
			  'Sustainable Living',
			  'http://sustainability.stackexchange.com',
			  'Q&A for folks dedicated to a lifestyle that can be maintained indefinitely without depleting available resources',
			),
			'tex' => array(
			  'TeX - LaTeX',
			  'http://tex.stackexchange.com',
			  'Q&A for users of TeX, LaTeX, ConTeXt, and related typesetting systems',
			),
			'travel' => array(
			  'Travel',
			  'http://travel.stackexchange.com',
			  'Q&A for road warriors and seasoned travelers',
			),
			'tridion' => array(
			  'Tridion',
			  'http://tridion.stackexchange.com',
			  'Q&A for Tridion developers and administrators',
			),
			'unix' => array(
			  'Unix & Linux',
			  'http://unix.stackexchange.com',
			  'Q&A for users of Linux, FreeBSD and other Un*x-like operating systems.',
			),
			'ux' => array(
			  'User Experience',
			  'http://ux.stackexchange.com',
			  'Q&A for user experience researchers and experts',
			),
			'webapps' => array(
			  'Web Applications',
			  'http://webapps.stackexchange.com',
			  'Q&A for power users of web applications',
			),
			'webmasters' => array(
			  'Webmasters',
			  'http://webmasters.stackexchange.com',
			  'Q&A for pro webmasters',
			),
			'windowsphone' => array(
			  'Windows Phone',
			  'http://windowsphone.stackexchange.com',
			  'Q&A for enthusiasts and power users of Windows Phone OS',
			),
			'wordpress' => array(
			  'WordPress Answers',
			  'http://wordpress.stackexchange.com',
			  'Q&A for WordPress developers and administrators',
			),
			'workplace' => array(
			  'The Workplace',
			  'http://workplace.stackexchange.com',
			  'Q&A for members of the workforce navigating the professional setting',
			),
			'writers' => array(
			  'Writers',
			  'http://writers.stackexchange.com',
			  'Q&A for authors, editors, reviewers, professional writers, and aspiring writers',
			),
		  );
			return $se_sites;
	}
}
<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2011 Simple Machines
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.0
 */

function template_main()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings;
	
	echo '
	<div class="col3 floatright">';
	 
	slider();
	categori();
		echo '
	<div id="tabContainer">
    <div id="tabs">
      <ul>
        <li id="tabHeader_1">', $txt['online_users'], '</li>
        <li id="tabHeader_2">', $txt['forum_stats'], '</li>
      </ul>
    </div>
    <div id="tabscontent">
      <div class="tabpage" id="tabpage_1">';
    	online();
	echo '
      </div>
	  <div class="tabpage" id="tabpage_2">';
		  bilgi();
	echo '
	   </div>
	  </div></div>';
	echo '
	</div>';
	
	echo '
	<div class="col6 floatleft">';

	ssi_boardNews1();
	echo'
	</div>';
	
	echo '
	<br class="clear" />';
}
function bilgi(){
	global $context, $settings, $options, $txt, $scripturl, $modSettings;
	// Show statistical style information...
	if ($settings['show_stats_index'])
	{
		echo ' 
				', $context['common_stats']['total_posts'], ' ', $txt['posts_made'], ' ', $txt['in'], ' ', $context['common_stats']['total_topics'], ' ', $txt['topics'], ' ', $txt['by'], ' ', $context['common_stats']['total_members'], ' ', $txt['members'], '.<br> ', !empty($settings['show_latest_member']) ? $txt['latest_member'] . ': <strong> ' . $context['common_stats']['latest_member']['link'] . '</strong>' : '', '<br />
				', (!empty($context['latest_post']) ? $txt['latest_post'] . ': <strong>&quot;' . $context['latest_post']['link'] . '&quot;</strong> <br />' : ''), '
				<a href="', $scripturl, '?action=recent">', $txt['recent_view'], '</a>', $context['show_stats'] ? '<br />
				<a href="' . $scripturl . '?action=stats">' . $txt['more_stats'] . '</a>' : '', '';
	}


}
function online(){
    global $context, $settings, $options, $txt, $scripturl, $modSettings;
	// "Users online" - in order of activity.
	echo '
			<p class="inline stats">
				', $context['show_who'] ? '<a href="' . $scripturl . '?action=who">' : '', comma_format($context['num_guests']), ' ', $context['num_guests'] == 1 ? $txt['guest'] : $txt['guests'], ', ' . comma_format($context['num_users_online']), ' ', $context['num_users_online'] == 1 ? $txt['user'] : $txt['users'];

	// Handle hidden users and buddies.
	$bracketList = array();
	if ($context['show_buddies'])
		$bracketList[] = comma_format($context['num_buddies']) . ' ' . ($context['num_buddies'] == 1 ? $txt['buddy'] : $txt['buddies']);
	if (!empty($context['num_spiders']))
		$bracketList[] = comma_format($context['num_spiders']) . ' ' . ($context['num_spiders'] == 1 ? $txt['spider'] : $txt['spiders']);
	if (!empty($context['num_users_hidden']))
		$bracketList[] = comma_format($context['num_users_hidden']) . ' ' . $txt['hidden'];

	if (!empty($bracketList))
		echo ' (' . implode(', ', $bracketList) . ')';

	echo $context['show_who'] ? '</a>' : '', '
			</p>
			<p class="inline smalltext">';

	// Assuming there ARE users online... each user in users_online has an id, username, name, group, href, and link.
	if (!empty($context['users_online']))
	{
		echo '
				', sprintf($txt['users_active'], $modSettings['lastActive']), ':<br />', implode(', ', $context['list_users_online']);

		// Showing membergroups?
		if (!empty($settings['show_group_key']) && !empty($context['membergroups']))
			echo '
				<br />[' . implode(']&nbsp;&nbsp;[', $context['membergroups']) . ']';
	}
	echo '
			</p>
			<p class="last smalltext">
				', $txt['most_online_today'], ': <strong>', comma_format($modSettings['mostOnlineToday']), '</strong>.
				', $txt['most_online_ever'], ': ', comma_format($modSettings['mostOnline']), ' (', timeformat($modSettings['mostDate']), ')
			</p>';

	// If they are logged in, but statistical information is off... show a personal message bar.
	if ($context['user']['is_logged'] && !$settings['show_stats_index'])
	{
		echo '
			<div class="title_barIC">
				<h4 class="titlebg">
					<span class="ie6_header floatleft">
						', $context['allow_pm'] ? '<a href="' . $scripturl . '?action=pm">' : '', '<img class="icon" src="', $settings['images_url'], '/message_sm.gif" alt="', $txt['personal_message'], '" />', $context['allow_pm'] ? '</a>' : '', '
						<span>', $txt['personal_message'], '</span>
					</span>
				</h4>
			</div>
			<p class="pminfo">
				<strong><a href="', $scripturl, '?action=pm">', $txt['personal_message'], '</a></strong>
				<span class="smalltext">
					', $txt['you_have'], ' ', comma_format($context['user']['messages']), ' ', $context['user']['messages'] == 1 ? $txt['message_lowercase'] : $txt['msg_alert_messages'], '.... ', $txt['click'], ' <a href="', $scripturl, '?action=pm">', $txt['here'], '</a> ', $txt['to_view'], '
				</span>
			</p>';
	}
}

// Show the latest news, with a template... by board.
function ssi_boardNews1($board = null, $limit = 10, $start = null, $length = 350, $output_method = 'echo')
{
	global $scripturl, $txt, $settings, $modSettings, $context;
	global $smcFunc;

	loadLanguage('Stats');

	// Must be integers....
	if ($limit === null)
		$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
	else
		$limit = (int) $limit;

	if ($start === null)
		$start = isset($_GET['start']) ? (int) $_GET['start'] : 0;
	else
		$start = (int) $start;

	if ($board !== null)
		$board = (int) $board;
	elseif (isset($_GET['board']))
		$board = (int) $_GET['board'];

	if ($length === null)
		$length = isset($_GET['length']) ? (int) $_GET['length'] : 0;
	else
		$length = (int) $length;

	$limit = max(0, $limit);
	$start = max(0, $start);

	// Make sure guests can see this board.
	$request = $smcFunc['db_query']('', '
		SELECT id_board
		FROM {db_prefix}boards
		WHERE ' . ($board === null ? '' : 'id_board = {int:current_board}
			AND ') . 'FIND_IN_SET(-1, member_groups) != 0
		LIMIT 1',
		array(
			'current_board' => $board,
		)
	);
	if ($smcFunc['db_num_rows']($request) == 0)
	{
		if ($output_method == 'echo')
			die($txt['ssi_no_guests']);
		else
			return array();
	}
	list ($board) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	// Load the message icons - the usual suspects.
	$stable_icons = array('xx', 'thumbup', 'thumbdown', 'exclamation', 'question', 'lamp', 'smiley', 'angry', 'cheesy', 'grin', 'sad', 'wink', 'poll', 'moved', 'recycled', 'wireless');
	$icon_sources = array();
	foreach ($stable_icons as $icon)
		$icon_sources[$icon] = 'images_url';

	// Find the post ids.
	$request = $smcFunc['db_query']('', '
		SELECT t.id_first_msg
		FROM {db_prefix}topics as t
		LEFT JOIN {db_prefix}boards as b ON (b.id_board = t.id_board)
		WHERE t.id_board = {int:current_board}' . ($modSettings['postmod_active'] ? '
			AND t.approved = {int:is_approved}' : '') . '
			AND {query_see_board}
		ORDER BY t.id_first_msg DESC
		LIMIT ' . $start . ', ' . $limit,
		array(
			'current_board' => $board,
			'is_approved' => 1,
		)
	);
	$posts = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$posts[] = $row['id_first_msg'];
	$smcFunc['db_free_result']($request);

	if (empty($posts))
		return array();

	// Find the posts.
	$request = $smcFunc['db_query']('', '
		SELECT
			m.icon, m.subject, m.body, IFNULL(mem.real_name, m.poster_name) AS poster_name, m.poster_time,
			t.num_replies, t.id_topic, m.id_member, m.smileys_enabled, m.id_msg, t.locked, t.id_last_msg, m.id_board
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = m.id_member)
		WHERE t.id_first_msg IN ({array_int:post_list})
		ORDER BY t.id_first_msg DESC
		LIMIT ' . count($posts),
		array(
			'post_list' => $posts,
		)
	);
	$return = array();
	$recycle_board = !empty($modSettings['recycle_enable']) && !empty($modSettings['recycle_board']) ? (int) $modSettings['recycle_board'] : 0;
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		// If we want to limit the length of the post.
		if (!empty($length) && $smcFunc['strlen']($row['body']) > $length)
		{
			$row['body'] = $smcFunc['substr']($row['body'], 0, $length);
			$cutoff = false;

			$last_space = strrpos($row['body'], ' ');
			$last_open = strrpos($row['body'], '<');
			$last_close = strrpos($row['body'], '>');
			if (empty($last_space) || ($last_space == $last_open + 3 && (empty($last_close) || (!empty($last_close) && $last_close < $last_open))) || $last_space < $last_open || $last_open == $length - 6)
				$cutoff = $last_open;
			elseif (empty($last_close) || $last_close < $last_open)
				$cutoff = $last_space;

			if ($cutoff !== false)
				$row['body'] = $smcFunc['substr']($row['body'], 0, $cutoff);
			$row['body'] .= '...';
		}

		$row['body'] = parse_bbc($row['body'], $row['smileys_enabled'], $row['id_msg']);

		if (!empty($recycle_board) && $row['id_board'] == $recycle_board)
			$row['icon'] = 'recycled';

		// Check that this message icon is there...
		if (!empty($modSettings['messageIconChecks_enable']) && !isset($icon_sources[$row['icon']]))
			$icon_sources[$row['icon']] = file_exists($settings['theme_dir'] . '/images/post/' . $row['icon'] . '.png') ? 'images_url' : 'default_images_url';
 // $row['body'] içerisinde <img> kodu ara
   $secimyap = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $row['body'], $sonuc);
   // src="" içindekini al.
 if(!empty($sonuc[0]) && !empty($sonuc[1]))
   $ilkresim = $sonuc [1] [0];
 
  else{ // Resim bulunmazsa default resim ekle
     $ilkresim = "http://www.teknoromi.com/teknoloji.haber.png";
   }		censorText($row['subject']);
		censorText($row['body']);

		$return[] = array(
			'id' => $row['id_topic'],
			'message_id' => $row['id_msg'],
			'icon' => '<img src="' . $settings[$icon_sources[$row['icon']]] . '/post/' . $row['icon'] . '.png" alt="' . $row['icon'] . '">',
			'subject' => $row['subject'],
			'resim' => $ilkresim,
			'time' => timeformat($row['poster_time']),
			'timestamp' => forum_time(true, $row['poster_time']),
			'body' => $row['body'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a class="yorum" href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['num_replies'] . ' ' . ($row['num_replies'] == 1 ? $txt['ssi_comment'] : $txt['ssi_comments']) . '</a>',
			'replies' => $row['num_replies'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'],
			'comment_link' => !empty($row['locked']) ? '' : '<a class="yorum" href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'] . '">' . $txt['ssi_write_comment'] . '</a>',
			'new_comment' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . '">' . $txt['ssi_write_comment'] . '</a>',
			'poster' => array(
				'id' => $row['id_member'],
				'name' => $row['poster_name'],
				'href' => !empty($row['id_member']) ? $scripturl . '?action=profile;u=' . $row['id_member'] : '',
				'link' => !empty($row['id_member']) ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['poster_name'] . '</a>' : $row['poster_name']
			),
			'locked' => !empty($row['locked']),
			'is_last' => false
		);
	}
	$smcFunc['db_free_result']($request);

	if (empty($return))
		return $return;

	$return[count($return) - 1]['is_last'] = true;

	if ($output_method != 'echo')
		return $return;
 echo '<table style="min-width:680px;background:#fff" >';
	foreach ($return as $news)
     {
       
		
		  echo '<td valing="top" class="haber">
            <div style="min-height: 220px;">
				<h3 class="news_header"style="height: 26px;">
		
					<a href="', $news['href'], '">', $news['subject'], '</a>
				</h3>
				<div class="news_timestamp">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], '</div>
                 
                <div  style="min-height: 152px; padding: 1ex 6px;"> <img src="', $news['resim'], '" alt="', $news['subject'], '" class="haber_resmi" />
                 
                 ', temizle($news['body']), ' </div>
 
                <p  style="margin-top: 5px; float: right;"> ', $news['link'], $news['locked'] ? '' : '  ' . $news['comment_link'], '</p>
             </div> ';
 
         if (!$news['is_last'])
             echo '
             </td>';
     }

	    echo '</td></table>';
}
function temizle($haber) {
     $strs=explode('<',$haber);
     $res=$strs[0];
     for($i=1;$i<count($strs);$i++)
     {
         if(!strpos($strs[$i],'>'))
             $res = $res.'&lt;'.$strs[$i];
         else
             $res = $res.'<'.$strs[$i];
     }
     return strip_tags($res);   
 } 
function categori(){
    global $context, $settings, $options, $txt, $scripturl, $modSettings;

	/* Each category in categories is made up of:
	id, href, link, name, is_collapsed (is it collapsed?), can_collapse (is it okay if it is?),
	new (is it new?), collapse_href (href to collapse/expand), collapse_image (up/down image),
	and boards. (see below.) */
	foreach ($context['categories'] as $category)
	{

			/* Each board in each category's boards has:
			new (is it new?), id, name, description, moderators (see below), link_moderators (just a list.),
			children (see below.), link_children (easier to use.), children_new (are they new?),
			topics (# of), posts (# of), link, href, and last_post. (see below.) */
			foreach ($category['boards'] as $board)
			{
				echo '
				<table id="board_', $board['id'], '" class="win1" style="width:100%;">
					<tr class="info">
						<td style="width:64%;"><a class="subject" href="', $board['href'], '" name="b', $board['id'], '">', $board['name'], '</a></td>';

				
				// Show some basic information about the number of posts, etc.
					echo '
				
						<td style="width:30%;"><span>', $board['is_redirect'] ? '' : comma_format($board['topics']) . ' ' . $txt['board_topics'], '</span></td>
					
                <td style="width:5%;"><a href="' . $scripturl . '?action=.xml;board=' . $board['id'] . ';type=rss"><img src="' . $settings['images_url'] . '/rss.png" alt="rss" /></a></td>'; 

echo'   
				</tr>';
				// Show the "Child Boards: ". (there's a link_children but we're going to bold the new ones...)
				if (!empty($board['children']))
				{
					// Sort the links into an array with new boards bold so it can be imploded.
					$children = array();
					/* Each child in each board's children has:
							id, name, description, new (is it new?), topics (#), posts (#), href, link, and last_post. */
					foreach ($board['children'] as $child)
					{
						if (!$child['is_redirect'])
							$child['link'] = '<a href="' . $child['href'] . '" ' . ($child['new'] ? 'class="new_posts" ' : '') . 'title="' . ($child['new'] ? $txt['new_posts'] : $txt['old_posts']) . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')">' . $child['name'] . ($child['new'] ? '</a> <a href="' . $scripturl . '?action=unread;board=' . $child['id'] . '" title="' . $txt['new_posts'] . ' (' . $txt['board_topics'] . ': ' . comma_format($child['topics']) . ', ' . $txt['posts'] . ': ' . comma_format($child['posts']) . ')"><img src="' . $settings['lang_images_url'] . '/new.gif" class="new_posts" alt="" />' : '') . '</a>';
						else
							$child['link'] = '<a href="' . $child['href'] . '" title="' . comma_format($child['posts']) . ' ' . $txt['redirects'] . '">' . $child['name'] . '</a>';

						// Has it posts awaiting approval?
						if ($child['can_approve_posts'] && ($child['unapproved_posts'] || $child['unapproved_topics']))
							$child['link'] .= ' <a href="' . $scripturl . '?action=moderate;area=postmod;sa=' . ($child['unapproved_topics'] > 0 ? 'topics' : 'posts') . ';brd=' . $child['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . '" title="' . sprintf($txt['unapproved_posts'], $child['unapproved_topics'], $child['unapproved_posts']) . '" class="moderation_link">(!)</a>';

						$children[] = $child['new'] ? '<strong>' . $child['link'] . '</strong>' : $child['link'];
					}
					echo '
					<tr id="board_', $board['id'], '_children">
						<td colspan="3" class="children win2">
							<table style="width:100%">
								<tr>';

							foreach ($children as $key => $child)
							{
								if ($key % 2 == 0 && $key != 0)
								echo '
								</tr>
								<tr>';

								echo '
									<td>', $child, '</td>';
							}

							echo '
								</tr>
							</table>
						</td>
					</tr>';
				}
			echo '
			</table>';
			}

	}
}

function slider() {
    global $settings;
    echo '		
    <script type="text/javascript" src="', $settings['theme_url'], '/scripts/jquery.js"></script>
    <script type="text/javascript" src="', $settings['theme_url'], '/scripts/jquery_002.js"></script>';
		
 global $smcFunc, $scripturl, $settings, $options, $txt ,$context, $modSettings;
		
$boards=array(1,2,3,4,5);

$request = $smcFunc['db_query']('', '
  SELECT t.id_topic, m.subject, m.body
  FROM {db_prefix}topics AS t
     INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
  WHERE t.id_board IN ({array_int:boards})
  ORDER BY t.id_topic DESC
       LIMIT {int:limit}',
  array(
    'boards' => $boards,
               'limit' => 5,
  )
);
$topics = array();
while ($row = $smcFunc['db_fetch_assoc']($request))
  $topics[] = array(
     'id_topic' => $row['id_topic'],
     'subject' => $row['subject'],
     'first_image'  => preg_match_all('~\[img.*?\]([^\]]+)\[\/img\]~i', $row['body'],  $images) ? '<img src="' . $images[1][0] . '" width="350px" alt="' .  $row['subject'] . '"/>      ' : '',
  );
  
 

$smcFunc['db_free_result']($request);

     echo '<div class="flex-container">
	<div class="flexslider">
		<ul class="slides">
			';
		 foreach ($topics as $topic)
			if (!empty($topic['first_image']))
				echo '
                <li class="" style="width: 100%; float: left; margin-right: -100%; position: relative; display: none;"><a  href="', $scripturl, '?topic=', $topic['id_topic'], '.0">',  $topic['first_image'], ' </a> 
                 <p>', $topic['subject'], '</p>
			    </li>';
								
			else {
				echo '
                  <li class="" style="width: 100%; float: left; margin-right: -100%; position: relative; display: none;"><a  href="', $scripturl, '?topic=', $topic['id_topic'], '.0"><img src="'.$settings['images_url'].'/th_resimyok.gif" alt="" /></a> 
                  <p>', $topic['subject'], '</p>
			</li>';				
			}      

echo '</ul>
	<ul class="flex-direction-nav"><li><a class="flex-prev" href="#">Previous</a></li><li><a class="flex-next" href="#">Next</a></li></ul></div>
</div>
<script>
$(document).ready(function () {
	$(\'.flexslider\').flexslider({
		animation: \'fade\',
		controlsContainer: \'.flexslider\'
	});
});
</script>';
}
?>


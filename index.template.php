<?php
/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines
 * @copyright 2014 Simple Machines and individual contributors
 * @license http://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 2.1 Alpha 1
 */

/*	This template is, perhaps, the most important template in the theme. It
	contains the main template layer that displays the header and footer of
	the forum, namely with main_above and main_below. It also contains the
	menu sub template, which appropriately displays the menu; the init sub
	template, which is there to set the theme up; (init can be missing.) and
	the linktree sub template, which sorts out the link tree.

	The init sub template should load any data and set any hardcoded options.

	The main_above sub template is what is shown above the main content, and
	should contain anything that should be shown up there.

	The main_below sub template, conversely, is shown after the main content.
	It should probably contain the copyright statement and some other things.

	The linktree sub template should display the link tree, using the data
	in the $context['linktree'] variable.

	The menu sub template should display all the relevant buttons the user
	wants and or needs.

	For more information on the templating system, please see the site at:
	http://www.simplemachines.org/
*/

/**
 * Initialize the template... mainly little settings.
 */
function template_init()
{
	global $settings;

	/* $context, $options and $txt may be available for use, but may not be fully populated yet. */

	/* Use images from default theme when using templates from the default theme?
		if this is 'always', images from the default theme will be used.
		if this is 'defaults', images from the default theme will only be used with default templates.
		if this is 'never' or isn't set at all, images from the default theme will not be used. */
	$settings['use_default_images'] = 'never';

	// The version this template/theme is for. This should probably be the version of SMF it was created for.
	$settings['theme_version'] = '2.1';

	// Use plain buttons - as opposed to text buttons?
	$settings['use_buttons'] = true;

	// Show sticky and lock status separate from topic icons?
	$settings['separate_sticky_lock'] = true;

	// Set the following variable to true if this theme requires the optional theme strings file to be loaded.
	$settings['require_theme_strings'] = false;

	// Set the following variable to true is this theme wants to display the avatar of the user that posted the last post on the board index and message index
	$settings['avatars_on_indexes'] = false;
}

/**
 * The main sub template above the content.
 */
function template_html_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings;

	// Show right to left and the character set for ease of translating.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
<head>';

	// The ?alp21 part of this link is just here to make sure browsers don't cache it wrongly.
	echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index.css?alp21" />';

	// The most efficient way of writing multi themes is to use a master index.css plus variant.css files.
	if (!empty($context['theme_variant']))
		echo '
	<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css?alp21" />';

	// Save some database hits, if a width for multiple wrappers is set in admin.
	if (!empty($settings['forum_width']))
		echo '
	<style type="text/css">#wrapper, .frame {width: ', $settings['forum_width'], ';}</style>';

	// Quick and dirty testing of RTL horrors. Remove before production build.
	//echo '
	//<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/rtl.css?alp21" />';

	// load in any css from mods or themes so they can overwrite if wanted
	template_css();

	// load in any javascript files from mods and themes
	template_javascript();

	// RTL languages require an additional stylesheet.
	if ($context['right_to_left'])
	{
		echo '
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/rtl.css?alp21" />';

	if (!empty($context['theme_variant']))
		echo '
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/rtl', $context['theme_variant'], '.css?alp21" />';
	}

	echo '
	<meta http-equiv="Content-Type" content="text/html; charset=', $context['character_set'], '" />
	<meta name="description" content="', !empty($context['meta_description']) ? $context['meta_description'] : $context['page_title_html_safe'], '" />', !empty($context['meta_keywords']) ? '
	<meta name="keywords" content="' . $context['meta_keywords'] . '" />' : '', '
	<meta name="author" content="teknorom@teknoromi.com, teknoromi" />
	<title>', $context['page_title_html_safe'], '</title>';

	// Please don't index these Mr Robot.
	if (!empty($context['robot_no_index']))
		echo '
	<meta name="robots" content="noindex" />';

	// Present a canonical url for search engines to prevent duplicate content in their indices.
	if (!empty($context['canonical_url']))
		echo '
	<link rel="canonical" href="', $context['canonical_url'], '" />';

	// Show all the relative links, such as help, search, contents, and the like.
	echo '
	<link rel="help" href="', $scripturl, '?action=help" />
	<link rel="contents" href="', $scripturl, '" />', ($context['allow_search'] ? '
	<link rel="search" href="' . $scripturl . '?action=search" />' : '');

	// If RSS feeds are enabled, advertise the presence of one.
	if (!empty($modSettings['xmlnews_enable']) && (!empty($modSettings['allow_guestAccess']) || $context['user']['is_logged']))
		echo '
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['rss'], '" href="', $scripturl, '?type=rss2;action=.xml;limit=25" />
	<link rel="alternate" type="application/rss+xml" title="', $context['forum_name_html_safe'], ' - ', $txt['atom'], '" href="', $scripturl, '?type=atom;action=.xml;limit=25" />';

	// If we're viewing a topic, these should be the previous and next topics, respectively.
	if (!empty($context['links']['next']))
	{
		echo '
	<link rel="next" href="', $context['links']['next'], '" />';
	}

	if (!empty($context['links']['prev']))
	{
		echo '
	<link rel="prev" href="', $context['links']['prev'], '" />';
	}

	// If we're in a board, or a topic for that matter, the index will be the board's index.
	if (!empty($context['current_board']))
		echo '
	<link rel="index" href="', $scripturl, '?board=', $context['current_board'], '.0" />';


	

	// Output any remaining HTML headers. (from mods, maybe?)
	echo $context['html_headers'];
	echo '
</head>
<body id="', $context['browser_body_id'], '" class="action_', !empty($context['current_action']) ? $context['current_action'] : (!empty($context['current_board']) ?
		'messageindex' : (!empty($context['current_topic']) ? 'display' : 'home')), !empty($context['current_board']) ? ' board_' . $context['current_board'] : '', '">';
}

function template_body_above()
{
	global $context, $settings, $scripturl, $txt, $modSettings;

	// Wrapper div now echoes permanently for better layout options. h1 a is now target for "Go up" links.
	echo '
	<div id="top_section">

		<div class="frame">';

	// If the user is logged in, display some things that might be useful.
	if ($context['user']['is_logged'])
	{
		// Firstly, the user's menu
		echo '
			<ul class="floatleft" id="top_info">
				<li>
					<a href="', $scripturl, '?action=profile"', !empty($context['self_profile']) ? ' class="active"' : '', ' id="profile_menu_top" onclick="return false;">', $context['user']['name'], ' &#9660;</a>
					<div id="profile_menu" class="top_menu"></div>
				</li>';

		// Secondly, PMs if we're doing them
		if ($context['allow_pm'])
		{
			echo '
				<li>
					<a href="', $scripturl, '?action=pm"', !empty($context['self_pm']) ? ' class="active"' : '', ' id="pm_menu_top">', $txt['pm_short'], !empty($context['user']['unread_messages']) ? ' <span class="amt">' . $context['user']['unread_messages'] . '</span>' : '', '</a>
					<div id="pm_menu" class="top_menu"></div>
				</li>';
		}

		// Thirdly, alerts
		echo '
				<li>
					<a href="', $scripturl, '?action=alerts"', !empty($context['self_alerts']) ? ' class="active"' : '', ' id="alerts_menu_top">', $txt['alerts'], !empty($context['user']['alerts']) ? ' <span class="amt">' . $context['user']['alerts'] . '</span>' : '', '</a>
					<div id="alerts_menu" class="top_menu"></div>
				</li>';

		// And now we're done.
		echo '
			</ul>';
	}
	// Otherwise they're a guest. Ask them to either register or login.
	else
		echo '
			<ul class="floatleft">
				<li>', sprintf($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest'], $txt['guest_title'], $scripturl . '?action=login'), '</li>
			</ul>';

	if ($context['allow_search'])
	{
		echo '				
			<form id="search_form" class="floatright" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
				<input type="text" name="search" value="" class="input_text" />&nbsp;';

		// Using the quick search dropdown?
		if (!empty($modSettings['search_dropdown']))
		{
			$selected = !empty($context['current_topic']) ? 'current_topic' : (!empty($context['current_board']) ? 'current_board' : 'all');

			echo '
				<select name="search_selection">
					<option value="all"', ($selected == 'all' ? ' selected="selected"' : ''), '>', $txt['search_entireforum'], ' </option>';

			// Can't limit it to a specific topic if we are not in one
			if (!empty($context['current_topic']))
				echo '
					<option value="topic"', ($selected == 'current_topic' ? ' selected="selected"' : ''), '>', $txt['search_thistopic'], '</option>';

		// Can't limit it to a specific board if we are not in one
		if (!empty($context['current_board']))
			echo '
					<option value="board"', ($selected == 'current_board' ? ' selected="selected"' : ''), '>', $txt['search_thisbrd'], '</option>';
			echo '
					<option value="members"', ($selected == 'members' ? ' selected="selected"' : ''), '>', $txt['search_members'], ' </option>
				</select>';
		}

		// Search within current topic?
		if (!empty($context['current_topic']))
			echo '
				<input type="hidden" name="', (!empty($modSettings['search_dropdown']) ? 'sd_topic' : 'topic'), '" value="', $context['current_topic'], '" />';
		// If we're on a certain board, limit it to this board ;).
		elseif (!empty($context['current_board']))
			echo '
				<input type="hidden" name="', (!empty($modSettings['search_dropdown']) ? 'sd_brd[' : 'brd['), $context['current_board'], ']"', ' value="', $context['current_board'], '" />';

		echo '
				<input type="submit" name="search2" value="', $txt['search'], '" class="button_submit" />
				<input type="hidden" name="advanced" value="0" />
			</form>';
	}

	echo '
		</div>
	</div>';

	echo '
	<div id="header">
		<div class="frame">
			<h1 class="forumtitle">
				<a id="top" href="', $scripturl, '">', empty($context['header_logo_url_html_safe']) ? $context['forum_name'] : '<img src="' . $context['header_logo_url_html_safe'] . '" alt="' . $context['forum_name'] . '" />', '</a>
			</h1>';

	echo '
			', empty($settings['site_slogan']) ? '<img id="smflogo" src="' . $settings['images_url'] . '/smflogo.png" alt="' . $context['forum_name'] . '" title="' . $context['forum_name'] . '" />' : '<div id="siteslogan" class="floatright">' . $settings['site_slogan'] . '</div>', '';

	echo'<div class="teknosocial">  
       <ul class="tekno_social"><li><a class="twitter" href="https://twitter.com/teknoromi" target="_blank"></a></li><li><a class="gplus" href="https://plus.google.com/+Teknoromi" target="_blank"></a></li><li><a class="facebook" href="https://www.facebook.com/teknoromi" target="_blank"><span></span></a></li><li><a class="youtube" href="http://www.youtube.com/user/osmandatan/videos" target="_blank"></a></li><li><a class="rss" href="http://teknoromi.com/index.php?type=atom;action=.xml" target="_blank"></a></li>
       </ul>   
        </div>
		</div>
	</div>
	<div id="wrapper">
		<div id="upper_section">
			<div id="inner_section">
				<div id="inner_wrap">
					<div class="user">';

	// Otherwise they're a guest - this time ask them to either register or login - lazy bums...
	if (!empty($context['show_login_bar']))
	{
		echo '
						<script src="', $settings['default_theme_url'], '/scripts/sha1.js"></script>
						<form id="guest_form" action="', $scripturl, '?action=login2;quicklogin" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\', \'' . (!empty($context['login_token']) ? $context['login_token'] : '') . '\');"' : '', '>
							<input type="text" name="user" size="10" class="input_text" />
							<input type="password" name="passwrd" size="10" class="input_password" />
							<select name="cookielength">
								<option value="60">', $txt['one_hour'], '</option>
								<option value="1440">', $txt['one_day'], '</option>
								<option value="10080">', $txt['one_week'], '</option>
								<option value="43200">', $txt['one_month'], '</option>
								<option value="-1" selected="selected">', $txt['forever'], '</option>
							</select>
							<input type="submit" value="', $txt['login'], '" class="button_submit" />
							<div>', $txt['quick_login_dec'], '</div>';

		if (!empty($modSettings['enableOpenID']))
			echo '
							<br /><input type="text" name="openid_identifier" size="25" class="input_text openid_login" />';

		echo '
							<input type="hidden" name="hash_passwrd" value="" />
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							<input type="hidden" name="', $context['login_token_var'], '" value="', $context['login_token'], '" />
						</form>';
	}
	else
	{
		echo '
						', $context['current_time'];
	}

	echo'
					</div>';
	// Show a random news item? (or you could pick one from news_lines...)
	if (!empty($settings['enable_news']) && !empty($context['random_news_line']))
		echo '
					<div class="news">
						<h2>', $txt['news'], ': </h2>
						<p>', $context['random_news_line'], '</p>
					</div>';

	echo '
					<hr class="clear" />
				</div>';

	// Show the menu here, according to the menu sub template, followed by the navigation tree.
	template_menu();

	theme_linktree();

	echo '
			</div>
		</div>';

	// The main content should go here.
	echo '
		<div id="content_section">
			<div id="main_content_section">';
}

function template_body_below()
{
	global $context, $txt;

	echo '
			</div>
		</div>
	</div>';

	// Show the XHTML, RSS and WAP2 links, as well as the copyright.
	// Footer is now full-width by default. Frame inside it will match theme wrapper width automatically.
	echo '
	<div id="footer_section">
		<div class="frame">';

	// There is now a global "Go to top" link at the right.
		echo '
			<a href="#top_section" id="bot" class="go_up">', $txt['go_up'], '</a>
			<ul class="reset">
				<li class="copyright">', theme_copyright(), '<a href="', $scripturl, '?action=sitemap"><span>', $txt['sitemap'] ,'</span></a></li>

			</ul>';

	// Show the load time?
	if ($context['show_load_time'])
		echo '
			<p>', sprintf($txt['page_created_full'], $context['load_time'], $context['load_queries']), '</p>';

	echo '
		</div>
	</div>	';


}

function template_html_below()
{
	// load in any javascipt that could be defered to the end of the page
	template_javascript(true);

	echo '

	
</body>
</html>';
}

/**
 * Show a linktree. This is that thing that shows "My Community | General Category | General Discussion"..
 * @param bool $force_show = false
 */
function theme_linktree($force_show = false)
{
	global $context, $settings, $shown_linktree, $scripturl, $txt;

	// If linktree is empty, just return - also allow an override.
	if (empty($context['linktree']) || (!empty($context['dont_default_linktree']) && !$force_show))
		return;

	echo '
				<div class="navigate_section">
					<ul>';

	if ($context['user']['is_logged'])
	echo '              	
						<li class="unread_links">
							<a href="', $scripturl, '?action=unread" title="', $txt['unread_since_visit'], '">', $txt['view_unread_category'], '</a>
							<a href="', $scripturl, '?action=unreadreplies" title="', $txt['show_unread_replies'], '">', $txt['unread_replies'], '</a>
						</li>';

	// Each tree item has a URL and name. Some may have extra_before and extra_after.
	foreach ($context['linktree'] as $link_num => $tree)
	{
		echo '
						<li', ($link_num == count($context['linktree']) - 1) ? ' class="last"' : '', '>';

		// Don't show a separator for the first one.
		// Better here. Always points to the next level when the linktree breaks to a second line.
		// Picked a better looking HTML entity, and added support for RTL plus a span for styling.
		if ($link_num != 0)
			echo '
							<span class="dividers">', $context['right_to_left'] ? ' &#9668; ' : ' &#9658; ', '</span>';

		// Show something before the link?
		if (isset($tree['extra_before']))
			echo $tree['extra_before'], ' ';

		// Show the link, including a URL if it should have one.
		echo $settings['linktree_link'] && isset($tree['url']) ? '
							<a href="' . $tree['url'] . '"><span>' . $tree['name'] . '</span></a>' : '<span>' . $tree['name'] . '</span>';

		// Show something after the link...?
		if (isset($tree['extra_after']))
			echo ' ', $tree['extra_after'];

		echo '
						</li>';
	}

	echo '
					</ul>
				</div>';

	$shown_linktree = true;
}

/**
 * Show the menu up top. Something like [home] [help] [profile] [logout]...
 */
function template_menu()
{
	global $context;

	echo '
				<div id="main_menu">
					<ul class="dropmenu" id="menu_nav">';

	// Note: Menu markup has been cleaned up to remove unnecessary spans and classes.
	foreach ($context['menu_buttons'] as $act => $button)
	{
		echo '
						<li id="button_', $act, '"', !empty($button['sub_buttons']) ? ' class="subsections"' :'', '>
							<a', $button['active_button'] ? ' class="active"' : '', ' href="', $button['href'], '"', isset($button['target']) ? ' target="' . $button['target'] . '"' : '', '>
								', $button['title'], '
							</a>';
		if (!empty($button['sub_buttons']))
		{
			echo '
							<ul>';

			foreach ($button['sub_buttons'] as $childbutton)
			{
				echo '
								<li', !empty($childbutton['sub_buttons']) ? ' class="subsections"' :'', '>
									<a href="', $childbutton['href'], '"' , isset($childbutton['target']) ? ' target="' . $childbutton['target'] . '"' : '', '>
										', $childbutton['title'], '
									</a>';
				// 3rd level menus :)
				if (!empty($childbutton['sub_buttons']))
				{
					echo '
									<ul>';

					foreach ($childbutton['sub_buttons'] as $grandchildbutton)
						echo '
										<li>
											<a href="', $grandchildbutton['href'], '"' , isset($grandchildbutton['target']) ? ' target="' . $grandchildbutton['target'] . '"' : '', '>
												', $grandchildbutton['title'], '
											</a>
										</li>';

					echo '
									</ul>';
				}

				echo '
								</li>';
			}
				echo '
							</ul>';
		}
		echo '
						</li>';
	}

	echo '
					</ul>
				</div>';
}

/**
 * Generate a strip of buttons.
 * @param array $button_strip
 * @param string $direction = ''
 * @param array $strip_options = array()
 */
function template_button_strip($button_strip, $direction = '', $strip_options = array())
{
	global $context, $txt;

	if (!is_array($strip_options))
		$strip_options = array();

	// List the buttons in reverse order for RTL languages.
	if ($context['right_to_left'])
		$button_strip = array_reverse($button_strip, true);

	// Create the buttons...
	$buttons = array();
	foreach ($button_strip as $key => $value)
	{
		// @todo this check here doesn't make much sense now (from 2.1 on), it should be moved to where the button array is generated
		// Kept for backward compatibility
		if (!isset($value['test']) || !empty($context[$value['test']]))
			$buttons[] = '
				<li><a' . (isset($value['id']) ? ' id="button_strip_' . $value['id'] . '"' : '') . ' class="button_strip_' . $key . (!empty($value['active']) ? ' active' : '') . '" href="' . $value['url'] . '"' . (isset($value['custom']) ? ' ' . $value['custom'] : '') . '><span>' . $txt[$value['text']] . '</span></a></li>';
	}

	// No buttons? No button strip either.
	if (empty($buttons))
		return;

	echo '
		<div class="buttonlist', !empty($direction) ? ' float' . $direction : '', '"', (empty($buttons) ? ' style="display: none;"' : ''), (!empty($strip_options['id']) ? ' id="' . $strip_options['id'] . '"': ''), '>
			<ul>',
				implode('', $buttons), '
			</ul>
		</div>';
}

function template_maint_warning_above()
{
	global $txt, $context, $scripturl;

	echo '
	<div class="errorbox" id="errors">
		<dl>
			<dt>
				<strong id="error_serious">', $txt['forum_in_maintainence'], '</strong>
			</dt>
			<dd class="error" id="error_list">
				', sprintf($txt['maintenance_page'], $scripturl . '?action=admin;area=serversettings;' . $context['session_var'] . '=' . $context['session_id']), '
			</dd>
		</dl>
	</div>';
}

function template_maint_warning_below()
{

}
// Show the latest news, with a template... by board.
function ssi_boardNews1($board = null, $limit = 10, $start = null, $length = 250, $output_method = 'echo')
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
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['num_replies'] . ' ' . ($row['num_replies'] == 1 ? $txt['ssi_comment'] : $txt['ssi_comments']) . '</a>',
			'replies' => $row['num_replies'],
			'comment_href' => !empty($row['locked']) ? '' : $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'],
			'comment_link' => !empty($row['locked']) ? '' : '<a href="' . $scripturl . '?action=post;topic=' . $row['id_topic'] . '.' . $row['num_replies'] . ';last_msg=' . $row['id_last_msg'] . '">' . $txt['ssi_write_comment'] . '</a>',
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

	foreach ($return as $news)
     {
         echo '
            <div style="min-height: 175px;">
				<h3 class="news_header">
					', $news['icon'], '
					<a href="', $news['href'], '">', $news['subject'], '</a>
				</h3>
				<div class="news_timestamp">', $news['time'], ' ', $txt['by'], ' ', $news['poster']['link'], '</div>
                 
                <div  style="min-height: 95px;padding: 1ex 6px;"> <img src="', $news['resim'], '" alt="', $news['subject'], '" class="haber_resmi" />
                 
                 ', temizle($news['body']), ' </div>
 
                <p  style="padding-left:20px;margin-top: 5px;"> ', $news['link'], $news['locked'] ? '' : ' | ' . $news['comment_link'], '</p>
             </div> ';
 
         if (!$news['is_last'])
             echo '
             <hr style="margin: 2ex 0;" width="100%" />';
     }
	 
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
 function ssi_topPoster1($topNumber, $output_method)
{
global $smcFunc, $scripturl, $modSettings, $settings;

// Height and width of avatar
$width = '20px';
$height = '20px';
// Number of top posters displayed
$topNumber = 5;

	// Find the latest poster.
	$request = $smcFunc['db_query']('', '
		SELECT mem.id_member, mem.show_online, mem.real_name, mem.posts, mem.avatar, a.id_attach, a.attachment_type, a.filename
			FROM ({db_prefix}members as mem)
			LEFT JOIN {db_prefix}attachments AS a ON (a.id_member = mem.id_member)
			WHERE show_online = 1
			ORDER BY posts DESC
			LIMIT {int:limit}',
			array('limit' => $topNumber)
		);
		
	$users = array();
	
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$users[] = array(
			'id' => $row['id_member'],
			'name' => $row['real_name'],
			'href' => $scripturl . '?action=profile;u=' . $row['id_member'],
			'link' => '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['real_name'] . '</a>',
			'posts' => $row['posts'],
			'show' => $row['show_online'],
			'avatar' => array(
	    		'image' => empty($row['avatar']) ? ($row['id_attach'] > 0 ? 'src="' . (empty($row['attachment_type']) ? $scripturl . '?action=dlattach;attach=' . $row['id_attach'] . ';type=avatar' : $modSettings['custom_avatar_url'] . '/' . $row['filename']) . '" alt="" border="0" width="'.$width.'" height="'.$height.'" title="'.$row['real_name'].'" />' : '') : (stristr($row['avatar'], 'http://') ? 'src="' . $row['avatar'] . '" alt="" border="0" width="'.$width.'" height="'.$height.'" title="'.$row['real_name'].'" />' : 'src="' . $modSettings['avatar_url'] . '/' . htmlspecialchars($row['avatar']) . '" alt="" border="0" width="'.$width.'" height="'.$height.'" title="'.$row['real_name'].'" />'),
	    	),
		);
	}
	
	$smcFunc['db_free_result']($request);

	// Output our array of users with avatar, posts, and name
		
$i = 0;
$len = count($array);
foreach($users as $user) {
    if ($i == 0) {
				echo'<table style="width: 100%;"><tr><td>';
        	
		echo '',empty($user['avatar']['image']) ? '<a href="'.$user['href'].'" ><img style="float: left;" src="'.$settings['images_url'].'/teknoloji.haber.png" width="'.$width.'" height="'.$height.'" alt="" title="'.$user['name'].'" /></a>	<strong style="margin-left: 5px;">'.$user['link'].'</strong><strong style="float: right;">Posts: '. $user['posts'] .'</strong>' : '<a style="float: left;" href="'.$user['href'].'"><img '.$user['avatar']['image'].'</a><strong style="margin-left: 5px;">'.$user['link'].'</strong><strong style="float: right;">Posts: '. $user['posts'] .'</strong>';
	
		echo'</td></tr></table>';
		
		$i++;
     } else if ($i == 1) {
		 	echo'<table style="width: 100%;"><tr><td>';
    	
		echo '',empty($user['avatar']['image']) ? '<a href="'.$user['href'].'"><img style="float: left;" src="'.$settings['images_url'].'/teknoloji.haber.png" width="'.$width.'" height="'.$height.'" alt="" title="'.$user['name'].'" /></a>	<strong style="margin-left: 5px;">'.$user['link'].'</strong><strong style="float: right;">Posts: '. $user['posts'] .'</strong>' : '<a style="float: left;" href="'.$user['href'].'"><img '.$user['avatar']['image'].'</a>
			<strong style="margin-left: 5px;">'.$user['link'].'</strong><strong style="float: right;">Posts: '. $user['posts'] .'</strong>';

		echo'</td></tr>';
		echo'</table>';
}

}
}
function ssi_topBoards1($num_top = 5, $output_method = 'echo')
{
	global $context, $txt, $scripturl, $user_info, $modSettings, $smcFunc;

	// Find boards with lots of posts.
	$request = $smcFunc['db_query']('', '
		SELECT
			b.name, b.num_topics, b.num_posts, b.id_board,' . (!$user_info['is_guest'] ? ' 1 AS is_read' : '
			(IFNULL(lb.id_msg, 0) >= b.id_last_msg) AS is_read') . '
		FROM {db_prefix}boards AS b
			LEFT JOIN {db_prefix}log_boards AS lb ON (lb.id_board = b.id_board AND lb.id_member = {int:current_member})
		WHERE {query_wanna_see_board}' . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_board}' : '') . '
		ORDER BY b.num_posts DESC
		LIMIT ' . $num_top,
		array(
			'current_member' => $user_info['id'],
			'recycle_board' => (int) $modSettings['recycle_board'],
		)
	);
	$boards = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$boards[] = array(
			'id' => $row['id_board'],
			'num_posts' => $row['num_posts'],
			'num_topics' => $row['num_topics'],
			'name' => $row['name'],
			'new' => empty($row['is_read']),
			'href' => $scripturl . '?board=' . $row['id_board'] . '.0',
			'link' => '<a href="' . $scripturl . '?board=' . $row['id_board'] . '.0">' . $row['name'] . '</a>'
		);
	$smcFunc['db_free_result']($request);

	// If we shouldn't output or have nothing to output, just jump out.
	if ($output_method != 'echo' || empty($boards))
		return $boards;

	echo '
		<table class="ssi_table">
			<tr>
				<th style="text-align: left">', $txt['board'], '</th>
				<th style="text-align: left">', $txt['board_topics'], '</th>
				<th style="text-align: left">', $txt['posts'], '</th>
			</tr>';
	foreach ($boards as $board)
		echo '
			<tr>
				<td>', $board['link'], $board['new'] ? ' <a href="' . $board['href'] . '"><span class="new_posts">' . $txt['new'] . '</span></a>' : '', '</td>
				<td style="text-align: right">', comma_format($board['num_topics']), '</td>
				<td style="text-align: right">', comma_format($board['num_posts']), '</td>
			</tr>';
	echo '
		</table>';
}

// Shows the top topics.
function ssi_topTopics1($type = 'replies', $num_topics = 5, $output_method = 'echo')
{
	global $txt, $scripturl, $modSettings, $smcFunc, $context;

	if ($modSettings['totalMessages'] > 100000)
	{
		// @todo Why don't we use {query(_wanna)_see_board}?
		$request = $smcFunc['db_query']('', '
			SELECT id_topic
			FROM {db_prefix}topics
			WHERE num_' . ($type != 'replies' ? 'views' : 'replies') . ' != 0' . ($modSettings['postmod_active'] ? '
				AND approved = {int:is_approved}' : '') . '
			ORDER BY num_' . ($type != 'replies' ? 'views' : 'replies') . ' DESC
			LIMIT {int:limit}',
			array(
				'is_approved' => 1,
				'limit' => $num_topics > 100 ? ($num_topics + ($num_topics / 2)) : 100,
			)
		);
		$topic_ids = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$topic_ids[] = $row['id_topic'];
		$smcFunc['db_free_result']($request);
	}
	else
		$topic_ids = array();

	$request = $smcFunc['db_query']('', '
		SELECT m.subject, m.id_topic, t.num_views, t.num_replies
		FROM {db_prefix}topics AS t
			INNER JOIN {db_prefix}messages AS m ON (m.id_msg = t.id_first_msg)
			INNER JOIN {db_prefix}boards AS b ON (b.id_board = t.id_board)
		WHERE {query_wanna_see_board}' . ($modSettings['postmod_active'] ? '
			AND t.approved = {int:is_approved}' : '') . (!empty($topic_ids) ? '
			AND t.id_topic IN ({array_int:topic_list})' : '') . (!empty($modSettings['recycle_enable']) && $modSettings['recycle_board'] > 0 ? '
			AND b.id_board != {int:recycle_enable}' : '') . '
		ORDER BY t.num_' . ($type != 'replies' ? 'views' : 'replies') . ' DESC
		LIMIT {int:limit}',
		array(
			'topic_list' => $topic_ids,
			'is_approved' => 1,
			'recycle_enable' => $modSettings['recycle_board'],
			'limit' => $num_topics,
		)
	);
	$topics = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		censorText($row['subject']);

		$topics[] = array(
			'id' => $row['id_topic'],
			'subject' => $row['subject'],
			'num_replies' => $row['num_replies'],
			'num_views' => $row['num_views'],
			'href' => $scripturl . '?topic=' . $row['id_topic'] . '.0',
			'link' => '<a href="' . $scripturl . '?topic=' . $row['id_topic'] . '.0">' . $row['subject'] . '</a>',
		);
	}
	$smcFunc['db_free_result']($request);

	if ($output_method != 'echo' || empty($topics))
		return $topics;

	echo '
		<table class="ssi_table">
			<tr>
				<th style="text-align: left"></th>
				<th style="text-align: left">', $txt['views'], '</th>
				<th style="text-align: left">', $txt['replies'], '</th>
			</tr>';
	foreach ($topics as $topic)
		echo '
			<tr>
				<td style="text-align: left">
					', $topic['link'], '
				</td>
				<td style="text-align: right">', comma_format($topic['num_views']), '</td>
				<td style="text-align: right">', comma_format($topic['num_replies']), '</td>
			</tr>';
	echo '
		</table>';
}

?>
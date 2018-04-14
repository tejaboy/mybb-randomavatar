<?php
/***************************************************************************
 *
 *   RandomAvatar Plugin (/inc/plugins/randomavatar.php)
 *	 Author: jacktheking (aka inTech https://keybase.io/intech)
 *   Copyright: © 2016 jacktheking
 *   
 *   Website: https://keybase.io/intech
 *
 *   A simple plugin that will select and give a random avatar for newly registred user.
 *
 ***************************************************************************/
 
/****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("member_do_register_end", "randomavatar_register");
$plugins->add_hook("admin_user_users_add_commit", "randomavatar_register");

function randomavatar_info()
{
	return array(
		"name"			=> "RandomAvatar",
		"description"	=> "A simple plugin that will select and give a random avatar for newly registred user.",
		"website"		=> "http://community.mybb.com/mods.php?action=view&pid=173",
		"author"		=> "inTech",
		"authorsite"	=> "https://keybase.io/intech",
		"version"		=> "1.1",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}

function randomavatar_activate()
{
	global $db, $mybb;
	$randomavatar_setting = array (
		'gid'	=> NULL,
		'name'	=> 'randomavatar',
		'title'	=> 'RandomAvatar',
		'description'	=> 'Setting for the RandomAvatar plugin.',
		'disporder'	=> 1,
		'isdefault'	=> 0,
	);
	$db->insert_query('settinggroups', $randomavatar_setting);
	$gid = $db->insert_id();
	
	$randomavatar_path = array (
		'sid'	=> NULL,
		'name'	=> 'randomavatar_path',
		'title'	=> 'The directory (path) you want the plugin to pick an avatar from. Only internal directory is allowed. Start from root.',
		'optionscode'	=> 'text',
		'value'	=> '',
		'disporder'	=> 2,
		'gid'	=> intval($gid),
	);
	$db->insert_query('settings', $randomavatar_path);
	rebuild_settings();
}

function randomavatar_deactivate()
{
	global $db, $mybb;
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE `name` IN ('randomavatar_path')");
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE `name` = 'randomavatar'");
	rebuild_settings();
}

function getAvatars($path)
{
	$avatars = array();
	if ($img_dir = @opendir($path)) {
		while(false!==($img_file = readdir($img_dir))) {
			if (preg_match("/(\.gif|\.jpg|\.png)$/", $img_file)) {
				$avatars[] = $img_file;
			}
		}
		closedir($img_dir);
	}
	return $avatars;
}

function getRandomFromArray($array)
{
	$number = array_rand($array);
	return $array[$number];
}

function randomavatar_register()
{
	global $mybb, $db, $user_info;
	$path = $mybb->settings['randomavatar_path'];
	$avatarsList = getAvatars(MYBB_ROOT . $path);
	$img = getRandomFromArray($avatarsList);
	$insertavatar = array('avatar' => $path . '/' . $img);
	$db->update_query('users', $insertavatar, 'uid = ' . $user_info['uid']);
}
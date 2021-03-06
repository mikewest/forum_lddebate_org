<?php
/** 
*
* acp_add_user [English]
*
* @author David Lewis (Highway of Life) http://phpbbacademy.com
*
* @package acp
* @version $Id: info_acp_add_user_mod.php 31M 2007-08-05 01:14:00Z (local) $
* @copyright (c) 2007 Star Trek Guide Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_ACCOUNT_ADDED'			=> 'The user account has been created. The user may now login with the username and password sent to the email address you provided.',
	'ACP_ACCOUNT_INACTIVE'		=> 'The user account has been created. However, the forum settings require the user to activate their account.<br />An activation key has been sent to the email address you provided for the user.',
	'ACP_ACCOUNT_INACTIVE_ADMIN'=> 'The account has been created. However, the forum settings require account activation by an administrator.<br />An email has been sent to the Administrators and the user will be informed when their account has been activated',
	'ACP_ADD_USER'				=> 'Add User',
	'ACP_ADMIN_ACTIVATE'		=> 'An email will be dispatched to an Administrator for account activation, alternatively you may check the activate account box below to activate the account instantly once created. The user will receive an email containing account login details.',
	'ACP_EMAIL_ACTIVATE'		=> 'Once the account has been created, The user will receive an email containing an activation link to activate the account.',
	'ACP_INSTANT_ACTIVATE'		=> 'The Account will be activated instantly. The user will recieve an email with account login details.',
	
	'ADD_USER'					=> 'Add User',
	'ADD_USER_EXPLAIN'			=> 'Create a new user account. If your activation settings are to Admin Activativation only, you will have the option to activate the user instantly.',
	'ADD_USER_MOD_INSTALLED'	=> 'Add User MOD version %s installed<br />Please be sure to assign <em>user_add</em> admin permissions for users who you want to be able to access this module.',
	'ADD_USER_MOD_UPDATED'		=> 'Add User MOD updated to version %s',
	'ADMIN_ACTIVATE'			=> 'Activate user account',
	
	'EDIT_USER_GROUPS'			=> '%sClick here to edit the usergroups for this user%s',
	
	'CONTINUE_EDIT_USER'		=> '%1$sClick here to the manage %2$s’s profile%3$s', // e.g.: Click here to edit Joe’s profile.
	'LOG_USER_ADDED'			=> '<strong>New user created</strong><br />» %s',
	
	'acl_a_add_user'			=> array('lang' => 'Can create new user accounts', 'cat' => 'user_group'),
));

?>
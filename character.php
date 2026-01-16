<?php

/**
 * MyBB 1.8 RPG "Veins & Tides" - Erweiterung: Character Control Panel
 * Copyright 2025 Julia Roloff, All Rights Reserved
 *
 * Website: http://www.veins-and-tides.de
 * License: https://www.mybb.com/about/license
 *
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'character.php');

$templatelist = "character, character_nav, character_nav_pages, character_pages";

require_once "./global.php";
require_once "./inc/functions_character.php";
$lang->load('character');
$lang->load('member');
$lang->load('usercp');

$uid = $mybb->user['uid'];
$mybb->input['action'] = $mybb->get_input('action');

add_breadcrumb($lang->character, "character.php");

character_menu();

$plugins->run_hooks("character_start");

if(!$mybb->input['action']) {
    eval("\$page = \"".$templates->get("character")."\";");
	output_page($page);
}

$query = $db->simple_select("character_pages", "*", "", ["order_by" => "sort", "order_dir" => "ASC"]);
while($page = $db->fetch_array($query)) {
	if($mybb->input['action'] == $page['action']) {
        add_breadcrumb($page['name']);
		$user = $mybb->user;
		$requiredfields = '';
        $mybb->input['profile_fields'] = $mybb->get_input('profile_fields', MyBB::INPUT_ARRAY);
        $pfcache = $cache->read('profilefields');
        if(is_array($pfcache)) {
            foreach($pfcache as $profilefield) {
                $check = "";
                $check = $db->fetch_field($db->simple_select("character_pages_fields", "cpfid", "cpid = '{$page['cpid']}' AND fid = '{$profilefield['fid']}'"), "cpfid");
                if(!is_member($profilefield['editableby']) || empty($check))
                {
                    continue;
                }
                $userfield = $code = $select = $val = $options = $expoptions = $useropts = '';
                $seloptions = [];
                $profilefield['type'] = htmlspecialchars_uni($profilefield['type']);
                $profilefield['name'] = htmlspecialchars_uni($profilefield['name']);
                $profilefield['description'] = htmlspecialchars_uni($profilefield['description']);
                $thing = explode("\n", $profilefield['type'], 2);
                $type = $thing[0];
                if(isset($thing[1]))
                {
                    $options = $thing[1];
                }
                else
                {
                    $options = [];
                }
                $field = "fid{$profilefield['fid']}";
                $userfield = $user[$field];
                $field = "fid{$profilefield['fid']}";
                if($errors)
                {
                    if(!isset($mybb->input['profile_fields'][$field]))
                    {
                        $mybb->input['profile_fields'][$field] = '';
                    }
                    $userfield = $mybb->input['profile_fields'][$field];
                }
                else
                {
                    $userfield = $user[$field];
                }
                if($type == "multiselect")
                {
                    $useropts = explode("\n", $userfield);
                    if(is_array($useropts))
                    {
                        foreach($useropts as $key => $val)
                        {
                            $val = htmlspecialchars_uni($val);
                            $seloptions[$val] = $val;
                        }
                    }
                    $expoptions = explode("\n", $options);
                    if(is_array($expoptions))
                    {
                        foreach($expoptions as $key => $val)
                        {
                            $val = trim($val);
                            $val = str_replace("\n", "\\n", $val);
    
                            $sel = "";
                            if(isset($seloptions[$val]) && $val == $seloptions[$val])
                            {
                                $sel = " selected=\"selected\"";
                            }
    
                            eval("\$select .= \"".$templates->get("usercp_profile_profilefields_select_option")."\";");
                        }
                        if(!$profilefield['length'])
                        {
                            $profilefield['length'] = 3;
                        }
    
                        eval("\$code = \"".$templates->get("usercp_profile_profilefields_multiselect")."\";");
                    }
                }
                elseif($type == "select")
                {
                    $expoptions = explode("\n", $options);
                    if(is_array($expoptions))
                    {
                        foreach($expoptions as $key => $val)
                        {
                            $val = trim($val);
                            $val = str_replace("\n", "\\n", $val);
                            $sel = "";
                            if($val == htmlspecialchars_uni($userfield))
                            {
                                $sel = " selected=\"selected\"";
                            }
    
                            eval("\$select .= \"".$templates->get("usercp_profile_profilefields_select_option")."\";");
                        }
                        if(!$profilefield['length'])
                        {
                            $profilefield['length'] = 1;
                        }
    
                        eval("\$code = \"".$templates->get("usercp_profile_profilefields_select")."\";");
                    }
                }
                elseif($type == "radio")
                {
                    $userfield = htmlspecialchars_uni($userfield);
                    $expoptions = explode("\n", $options);
                    if(is_array($expoptions))
                    {
                        foreach($expoptions as $key => $val)
                        {
                            $checked = "";
                            if($val == $userfield)
                            {
                                $checked = " checked=\"checked\"";
                            }
    
                            eval("\$code .= \"".$templates->get("usercp_profile_profilefields_radio")."\";");
                        }
                    }
                }
                elseif($type == "checkbox")
                {
                    $userfield = htmlspecialchars_uni($userfield);
                    if($errors)
                    {
                        $useropts = $userfield;
                    }
                    else
                    {
                        $useropts = explode("\n", $userfield);
                    }
                    if(is_array($useropts))
                    {
                        foreach($useropts as $key => $val)
                        {
                            $seloptions[$val] = $val;
                        }
                    }
                    $expoptions = explode("\n", $options);
                    if(is_array($expoptions))
                    {
                        foreach($expoptions as $key => $val)
                        {
                            $checked = "";
                            if(isset($seloptions[$val]) && $val == $seloptions[$val])
                            {
                                $checked = " checked=\"checked\"";
                            }
    
                            eval("\$code .= \"".$templates->get("usercp_profile_profilefields_checkbox")."\";");
                        }
                    }
                }
                elseif($type == "textarea")
                {
                    $value = htmlspecialchars_uni($userfield);
                    eval("\$code = \"".$templates->get("usercp_profile_profilefields_textarea")."\";");
                }
                else
                {
                    $value = htmlspecialchars_uni($userfield);
                    $maxlength = "";
                    if($profilefield['maxlength'] > 0)
                    {
                        $maxlength = " maxlength=\"{$profilefield['maxlength']}\"";
                    }
    
                    eval("\$code = \"".$templates->get("usercp_profile_profilefields_text")."\";");
                }
    
                eval("\$requiredfields .= \"".$templates->get("usercp_profile_customfield")."\";");

                $altbg = alt_trow();
            }
        }
	
		eval("\$page_tpl = \"".$templates->get("character_page")."\";");
		output_page($page_tpl);
	 }

	 if($mybb->input['action'] == "do_{$page['action']}" && $mybb->request_method == "post")
	 {
		 // Verify incoming POST request
		 verify_post_check($mybb->get_input('my_post_key'));
	 
		 $user = [];
		 $fields = $mybb->get_input('profile_fields', MyBB::INPUT_ARRAY);
		 $db->update_query("userfields", $fields, "ufid = '{$mybb->user['uid']}'");
		 redirect("character.php?action={$page['action']}", $lang->redirect_profileupdated);
	 }
}

$query = $db->simple_select("character_pages_code", "*", "", ["order_by" => "sort", "order_dir" => "ASC"]);
while($page = $db->fetch_array($query)) {
        if($mybb->input['action'] == $page['action']) {
            add_breadcrumb($page['name']);
            eval($page['code']);
    	    eval("\$page_tpl = \"".$templates->get($page['template_name'])."\";");
		    output_page($page_tpl);
        }
}

$plugins->run_hooks("character_end");
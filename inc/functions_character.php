<?php

/**
 * MyBB 1.8 RPG "Veins & Tides" - Erweiterung: Character Control Panel
 * Copyright 2025 Julia Roloff, All Rights Reserved
 *
 * Website: http://www.veins-and-tides.de
 * License: https://www.mybb.com/about/license
 *
 */

/**
 * Constructs the character control panel menu
 *
 */

function character_menu()
{
	global $mybb, $templates, $plugins, $lang, $character_nav, $characternavbit;

	$lang->load("character");

    $plugins->add_hook("character_menu", "character_menu_pages", 0);
    $plugins->add_hook("character_menu", "character_menu_plugins", 2);
    $plugins->add_hook("character_menu", "character_menu_code", 1);
	$plugins->run_hooks("character_menu");
    global $usercpmenu;
	eval("\$character_nav = \"".$templates->get("character_nav")."\";");
	$plugins->run_hooks("character_menu_built");
}

/**
 * Constructs character control panel menu for pages
 *
 */
function character_menu_pages()
{
    global $db, $mybb, $lang, $templates, $characternavbit;

    $cat = 1;
    $characternavbit = "";
    $title = $lang->character_nav_title_pages;
    eval("\$characternavbit .= \"".$templates->get("character_nav_title")."\";");
    $query = $db->simple_select("character_pages", "*", "", ["order_by" => "sort", "order_dir" => "ASC"]);
    while($cpage = $db->fetch_array($query)) {
	    $linkname = ucfirst($cpage['name']);
            if(is_member($cpage['gids'])) {
	        eval("\$characternavbit .= \"".$templates->get("character_nav_pages")."\";");
            } else { $characternavbit .= ""; }
    }
    $plugins->run_hooks("character_menu_pages");
    character_build_custom_nav($cat);
}

function character_menu_plugins()
{
    global $db, $mybb, $lang, $templates, $characternavbit;

    $cat = 2;
    $title = $lang->character_nav_title_plugins;
    eval("\$characternavbit .= \"".$templates->get("character_nav_title")."\";");
    $plugins->run_hooks("character_menu_plugins");
    character_build_custom_nav($cat);
}

function character_menu_code()
{
    global $db, $mybb, $lang, $templates, $characternavbit;

    $cat = 3;
    $title = $lang->character_nav_title_code;
    eval("\$characternavbit .= \"".$templates->get("character_nav_title")."\";");
    $query = $db->simple_select("character_pages_code", "*", "", ["order_by" => "sort", "order_dir" => "ASC"]);
    while($cpage = $db->fetch_array($query)) {
	    $linkname = ucfirst($cpage['name']);
            if(is_member($cpage['gids'])) {
	        eval("\$characternavbit .= \"".$templates->get("character_nav_pages")."\";");
            } else { $characternavbit .= ""; }
    }
    $plugins->run_hooks("character_menu_code");
    character_build_custom_nav($cat);
}

function character_build_custom_nav($cat)
{     
    global $db, $mybb, $templates, $characternavbit;
    $query = $db->simple_select("character_nav", "*", "cat = {$cat}", ["order_by" => "cnid", "order_dir" => "ASC"]);
    while($cpage = $db->fetch_array($query)) {
       $linkname = ucfirst($cpage['name']); 
        if(is_member($cpage['gids'])) {
        eval("\$characternavbit .= \"".$templates->get("character_nav_custom")."\";");
        } else { $characternavbit .= ""; }
    }
}
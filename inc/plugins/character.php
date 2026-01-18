<?php

if (!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function character_get_plugins_list(string $hook): array
{
    $list = [];

    foreach (glob(MYBB_ROOT . "inc/plugins/*.php") as $path) {
        $file = basename($path);
        $codename = substr($file, 0, -4);
        $contents = file_get_contents($path);
        if(preg_match('/\$plugins\s*->\s*add_hook\s*\(\s*[\'"]'
             . preg_quote($hook, '/')
             . '[\'"]\s*,/i',$contents)) {
            $list[$codename] = [
                'file' => $file,
                'codename' => $codename,
            ];
        }
    }
    ksort($list);
    return $list;
}

function set_character_hook(string $codename, string $oldHook, string $newHook)
{
    $path = MYBB_ROOT . 'inc/plugins/' . $codename . '.php';
    if (!is_readable($path) || !is_writable($path)) {
        return;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return;
    }

    $pattern = '/(\$plugins\s*->\s*add_hook\s*\(\s*[\'"])'
                . preg_quote($oldHook, '/')
                . '([\'"]\s*,\s*[\'"][^\'"]+[\'"]\s*\))/i';

    $replacement = '$1' . $newHook . '$2';

    $newContents = preg_replace($pattern, $replacement, $contents, -1, $replaced);

    if ($replaced > 0) {
        file_put_contents($path, $newContents);
    }
}

function set_character_navigation(string $codename)
{
    $path = MYBB_ROOT . 'inc/plugins/' . $codename . '.php';
    if (!is_readable($path) || !is_writable($path)) {
        return;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return;
    }

    $replacements = [
        // $usercpnav → $character_nav
        '/(?<!\$)\$usercpnav\b/'   => '$character_nav',

        // $usercpmenu → $characternavbit
        '/(?<!\$)\$usercpmenu\b/'  => '$characternavbit',
    ];

    $count = 0;
    $newContents = preg_replace(
        array_keys($replacements),
        array_values($replacements),
        $contents,
        -1,
        $count
    );

    if ($count > 0) {
        file_put_contents($path, $newContents);
    }
}

function set_usercp_navigation(string $codename)
{
    $path = MYBB_ROOT . 'inc/plugins/' . $codename . '.php';
    if (!is_readable($path) || !is_writable($path)) {
        return;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        return;
    }

    $replacements = [
        // $usercpnav → $character_nav
        '/(?<!\$)\$character_nav\b/'   => '$usercpnav',

        // $usercpmenu → $characternavbit
        '/(?<!\$)\$characternavbit\b/'  => '$usercpmenu',
    ];

    $count = 0;
    $newContents = preg_replace(
        array_keys($replacements),
        array_values($replacements),
        $contents,
        -1,
        $count
    );

    if ($count > 0) {
        file_put_contents($path, $newContents);
    }
}

function character_info()
{
	global $lang;
	$lang->load('character');
	
	return [
		'name' => $lang->character_name_acp,
		'description' => $lang->character_desc_acp,
		'author' => "Julia Roloff",
		'authorsite' => "https://the-empyrean.de",
		'version' => "1.0",
		'compatibility' => "18*"
	];
}

function character_install()
{
	global $db, $lang;
    $lang->load("character");

	$db->write_query("
    	CREATE TABLE " . TABLE_PREFIX . "character_pages (
        `cpid` int(11)  NOT NULL auto_increment, 
        `name` varchar(500) CHARACTER SET utf8 NOT NULL,
        `action` varchar(500) CHARACTER SET utf8 NOT NULL,
        `description` longtext CHARACTER SET utf8 NOT NULL,
        `sort` int(11)  NOT NULL,
        `gids` varchar(500)  NOT NULL,
        PRIMARY KEY (`cpid`)
    )
     ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1
    ");

	$db->write_query("
    	CREATE TABLE " . TABLE_PREFIX . "character_pages_code (
        `cpcid` int(11)  NOT NULL auto_increment, 
        `name` varchar(500) CHARACTER SET utf8 NOT NULL,
        `action` varchar(500) CHARACTER SET utf8 NOT NULL,
        `description` longtext CHARACTER SET utf8 NOT NULL,
        `code` longtext CHARACTER SET utf8 NOT NULL,
        `sort` int(11)  NOT NULL,
        `gids` varchar(500)  NOT NULL,
        `template_name` varchar(500) NOT NULL,
        PRIMARY KEY (`cpcid`)
    )
     ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1
    ");

	$db->write_query("
    	CREATE TABLE " . TABLE_PREFIX . "character_pages_fields (
        `cpfid` int(11)  NOT NULL auto_increment, 
        `cpid` int(11) NOT NULL,
        `fid` int(11) NOT NULL,
        PRIMARY KEY (`cpfid`)
    )
     ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1
    ");

	$db->write_query("
    	CREATE TABLE " . TABLE_PREFIX . "character_nav (
        `cnid` int(11)  NOT NULL auto_increment, 
        `cat` int(11) NOT NULL,
        `name` varchar(500) CHARACTER SET utf8 NOT NULL,
        `link` varchar(500) CHARACTER SET utf8 NOT NULL,
        `gids` varchar(500)  NOT NULL,
        PRIMARY KEY (`cnid`)
    )
     ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1
    ");

    $templategrouparray = [
        'prefix' => 'character',
        'title'  => $lang->character_name_acp,
        'isdefault' => 1
    ];
    $db->insert_query("templategroups", $templategrouparray);

    $character = [
        'title' => 'character',
        'template' => $db->escape_string('<html>
            <head>
            <title>{$lang->character}</title>
            {$headerinclude}
            </head>
            <body>
            {$header}
            <table width="100%" border="0" align="center">
            <tr>
            {$character_nav}
            <td valign="top">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
            <tr>
            <td class="thead" colspan="{$colspan}"><strong>{$lang->character} </strong></td>
            </tr>
            <tr>
            <td class="trow2">{$lang->character_welcome}
            </td>
            </tr>
            </table>
                </td>
                </tr>
                </table>
            {$footer}
            </body>
            </html>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character);

    $character_header = [
        'title' => 'character_header',
        'template' => $db->escape_string('<li><a href="{$mybb->settings[\'bburl\']}/character.php" class="usercp">{$lang->character}</a></li>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character_header);

    $character_nav = [
        'title' => 'character_nav',
        'template' => $db->escape_string('<td width="15%" valign="top">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
                <tr>
                    <td class="thead"><strong>{$lang->character}</strong></td>
                </tr>
                <tr>
                    <td class="trow1 smalltext"><a href="character.php">{$lang->character_nav_home}</a></td>
                </tr>
                {$characternavbit}
            </table>
            </td>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character_nav);

    $character_nav_custom = [
        'title' => 'character_nav_custom',
        'template' => $db->escape_string('<tr><td class="trow1 smalltext"><a href="{$cpage[\'link\']}">{$linkname}</a></td></tr>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character_nav_custom);

    $character_nav_title = [
        'title' => 'character_nav_title',
        'template' => $db->escape_string('<tr>
            <td class="tcat tcat_menu">
                <div><span class="smalltext"><strong>{$title}</strong></span></div>
            </td>
        </tr>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character_nav_title);

    $character_nav_pages = [
        'title' => 'character_nav_pages',
        'template' => $db->escape_string('<tr><td class="trow1 smalltext"><a href="character.php?action={$cpage[\'action\']}">{$linkname}</a></td></tr>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character_nav_pages);

    $character_nav_title = [
        'title' => 'character_nav_title',
        'template' => $db->escape_string('<tr>
            <td class="tcat tcat_menu">
                <div><span class="smalltext"><strong>{$title}</strong></span></div>
            </td>
        </tr>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character_nav_title);

    $character_page = [
        'title' => 'character_page',
        'template' => $db->escape_string('<html>
            <head>
            <title>{$lang->character} - {$page[\'name\']}</title>
            {$headerinclude}
            </head>
            <body>
            {$header}
            <table width="100%" border="0" align="center">
            <tr>
            {$character_nav}
            <td valign="top">
            <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
            <tr>
            <td class="thead" colspan="{$colspan}"><strong>{$lang->character} - {$page[\'name\']}</strong></td>
            </tr>
            <tr>
            <td class="trow2"><br />{$page[\'description\']}<br /><br />
                                <form action="character.php" method="post" name="input">
                            <input type="hidden" name="my_post_key" value="{$mybb->post_code}" />
            <fieldset class="trow2">
            <legend><strong>{$lang->character_fields}</strong></legend>
            <table cellspacing="0" cellpadding="{$theme[\'tablespace\']}">
            {$requiredfields}
            </table>
            </fieldset>
            {$customfields}<br />
            </td>
            </tr>
            </table>				<br /><div align="center">
                            <input type="hidden" name="action" value="do_{$page[\'action\']}" />
                            <input type="submit" class="button" name="regsubmit" value="{$lang->update_profile}" />
                            </div>
                            </form>
                </td>
                </tr>
                </table>
            {$footer}
            </body>
            </html>'
        ),
        'sid' => '-2',
        'version' => '',
        'dateline' => TIME_NOW
    ];
    $db->insert_query("templates", $character_page);

}

function character_is_installed ()
{
    global $db;

    if ($db->table_exists("character_pages"))
    {
        return true;
    }
    return false;
}

function character_uninstall()
{
    global $db;
    $tables = ["character_pages", "character_pages_fields", "character_pages_code", "character_nav"];
    foreach($tables as $table)
	if ($db->table_exists($table))
	{
		$db->drop_table($table);
	}
    $db->delete_query("templategroups", "prefix = 'character'");
    $db->delete_query("templates", "title LIKE 'character_%'");

    $plugins_list = character_get_plugins_list("character_start");
    foreach($plugins_list as $plugin) {
        $path = MYBB_ROOT."inc/plugins/".$plugin['file'];
        $codename = str_replace(".php", "", $plugin['file']);
        set_character_hook($codename, "character_start", "usercp_start");
        set_usercp_navigation($codename);
        set_character_hook($codename, "character_menu", "usercp_menu");
    }
}

function character_activate()
{
	global $db, $cache;
	require MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("header_welcomeblock_member", "#" . preg_quote('{$usercplink}') . "#i", '{$usercplink} {$header_character}');

}

function character_deactivate()
{
	global $db, $cache;
    include MYBB_ROOT . "/inc/adminfunctions_templates.php";
    find_replace_templatesets("header_welcomeblock_member", "#" . preg_quote('{$header_character}') . "#i", '', 0);

}

$plugins->add_hook("admin_config_menu", "character_admin_config_menu");
$plugins->add_hook("admin_config_action_handler", "character_admin_config_action_handler");
$plugins->add_hook("admin_config_permissions", "character_admin_config_permissions");
$plugins->add_hook("admin_load", "character_admin_load");

function character_admin_config_action_handler(&$actions)
{
	$actions['character'] = ["active" => "character", "file" => "character"];
}


// admin permissions
function character_admin_config_permissions(&$admin_permissions)
{
	global $lang;
	$lang->load('character');
	$admin_permissions['character'] = $lang->character_admin_config_permissions;
	return $admin_permissions;
}

// admin menu
function character_admin_config_menu(&$sub_menu)
{
	global $lang;
	$lang->load('character');

    $sub_menu['35'] = [
        "id"    => "character",
        "title" => $lang->character_admin_config_menu,
        "link"  => "index.php?module=config-character"
    ];
    ksort($sub_menu, SORT_NUMERIC);

}

function build_sub_tabs_character() {
		global $lang;
        $lang->load('character');
			// build tabs
		$sub_tabs['character'] = [
			"title" => $lang->character_admin_overview_character,
			"link" => "index.php?module=config-character",
			"description" => $lang->character_admin_overview_character_desc
		];
 
		$sub_tabs['character_add_pages'] = [
			"title" => $lang->character_admin_add_pages,
			"link" => "index.php?module=config-character&amp;action=add_pages",
			"description" => $lang->character_admin_add_pages_desc
		];

		$sub_tabs['character_code_pages'] = [
			"title" => $lang->character_admin_overview_code,
			"link" => "index.php?module=config-character&action=code",
			"description" => $lang->character_admin_overview_code_desc
		];

		$sub_tabs['character_add_pages_code'] = [
			"title" => $lang->character_admin_add_pages_code,
			"link" => "index.php?module=config-character&amp;action=add_pages&code=1",
			"description" => $lang->character_admin_add_pages_code_desc
		];

		$sub_tabs['character_plugins'] = [
			"title" => $lang->character_admin_plugins,
			"link" => "index.php?module=config-character&amp;action=plugins",
			"description" => $lang->character_admin_plugins_desc
		];

		$sub_tabs['character_plugins_ccp'] = [
			"title" => $lang->character_admin_plugins_ccp,
			"link" => "index.php?module=config-character&amp;action=plugins_ccp",
			"description" => $lang->character_admin_plugins_ccp_desc
		];

		$sub_tabs['character_nav'] = [
			"title" => $lang->character_admin_nav,
			"link" => "index.php?module=config-character&amp;action=nav",
			"description" => $lang->character_admin_nav_desc
		];

		$sub_tabs['character_nav_add'] = [
			"title" => $lang->character_admin_nav_add,
			"link" => "index.php?module=config-character&amp;action=add_nav",
			"description" => $lang->character_admin_nav_add_desc
		];


		return $sub_tabs;
}

// new admin cp panel
function character_admin_load()
{
    global $mybb, $db, $lang, $page, $run_module, $action_file;
    $lang->load('character');

	if ($page->active_action !='character') {
		return false;
	}

	if ($run_module == "config" && $action_file == "character") {
        if($mybb->input['action'] == "") {

            if($mybb->request_method == "post") {
                if(!empty($mybb->input['disporder']))
                {
                    foreach($mybb->input['disporder'] as $cpid => $order)
                    {
                        if(is_numeric($order) && (int)$order >= 0)
                        {
                            $db->update_query("character_pages", ['sort' => (int)$order], "cpid='".(int)$cpid."'");
                        }
                    }
                    flash_message($lang->character_admin_order_updated, 'success');
                    admin_redirect("index.php?module=config-character");
                }
            }

			$page->add_breadcrumb_item($lang->character_admin_manage);
			$page->output_header($mybb->settings['bbname']." - ".$lang->character_admin_manage);

			$sub_tabs = build_sub_tabs_character();

			$page->output_nav_tabs($sub_tabs, "character");

            if(isset($errors)) {
                $page->output_inline_error($errors);
            }

			$form = new Form("index.php?module=config-character", "post");
			$form_container = new FormContainer($lang->character_admin_manage);
			$form_container->output_row_header($lang->character_admin_overview_pages, ['style' => 'width: 35%;']);
			$form_container->output_row_header($lang->character_admin_overview_fields, ['style' => 'width: 35%;']);
			$form_container->output_row_header($lang->character_admin_overview_order, ['style' => 'width: 10%;', 'class' => 'align_center']);
			$form_container->output_row_header($lang->character_admin_overview_options, ['style' => 'width: 20%;']);

			$query = $db->simple_select("character_pages", "*", "", ["order_by" => 'sort', 'order_dir' => 'ASC']);
			while($result = $db->fetch_array($query)) {
                $form_container->output_cell('<strong>'.htmlspecialchars_uni($result['name']).'</strong> (Action: '.$result['action'].')');
                $query2 = $db->query("
                SELECT * FROM ".TABLE_PREFIX."character_pages_fields cpf
                LEFT JOIN ".TABLE_PREFIX."profilefields pf ON cpf.fid = pf.fid
                WHERE cpid = '{$result['cpid']}'
                ORDER by disporder ASC ");
                $str_fields = "";
                while($field = $db->fetch_array($query2)) {
                    $str_fields .= "<li /> {$field['name']} (fid: {$field['fid']}) <small> <a href=\"index.php?module=config-profile_fields&action=edit&fid={$field['fid']}\" target=\"_blank\">{$lang->character_admin_manage_edit}</a> | <a href=\"index.php?module=config-character&amp;action=detach_field&amp;fid={$field['fid']}\" onclick=\"return confirm('{$lang->character_admin_confirmation_detach_field}')\">{$lang->character_admin_detach_field}</a> </small>";
                }
                $form_container->output_cell('<ul>'.$str_fields.'</ul>');
                $form_container->output_cell("<input type=\"number\" name=\"disporder[{$result['cpid']}]\" value=\"{$result['sort']}\" min=\"0\" class=\"text_input align_center\" style=\"width: 80%;\" />", [ "class" => "align_center"]);
                $popup = new PopupMenu("character_{$result['cpid']}", $lang->character_admin_manage_options);
                $popup->add_item(
                    $lang->character_admin_manage_edit,
                    "index.php?module=config-character&amp;action=edit_page&amp;cpid={$result['cpid']}"
                );
                $popup->add_item(
                    $lang->character_admin_manage_delete,
                    "index.php?module=config-character&amp;action=delete_character&amp;cpid={$result['cpid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), ["class" => "align_center"]);
                $form_container->construct_row();
            }

            if($form_container->num_rows() == 0)
            {
                $form_container->output_cell($lang->character_admin_no_pages, ['colspan' => 4]);
                $form_container->construct_row();
            }

			$form_container->end();
            $buttons = [];
            $buttons[] = $form->generate_submit_button($lang->character_admin_update_order);
            $form->output_submit_wrapper($buttons);
			$form->end();
			$page->output_footer();
        }

        if($mybb->input['action'] == "code") {

            if($mybb->request_method == "post") {
                if(!empty($mybb->input['disporder']))
                {
                    foreach($mybb->input['disporder'] as $cpcid => $order)
                    {
                        if(is_numeric($order) && (int)$order >= 0)
                        {
                            $db->update_query("character_pages_code", ['sort' => (int)$order], "cpcid='".(int)$cpcid."'");
                        }
                    }
                    flash_message($lang->character_admin_order_updated, 'success');
                    admin_redirect("index.php?module=config-character&action=code");
                }
            }

			$page->add_breadcrumb_item($lang->character_admin_manage);
			$page->output_header($mybb->settings['bbname']." - ".$lang->character_admin_manage);

			$sub_tabs = build_sub_tabs_character();

			$page->output_nav_tabs($sub_tabs, "character_code_pages");

            if(isset($errors)) {
                $page->output_inline_error($errors);
            }

			$form = new Form("index.php?module=config-character", "post");
			$form_container = new FormContainer($lang->character_admin_manage);
			$form_container->output_row_header($lang->character_admin_overview_pages, ['style' => 'width: 70%;']);
			$form_container->output_row_header($lang->character_admin_overview_order, ['style' => 'width: 10%;', 'class' => 'align_center']);
			$form_container->output_row_header($lang->character_admin_overview_options, ['style' => 'width: 20%;']);

			$query = $db->simple_select("character_pages_code", "*", "", ["order_by" => 'sort', 'order_dir' => 'ASC']);
			while($result = $db->fetch_array($query)) {
                $form_container->output_cell('<strong>'.htmlspecialchars_uni($result['name']).'</strong> (Action: '.$result['action'].')');
                $form_container->output_cell("<input type=\"number\" name=\"disporder[{$result['cpcid']}]\" value=\"{$result['sort']}\" min=\"0\" class=\"text_input align_center\" style=\"width: 80%;\" />", [ "class" => "align_center"]);
                $popup = new PopupMenu("character_{$result['cpcid']}", $lang->character_admin_manage_options);
                $popup->add_item(
                    $lang->character_admin_manage_edit,
                    "index.php?module=config-character&amp;action=edit_page&code=1&amp;cpid={$result['cpcid']}"
                );
                $popup->add_item(
                    $lang->character_admin_manage_delete,
                    "index.php?module=config-character&amp;action=delete_code&amp;cpcid={$result['cpcid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), ["class" => "align_center"]);
                $form_container->construct_row();
            }

            if($form_container->num_rows() == 0)
            {
                $form_container->output_cell($lang->character_admin_no_pages, ['colspan' => 4]);
                $form_container->construct_row();
            }

			$form_container->end();
            $buttons = [];
            $buttons[] = $form->generate_submit_button($lang->character_admin_update_order);
            $form->output_submit_wrapper($buttons);
			$form->end();
			$page->output_footer();
        }

        if($mybb->input['action'] == "nav") {

			$page->add_breadcrumb_item($lang->character_admin_nav);
			$page->output_header($mybb->settings['bbname']." - ".$lang->character_admin_nav);

			$sub_tabs = build_sub_tabs_character();

			$page->output_nav_tabs($sub_tabs, "character_nav");

            if(isset($errors)) {
                $page->output_inline_error($errors);
            }

			$form = new Form("index.php?module=config-character&action=nav", "post");
			$form_container = new FormContainer($lang->character_admin_nav);
			$form_container->output_row_header($lang->character_admin_overview_nav, ['style' => 'width: 60%;']);
			$form_container->output_row_header($lang->character_admin_overview_cat, ['style' => 'width: 20%;', 'class' => 'align_center']);
			$form_container->output_row_header($lang->character_admin_overview_options, ['style' => 'width: 20%;']);

			$query = $db->simple_select("character_nav", "*", "", ["order_by" => 'cnid', 'order_dir' => 'ASC']);
			while($result = $db->fetch_array($query)) {
                $form_container->output_cell('<strong>'.htmlspecialchars_uni($result['name']).'</strong> (URL: '.$result['link'].')');
                $cats = ["1" => $lang->character_nav_title_pages, "2" => $lang->character_nav_title_plugins, "3" => $lang->character_nav_title_code];
                $cat = $result['cat'];
                $form_container->output_cell("{$cats[$cat]}", [ "class" => "align_center"]);
                $popup = new PopupMenu("character_{$result['cnid']}", $lang->character_admin_manage_options);
                $popup->add_item(
                    $lang->character_admin_manage_edit,
                    "index.php?module=config-character&amp;action=edit_nav&amp;cnid={$result['cnid']}"
                );
                $popup->add_item(
                    $lang->character_admin_manage_delete,
                    "index.php?module=config-character&amp;action=delete_nav&amp;cnid={$result['cnid']}"
                    ."&amp;my_post_key={$mybb->post_code}"
                );
                $form_container->output_cell($popup->fetch(), ["class" => "align_center"]);
                $form_container->construct_row();
            }

            if($form_container->num_rows() == 0)
            {
                $form_container->output_cell($lang->character_admin_no_nav, ['colspan' => 3]);
                $form_container->construct_row();
            }

			$form_container->end();
			$form->end();
			$page->output_footer();
        }

		if ($mybb->input['action'] == "add_nav" || $mybb->input['action'] == "edit_nav") {
            $action = $mybb->input['action'];
            $cnid = $mybb->get_input('cnid');
            if ($mybb->request_method == "post") {
                $selected_gids = implode(",", $mybb->get_input('character_nav_groups', MyBB::INPUT_ARRAY));
				if(!$errors) {
					$new_entry = [
						"name" => $db->escape_string($mybb->get_input('character_nav_name')),
                        "link" => $db->escape_string($mybb->get_input('character_nav_link')),
                        "gids" => $selected_gids,
                        "cat" => (int)$mybb->get_input('character_nav_cat')
					];
                    if($action == "add_nav") {
                        $cnid = $db->insert_query("character_nav", $new_entry);
                        $lang_module = $lang->character_admin_nav_added;
                    } else {
                        $db->update_query("character_nav", $new_entry, "cnid = '{$cnid}'");
                        $lang_module = $lang->character_admin_page_edited;                        
                    }                
					$mybb->input['module'] = $lang->character_admin_config_menu;
					$mybb->input['action'] = $lang_module . " ";
					log_admin_action(htmlspecialchars_uni($mybb->get_input('character_nav_name')));
					flash_message($lang_module, 'success');
					admin_redirect("index.php?module=config-character&action=nav");
				}
            }

            $sub_tabs = build_sub_tabs_character();
            $page->add_breadcrumb_item($lang->character_admin_nav_add);
            $page->output_header($mybb->settings['bbname']." - ".$lang->character_admin_nav_add); 
            $page->output_nav_tabs($sub_tabs, "character_nav_add");

            if($action == "add_nav") {
                $form = new Form("index.php?module=config-character&amp;action=add_nav", "post", "", 1);
                $form_container = new FormContainer($lang->character_admin_nav_add);
            }
            else {
                $cnav = $db->fetch_array($db->simple_select("character_nav", "*", "cnid = {$cnid}"));
                $form = new Form("index.php?module=config-character&amp;action=edit_nav&cnid={$cnid}", "post", "", 1); 
			    $form_container = new FormContainer($lang->character_admin_nav_edit);      
            }

			if (isset($errors)) {
				$page->output_inline_error($errors);
			}

			$form_container->output_row(
					$lang->character_admin_add_nav_name.'<em>*</em>',
					$lang->character_admin_add_nav_name_desc,
					$form->generate_text_box('character_nav_name', isset($mybb->input['character_nav_name']) 
                        ? $mybb->input['character_nav_name'] 
                        : (isset($cnav['name']) ? $cnav['name'] : ''))
			);

			$form_container->output_row(
					$lang->character_admin_add_nav_link.'<em>*</em>',
					$lang->character_admin_add_nav_link_desc,
					$form->generate_text_box('character_nav_link', isset($mybb->input['character_nav_link']) 
                        ? $mybb->input['character_nav_link'] 
                        : (isset($cnav['link']) ? $cnav['link'] : ''))
			);

            $groups_options = [];
            $checked_groups = [];
            $query = $db->simple_select("usergroups", "gid, title", "", ["order_by" => "title"]);
            while ($group = $db->fetch_array($query)) {
                $groups_options[$group['gid']] = $group['title'];
                if($action == "edit_nav") {
                    $gids = $db->fetch_field($db->simple_select("character_nav", "gids", "cnid = {$cnid}"), "gids");
                    $gids_list = explode(",", $gids);
                    if(in_array($group['gid'], $gids_list)) {
                        $checked_groups[] = $group['gid'];
                    }
                }
            }

            $form_container->output_row(
                $lang->character_admin_add_pages_groups.'<em>*</em>',
                $lang->character_admin_add_pages_groups_desc,
                $form->generate_select_box('character_nav_groups[]', $groups_options, isset($mybb->input['character_nav_groups']) 
                        ? $mybb->input['character_nav_groups'] 
                        : (isset($checked_groups) ? $checked_groups : ''), ['multiple' => true])
            );

            $form_container->output_row(
                $lang->character_admin_add_nav_cat.'<em>*</em>',
                $lang->character_admin_add_nav_cat_desc,
                $form->generate_select_box('character_nav_cat', ["1" => $lang->character_nav_title_pages, "2" => $lang->character_nav_title_plugins, "3" => $lang->character_nav_title_code], isset($mybb->input['character_nav_cat']) 
                        ? $mybb->input['character_nav_cat'] 
                        : (isset($cnav['cat']) ? $cnav['cat'] : ''))
            );

			$form_container->end();
			$buttons[] = $form->generate_submit_button($lang->character_admin_submit_button);
			$form->output_submit_wrapper($buttons);
			$form->end();
			$page->output_footer();

        }

		if ($mybb->input['action'] == "add_pages" || $mybb->input['action'] == "edit_page") {
            $action = $mybb->input['action'];
            $cpid = $mybb->get_input('cpid');
            $code = $mybb->get_input('code');
			if ($mybb->request_method == "post") {
                if($code == false) {
                    if(empty($mybb->get_input('character_pages_name')) || empty($mybb->get_input('character_pages_desc')) || empty($mybb->get_input('character_pages_action'))) {
                        $errors[] = $lang->character_admin_error_missing_values;
                    }
                } else {
                    if(empty($mybb->get_input('character_pages_name')) || empty($mybb->get_input('character_pages_desc')) || empty($mybb->get_input('character_pages_action')) || empty($mybb->get_input('character_pages_template'))) {
                        $errors[] = $lang->character_admin_error_missing_values;
                    }                   
                }
                $selected_gids = implode(",", $mybb->get_input('character_groups', MyBB::INPUT_ARRAY));
				if(!$errors) {
					$new_entry = [
						"name" => $db->escape_string($mybb->get_input('character_pages_name')),
                        "action" => $db->escape_string($mybb->get_input('character_pages_action')),
						"description" => $db->escape_string($mybb->get_input('character_pages_desc')),
                        "sort" => $db->escape_string($mybb->get_input('character_pages_order')),
                        "gids" => $selected_gids
					];
                    if($code) {
                        $add_redirect = "&action=code";
                    }
                    if($action == "add_pages") {
                        if($code == false) {
                            $cpid = $db->insert_query("character_pages", $new_entry);
                        } else { 
                            $new_entry['code'] = $db->escape_string($mybb->get_input('character_pages_code'));
                            $new_entry['template_name'] = $db->escape_string($mybb->get_input('character_pages_template'));
                            $cpid = $db->insert_query("character_pages_code", $new_entry);
                        }
                        $lang_module = $lang->character_admin_page_added;
                    } else {
                        if($code == false) {
                            $db->delete_query("character_pages_fields", "cpid = {$cpid}");
                            $db->update_query("character_pages", $new_entry, "cpid = '{$cpid}'");
                        } else {
                            $new_entry['code'] = $db->escape_string($mybb->get_input('character_pages_code'));
                            $new_entry['template_name'] = $db->escape_string($mybb->get_input('character_pages_template'));
                            $db->update_query("character_pages_code", $new_entry, "cpcid = '{$cpid}'");
                        }
                        $lang_module = $lang->character_admin_page_edited;                        
                    }

                    foreach($mybb->get_input('character_fields', MyBB::INPUT_ARRAY) as $fid) {
                        $new_entry = [ "fid" => $fid, "cpid" => $cpid];
                        $db->insert_query("character_pages_fields", $new_entry);
                    }
                        
					$mybb->input['module'] = $lang->character_admin_config_menu;
					$mybb->input['action'] = $lang_module . " ";
					log_admin_action(htmlspecialchars_uni($mybb->get_input('character_pages_name')));
					flash_message($lang_module, 'success');
					admin_redirect("index.php?module=config-character{$add_redirect}");
				}
			}

			$sub_tabs = build_sub_tabs_character();
            $page->extra_header .= '
            <link rel="stylesheet" href="./jscripts/codemirror/lib/codemirror.css">
            <link rel="stylesheet" href="./jscripts/codemirror/theme/mybb.css">

            <script src="./jscripts/codemirror/lib/codemirror.js"></script>

            <script src="./jscripts/codemirror/mode/clike/clike.js"></script>
            <script src="./jscripts/codemirror/mode/php/php.js"></script>

            <script>
            document.addEventListener("DOMContentLoaded", function () {
            var ta = document.getElementById("character_pages_code");
            if (!ta || typeof CodeMirror === "undefined") return;

            CodeMirror.fromTextArea(ta, {
                lineNumbers: true,
                matchBrackets: true,
                indentUnit: 4,
                indentWithTabs: true,
                mode: "text/x-php",   // <-- für PHP-Snippets
                theme: "mybb"
            });
            });
            </script>
            ';
            $page->add_breadcrumb_item($lang->character_admin_add_pages);
            $page->output_header($mybb->settings['bbname']." - ".$lang->character_admin_add_pages); 
            if($code == false) {
                $page->output_nav_tabs($sub_tabs, "character_add_pages");
            } else { 
                $add_code = "&code=1";
                $page->output_nav_tabs($sub_tabs, "character_add_pages_code"); } 

            if($action == "add_pages") {
                $form = new Form("index.php?module=config-character&amp;action=add_pages{$add_code}", "post", "", 1);
                $form_container = new FormContainer($lang->character_admin_add_pages);
            }
            else {
                if($code == false) {
                    $cpage = $db->fetch_array($db->simple_select("character_pages", "*", "cpid = {$cpid}"));
                } else {
                    $cpage = $db->fetch_array($db->simple_select("character_pages_code", "*", "cpcid = {$cpid}"));
                }
                $checked_fields = [];
                $form = new Form("index.php?module=config-character&amp;action=edit_page{$add_code}&cpid={$cpid}", "post", "", 1); 
			    $form_container = new FormContainer($lang->character_admin_edit_page);      
            }
            if($code == false) {
                $query = $db->simple_select("profilefields", "fid, name", "", ["order_by" => "name", "order_dir" => "ASC"]);	
                $fields_options = [];
                while($fields = $db->fetch_array($query)) {
                    $fid = $fields['fid'];
                    $check = "";
                    $check = $db->fetch_field($db->simple_select("character_pages_fields", "cpid", "fid = '{$fid}'"), "cpid");
                    if(!$check && $action == "add_pages") {
                        $fields_options[$fid] = $fields['name'];
                    } elseif($action == "edit_page") {
                        $fields_options[$fid] = $fields['name'];
                        if($check == $cpid) {
                        $checked_fields[] = $fid;
                        }
                    }
                }
            }

            $groups_options = [];
            $checked_groups = [];
            $query = $db->simple_select("usergroups", "gid, title", "", ["order_by" => "title"]);
            while ($group = $db->fetch_array($query)) {
                $groups_options[$group['gid']] = $group['title'];
                if($action == "edit_page") {
                    if($code == false) {
                        $gids = $db->fetch_field($db->simple_select("character_pages", "gids", "cpid = {$cpid}"), "gids");
                    } else { $gids = $db->fetch_field($db->simple_select("character_pages_code", "gids", "cpcid = {$cpid}"), "gids"); }
                    $gids_list = explode(",", $gids);
                    if(in_array($group['gid'], $gids_list)) {
                        $checked_groups[] = $group['gid'];
                    }
                }
            }

			if (isset($errors)) {
				$page->output_inline_error($errors);
			}

			$form_container->output_row(
					$lang->character_admin_add_pages_name.'<em>*</em>',
					$lang->character_admin_add_pages_name_desc,
					$form->generate_text_box('character_pages_name', isset($mybb->input['character_pages_name']) 
                        ? $mybb->input['character_pages_name'] 
                        : (isset($cpage['name']) ? $cpage['name'] : ''))
			);
			$form_container->output_row(
					$lang->character_admin_add_pages_action.'<em>*</em>',
					$lang->character_admin_add_pages_action_desc,
					$form->generate_text_box('character_pages_action', isset($mybb->input['character_pages_action']) 
                        ? $mybb->input['character_pages_action'] 
                        : (isset($cpage['action']) ? $cpage['action'] : ''))
			);
			$form_container->output_row(
					$lang->character_admin_add_pages_descr.'<em>*</em>',
					$lang->character_admin_add_pages_descr_desc,
					$form->generate_text_area('character_pages_desc', isset($mybb->input['character_pages_desc']) 
                        ? $mybb->input['character_pages_desc'] 
                        : (isset($cpage['description']) ? $cpage['description'] : ''))
			);

            $form_container->output_row(
                $lang->character_admin_add_pages_groups.'<em>*</em>',
                $lang->character_admin_add_pages_groups_desc,
                $form->generate_select_box('character_groups[]', $groups_options, isset($mybb->input['character_groups']) 
                        ? $mybb->input['character_groups'] 
                        : (isset($checked_groups) ? $checked_groups : ''), ['multiple' => true])
            );
            if($code == false) {
                $form_container->output_row(
                    $lang->character_admin_add_pages_fields.'<em>*</em>',
                    $lang->caracter_admin_add_pages_fields_desc,
                    $form->generate_select_box('character_fields[]', $fields_options, isset($mybb->input['character_fields']) 
                            ? $mybb->input['character_fields'] 
                            : (isset($checked_fields) ? $checked_fields : ''), ['multiple' => true])
                );
            } else {

                $form_container->output_row(
                        $lang->character_admin_add_pages_code_code.'<em>*</em>',
                        $lang->character_admin_add_pages_code_code_desc,
                        $form->generate_text_area('character_pages_code', isset($mybb->input['character_pages_code']) 
                            ? $mybb->input['character_pages_code'] 
                            : (isset($cpage['code']) ? $cpage['code'] : ''), ["id" => "character_pages_code", "rows" => 15, "style" => "width: 95%"])
                );  
                $form_container->output_row(
                        $lang->character_admin_add_pages_template.'<em>*</em>',
                        $lang->character_admin_add_pages_template_desc,
                        $form->generate_text_box('character_pages_template', isset($mybb->input['character_pages_template']) 
                            ? $mybb->input['character_pages_template'] 
                            : (isset($cpage['template_name']) ? $cpage['template_name'] : ''))
                );             
            }
			$form_container->output_row(
					$lang->character_admin_add_pages_order,
					$lang->character_admin_add_pages_order_desc,
					$form->generate_text_box('character_pages_order', isset($mybb->input['character_pages_order']) 
                        ? $mybb->input['character_pages_order'] 
                        : (isset($cpage['sort']) ? $cpage['sort'] : '1'))
			);
			$form_container->end();
			$buttons[] = $form->generate_submit_button($lang->character_admin_submit_button);
			$form->output_submit_wrapper($buttons);
			$form->end();
			$page->output_footer();
		}
        
        if($mybb->input['action'] == "plugins") {

			$page->add_breadcrumb_item($lang->character_admin_plugins);
			$page->output_header($mybb->settings['bbname']." - ".$lang->character_admin_plugins);

			$sub_tabs = build_sub_tabs_character();

			$page->output_nav_tabs($sub_tabs, "character_plugins");

            if(isset($errors)) {
                $page->output_inline_error($errors);
            }
            $plugins_list = character_get_plugins_list("usercp_start");
            $table = new Table;
            $table->construct_header($lang->character_admin_plugin);
            $table->construct_header($lang->character_admin_plugin_control, ["colspan" => 2, "class" => "align_center", "width" => 300]);
            if(empty($plugins_list)) {
                $table->construct_cell($lang->character_admin_no_plugins, ['colspan' => 3]);
                $table->construct_row();
            }
            else {
                foreach($plugins_list as $plugin_file) {
                    $path = MYBB_ROOT."inc/plugins/".$plugin_file['file'];
                    $codename = str_replace(".php", "", $plugin_file['file']);
                    $infofunc = $codename."_info";

                    if(!function_exists($infofunc))
                    {
                        continue;
                    }
                    $plugininfo = $infofunc();
                    $plugininfo['codename'] = $codename;  
                    $contents = file_get_contents($path);
                    if (strpos($contents, "character.php") !== false) {
                        $plugininfo['usercp'] = "&bull; <small><span style=\"color: #ff0000;\">{$lang->character_admin_plugin_character_warning}</span></small>";
                    } else {
                        $plugininfo['usercp'] = "";
                    }
                    $table->construct_cell("<strong>{$plugininfo['name']}</strong> ({$plugininfo['version']})<br /><small>{$plugininfo['description']}</small><br /><i><small>erstellt von {$plugininfo['author']}</small></i>");  
                    $table->construct_cell("<a href=\"index.php?module=config-character&amp;action=do_append&amp;plugin={$plugininfo['codename']}&amp;my_post_key={$mybb->post_code}\">{$lang->character_admin_append_plugin}</a>", ["class" => "align_center", "colspan" => 2]);
                    $table->construct_row();             
                }  
            }
            $table->output($lang->character_admin_plugins);
            $page->output_footer();
        }
        if($mybb->input['action'] == "plugins_ccp") {

			$page->add_breadcrumb_item($lang->character_admin_plugins_ccp);
			$page->output_header($mybb->settings['bbname']." - ".$lang->character_admin_plugins_ccp);

			$sub_tabs = build_sub_tabs_character();

			$page->output_nav_tabs($sub_tabs, "character_plugins_ccp");

            if(isset($errors)) {
                $page->output_inline_error($errors);
            }
            $plugins_list = character_get_plugins_list("character_start");
            $table = new Table;
            $table->construct_header($lang->character_admin_plugin_control);
            $table->construct_header($lang->character_admin_plugin_control, ["colspan" => 2, "class" => "align_center", "width" => 300]);
            if(empty($plugins_list)) {
                $table->construct_cell($lang->character_admin_no_plugins, ['colspan' => 3]);
                $table->construct_row();
            }
            else {
                foreach($plugins_list as $plugin_file) {
                    $path = MYBB_ROOT."inc/plugins/".$plugin_file['file'];
                    $codename = str_replace(".php", "", $plugin_file['file']);
                    $infofunc = $codename."_info";

                    if(!function_exists($infofunc))
                    {
                        continue;
                    }
                    $plugininfo = $infofunc();
                    $plugininfo['codename'] = $codename; 
                    $contents = file_get_contents($path);
                    if (strpos($contents, "usercp.php") !== false) {
                        $plugininfo['usercp'] = "&bull; <small><span style=\"color: #ff0000;\">{$lang->character_admin_plugin_usercp_warning}</span></small>";
                    } else {
                        $plugininfo['usercp'] = "";
                    }
                    $table->construct_cell("<strong>{$plugininfo['name']}</strong> ({$plugininfo['version']}) {$plugininfo['usercp']} <br /><small>{$plugininfo['description']}</small><br /><i><small>erstellt von {$plugininfo['author']}</small></i>");  
                    $table->construct_cell("<a href=\"index.php?module=config-character&amp;action=do_detach&amp;plugin={$plugininfo['codename']}&amp;my_post_key={$mybb->post_code}\">{$lang->character_admin_detach_plugin}</a>", ["class" => "align_center", "colspan" => 2]);
                    $table->construct_row();             
                }  
            }
            $table->output($lang->character_admin_plugins_ccp);
            $page->output_footer();
        }
        if($mybb->input['action'] == "delete_character") {
            $cpid = $mybb->get_input('cpid');
            $db->delete_query("character_pages", "cpid = '{$cpid}'");
            $db->delete_query("character_pages_fields", "cpid = '{$cpid}'");
            log_admin_action(htmlspecialchars_uni($cpid));
            flash_message($lang->character_admin_page_deleted, 'success');
            admin_redirect("index.php?module=config-character");
        }
        if($mybb->input['action'] == "delete_code") {
            $cpcid = $mybb->get_input('cpcid');
            $db->delete_query("character_pages_code", "cpcid = '{$cpcid}'");
            log_admin_action(htmlspecialchars_uni($cpcid));
            flash_message($lang->character_admin_page_deleted, 'success');
            admin_redirect("index.php?module=config-character&action=code");
        }
        if($mybb->input['action'] == "delete_nav") {
            $cnid = $mybb->get_input('cnid');
            $db->delete_query("character_nav", "cnid = '{$cnid}'");
            log_admin_action(htmlspecialchars_uni($cnid));
            flash_message($lang->character_admin_nav_deleted, 'success');
            admin_redirect("index.php?module=config-character&action=nav");
        }
        if($mybb->input['action'] == "do_append") {
            $plugin = $mybb->get_input('plugin');
            set_character_hook($plugin, "usercp_start", "character_start");
            set_character_navigation($plugin);
            set_character_hook($plugin, "usercp_menu", "character_menu");
            log_admin_action(htmlspecialchars_uni($plugin));
            flash_message($lang->character_admin_plugin_appended, 'success');
            admin_redirect("index.php?module=config-character&action=plugins_ccp");
        }
        if($mybb->input['action'] == "do_detach") {
            $plugin = $mybb->get_input('plugin');
            set_character_hook($plugin, "character_start", "usercp_start");
            set_usercp_navigation($plugin);
            set_character_hook($plugin, "character_menu", "usercp_menu");
            log_admin_action(htmlspecialchars_uni($plugin));
            flash_message($lang->character_admin_plugin_detached, 'success');
            admin_redirect("index.php?module=config-character&action=plugins");
        }
        if($mybb->input['action'] == "detach_field") {
            $db->delete_query("character_pages_fields", "fid = '{$mybb->get_input('fid')}'");
            log_admin_action(htmlspecialchars_uni("ID: " . $fid));
            flash_message($lang->character_admin_field_detached, 'success');
            admin_redirect("index.php?module=config-character");
        } 
    }
}

$plugins->add_hook('admin_config_profile_fields_add', 'character_admin_config_profile_fields');
$plugins->add_hook('admin_config_profile_fields_edit', 'character_admin_config_profile_fields');
$plugins->add_hook('admin_formcontainer_end', 'character_admin_formcontainer_end');

function character_admin_config_profile_fields()
{
    global $mybb, $lang;
    $lang->load('character');

    // Nur in genau diesem Modul/Action (Sicherheit)
    if ($mybb->get_input('module') === 'config-profile_fields' && in_array($mybb->get_input('action'), ['add', 'edit'], true)) {
        $GLOBALS['character_formcontainer_end'] = true;
    }
}

function character_admin_formcontainer_end()
{
    global $form_container, $form, $lang, $mybb, $db, $profile_field;

    if (empty($GLOBALS['character_formcontainer_end'])) {
        return;
    }

    $GLOBALS['character_formcontainer_end'] = false;

    $fields_options = ["" => $lang->character_admin_set_page_none];
    $query = $db->simple_select("character_pages", "cpid, name", "", ["order_by" => "name", "order_dir" => "ASC"]);
    while($fields = $db->fetch_array($query)) {
        $cpid = $fields['cpid'];
        $fields_options[$cpid] = $fields['name'];
    }

    $current = "";
    if(!empty($profile_field['fid'])) {
        $row = $db->fetch_array(
        $db->simple_select("character_pages_fields", "cpid", "fid='".(int)$profile_field['fid']."'"));
        $current = $row['cpid'];
    }

    $selected = isset($mybb->input['character_page'])
        ? $mybb->input['character_page']
        : $current;

    $form_container->output_row(
        $lang->character_admin_set_page_name,
        $lang->character_admin_set_page_desc,
        $form->generate_select_box("character_page", $fields_options, $selected)
    );
}

$plugins->add_hook('admin_config_profile_fields_add_commit',  'character_admin_config_profile_fields_commit');
$plugins->add_hook('admin_config_profile_fields_edit_commit', 'character_admin_config_profile_fields_commit');

function character_admin_config_profile_fields_commit()
{
    global $mybb, $db, $profile_field, $fid;

    $character_page = $db->escape_string($mybb->get_input('character_page'));

    if (!empty($profile_field['fid'])) {
        $fid = (int)$profile_field['fid'];
    } elseif (!empty($mybb->input['fid'])) {
        $fid = (int)$mybb->input['fid'];
    }

    if ($fid <= 0) {
        return; 
    }

    $check = $db->fetch_field($db->simple_select('character_pages_fields', 'fid', "fid='{$fid}'"), 'fid');
    if ($check) {
        $db->update_query("character_pages_fields", ["cpid" => (int)$character_page], "fid='{$fid}'");
    } else {
        $db->insert_query("character_pages_fields", ["fid" => $fid, "cpid" => (int)$character_page]);
    }
}

$plugins->add_hook("usercp_profile_start", "character_usercp_profile_start", 0);
function character_usercp_profile_start()
{
    global $mybb, $cache, $db;

    $exclude = [];
    $query = $db->simple_select("character_pages_fields", "fid");

    while($field = $db->fetch_array($query)) {
        $exclude[(int)$field['fid']] = true;
    }

    $pfcache = $cache->read('profilefields');
    $filtered = [];
    foreach ($pfcache as $pf) {
        $fid = (int)$pf['fid'];
        if (isset($exclude[$fid])) {
            continue;
        }
        $filtered[] = $pf;
    }
    $cache->cache['profilefields'] = $filtered;
}

$plugins->add_hook("usercp_do_profile_start", "character_usercp_do_profile_start");
function character_usercp_do_profile_start()
{
    global $db, $mybb, $cache;

    $exclude = [];
    $query = $db->simple_select("character_pages_fields", "fid");
    while($row = $db->fetch_array($query)) {
        $exclude[(int)$row['fid']] = true;
    }
    if(!$exclude) return;

    if(isset($mybb->input['profile_fields']) && is_array($mybb->input['profile_fields'])) {
        foreach($exclude as $fid => $_) {
            unset($mybb->input['profile_fields'][$fid]);
        }
    }

    $pfcache = $cache->read('profilefields');
    if(is_array($pfcache)) {
        $filtered = [];
        foreach($pfcache as $pf) {
            $fid = (int)$pf['fid'];
            if(isset($exclude[$fid])) { continue; }
            $filtered[] = $pf;
        }
        $cache->cache['profilefields'] = $filtered;
    }
}

$plugins->add_hook("global_intermediate", "character_global_intermediate");
function character_global_intermediate()
{
    global $db, $mybb, $lang, $templates, $header_character;
    $lang->load('character');

    eval("\$header_character = \"" . $templates->get("character_header") . "\";");
}

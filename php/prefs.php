<?php
    require_once("include.inc.php");

    if ($_action == "edit") {
        $action = "update";
    }
    else if ($_action == "update") {
        if (DEFAULT_USER != $user->get("user_id")) {
            $user->prefs->set_fields($request_vars);
            $user->prefs->update();
            $user->prefs->load(1);
            $rtplang = $user->load_language(1);
        }
        $action = "update";
    }
    else {
        $action = "update";
    }

    $title = translate("Preferences");

    $table_width = " width=\"" . DEFAULT_TABLE_WIDTH . "\"";
    require_once("header.inc.php");
?>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TITLE_BG_COLOR?>">
        <tr>
          <th align="left"><font color="<?php echo $TITLE_FONT_COLOR ?>"><?php echo translate("edit preferences") ?></font></th>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <table border="0" cellpadding="4" cellspacing="0" width="100%" bgcolor="<?php echo $TABLE_BG_COLOR?>">
<?php
    if ($user->get("user_id") == DEFAULT_USER) {
?>
        <tr>
          <td colspan="2">
       <?php echo sprintf(translate("The user %s is currently defined as the default user and does not have permission to change its preferences.  The current values are shown below but any changes made will be ignored until a different default user is defined."), $user->get("user_name")); ?>
          </td>
        </tr>
<?php
    }
?>
        <tr>
          <td align="right">
<form action="prefs.php" method="GET">
<input type="hidden" name="_action" value="<?php echo $action ?>">
<input type="hidden" name="user_id" value="<?php echo $user->prefs->get("user_id") ?>">
    <?php echo translate("user name") ?>
          </td>
          <td>
            <?php echo $user->get("user_name") ?>
            [ <a href="password.php"><?php echo translate("change password") ?></a> ]
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("show breadcrumbs") ?></td>
          <td>
<?php echo create_pulldown("show_breadcrumbs", $user->prefs->get("show_breadcrumbs"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("number of breadcrumbs to show") ?></td>
          <td>
<?php echo create_integer_pulldown("num_breadcrumbs", $user->prefs->get("num_breadcrumbs"), 1, 20) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("default number of rows on results page") ?></td>
          <td>
<?php echo create_integer_pulldown("num_rows", $user->prefs->get("num_rows"), 1, 10) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("default number of columns on results page") ?></td>
          <td>
<?php echo create_integer_pulldown("num_cols", $user->prefs->get("num_cols"), 1, 10) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("size of pager on results page") ?></td>
          <td>
<?php echo create_integer_pulldown("max_pager_size", $user->prefs->get("max_pager_size"), 1, 20) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("minimum rating for random photos") ?></td>
          <td>
<?php echo create_integer_pulldown("random_photo_min_rating", $user->prefs->get("random_photo_min_rating"), 0, 10) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("number of results to display on reports page") ?></td>
          <td>
<?php echo create_integer_pulldown("reports_top_n", $user->prefs->get("reports_top_n"), 1, 20) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("time to display each photo in a slideshow") ?></td>
          <td>
<?php echo create_text_input("slideshow_time", $user->prefs->get("slideshow_time"), 4, 4) ?> <?php echo translate("seconds") ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("days past for recent photos links") ?></td>
          <td>
<?php echo create_text_input("recent_photo_days", $user->prefs->get("recent_photo_days"), 4, 4) ?>
          </td>
        </tr>
<?php
    if (MAX_THUMB_DESC) {
?>
        <tr>
          <td align="right"><?php echo translate("show descriptions under thumbnails") ?></td>
          <td>
<?php echo create_pulldown("desc_thumbnails", $user->prefs->get("desc_thumbnails"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
          </td>
        </tr>
<?php
    }
?>
        <tr>
          <td align="right"><?php echo translate("display camera info") ?></td>
          <td>
<?php echo create_pulldown("camera_info", $user->prefs->get("camera_info"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td align="right"><?php echo translate("automatically edit photos") ?></td>
          <td>
<?php echo create_pulldown("auto_edit", $user->prefs->get("auto_edit"), array("1" => translate("Yes",0), "0" => translate("No",0)) ) ?>
          </td>
        </tr>
        <tr>
          <td align="right">
<?php
    if ($user->is_admin()) {
?>
            <a href="color_schemes.php"><?php echo translate("color scheme") ?></a>
<?php
    }
    else {
?>
            <?php echo translate("color scheme") ?>
<?php
    }
?>
          </td>
          <td>
<?php echo create_smart_pulldown("color_scheme_id", $user->prefs->get("color_scheme_id"), create_select_array(get_records("color_scheme", "name"), array("name"))) ?>
          </td>
        </tr>
<?php
    $lang_array = $rtplang->get_available_languages();
    $lang_select_array['null'] = 'Browser Default';
    while (list($code, $code_to_name) = each($lang_array)) {
        $lang_select_array[$code] = $code_to_name[$code];
    }
?>
        <tr>
          <td align="right"><?php echo translate("language") ?></td>
          <td>
<?php echo create_smart_pulldown("language", $user->prefs->get("language"), $lang_select_array) ?>
          </td>
        </tr>
        <tr>
          <td colspan="2" align="center">
<input type="submit" value="update">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</div>

<?php require_once("footer.inc.php"); ?>
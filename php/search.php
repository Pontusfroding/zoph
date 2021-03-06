<?php
/**
 * Search page
 *
 * This file is part of Zoph.
 *
 * Zoph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Zoph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Zoph; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @author Jason Geiger
 * @author Jeroen Roos
 * @package Zoph
 */

use conf\conf;
use template\template;

require_once "include.inc.php";

error_reporting(E_ALL & ~E_NOTICE);

$title=translate("search",0);
if ($_action=="insert") {
    $search=new search();
    $search->set("owner", $user->get("user_id"));
} else if ($_action == "update" ||
           $_action == "confirm" ||
           $_action == "delete" ) {
    $search_id=getvar("search_id");
    $search=new search($search_id);
    $search->lookup();
    if (!($search->get("owner") == $user->get("user_id") ||
        $user->isAdmin())) {
        redirect("zoph.php", "You're not allowed to do that!");
    }
}

if (strtolower($_action) == strtolower(rtrim(translate("search")))) {
    $request_vars=$request->getRequestVarsClean();
    require_once "photos.php";
} else if ($_action=="new" || $_action=="edit") {
    if ($_action=="new") {
        $action="insert";
        unset($request_vars["_action"]);
        $request_vars=$request->getRequestVarsClean();

        foreach ($request_vars as $key => $val) {
            # Change key#0 into key[0]:
            $key=preg_replace("/\#([0-9]+)/", "[$1]", $key);
            # Change key[0]-children into key_children[0] because everything
            # after ] in a URL is lost fix for bug#2890387
            $key=preg_replace("/\[(.+)\]-([a-z]+)/", "_$2[$1]", $key);
            if ($url) {
                $url.="&";
            }
            $url.=e($key) . "=" . e($val);
        }
        $search=new search;
        $search->set("search", $url);
        $search->set("owner", $user->get("user_id"));
    } else if ($_action=="edit") {
        $action="update";
        $search_id=getvar("search_id");
        $search=new search($search_id);
        $search->lookup();
        $url=$search->get("search");
    }
    require_once "header.inc.php";
    ?>
    <h1><?php echo translate("Save search")?></h1>
    <div class="main">
    <form>
        <input type="hidden" name="search_id" value="<?php echo $search->get("search_id") ?>">

        <?php echo create_edit_fields($search->getEditArray()) ?>
        <input type="hidden" name="search" value="<?php echo $url ?>">
        <input type="hidden" name="_action" value="<?php echo $action?>">
        <input type="submit" name="_button" value="<?php echo translate($action,0)?>">
    </form>
    <div>
    <?php
    require_once "footer.inc.php";
    exit;
} else if ($_action=="update" ||
           $_action=="confirm" ||
           $_action=="insert") {
    $obj = &$search;
    require_once "actions.inc.php";
    redirect("search.php", "Redirect");
} else if ($_action=="delete") {
    $search_id=getvar("search_id");
    $search=new search($search_id);
    $search->lookup();
    $url="search.php?search_id=" . $search->get("search_id") .
        "&_action=confirm";
    require_once "header.inc.php";
    ?>
    <h1><?php echo translate("Delete saved search")?></h1>
    <div class="main">
        <ul class="actionlink">
            <li><a href='<?php echo $url ?>'><?php echo translate("delete") ?></a></li>
            <li><a href='search.php'><?php echo translate("cancel") ?></a></li>
        </ul>
        <?php printf(translate("Confirm deletion of saved search '%s'"), $search->get("name")) ?>
        <br>
     </div>
    <?php
    require_once "footer.inc.php";
    exit;
} else {
    $today = date("Y-m-d");

    require_once "header.inc.php";

    /*
     * Each search item is stored in a set of arrays. The increment button increases
     * the size of the array. The form is generated by looping through the array from
     * the first element to the second to last element, displaying it without an increment
     * button. Then the last element is displayed with an increment button.
     *
     * Each time a search item is added to the form, it is regenerated with the contents
     * intact. -RB
     */
    ?>
    <h1><?php echo translate("search") ?></h1>
    <div class="main">
        <?= template::showJSwarning() ?>
      <form method="GET" action="search.php">
        <!-- There is a search button here to make it the first submit
             in the form for submit on Enter -->
        <span>
          <input type="submit" name="_action" value="<?php echo translate("search", 0); ?>">
        </span>
        <br>
        <table id="search">
    <?php
    /* photo taken date */

    $date = getvar('date');
    $_date_conj = getvar('_date_conj');
    $_date_op = getvar('_date_op');

    $count = $date ? sizeof($date) - 1 : 0;
    if ($date[$count] == "+") {
        $date[$count] = "";
    }
    for ($i = 0; $i <= $count; $i++) {
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment" name="date[<?php echo $count + 1; ?>]" value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
        <?php echo create_conjunction_pulldown("_date_conj[$i]", $_date_conj[$i]) ?>
          </td>
          <td><?php echo translate("photos taken") ?></td>
          <td>
        <?php echo create_inequality_operator_pulldown("_date_op[$i]", $_date_op[$i]) ?>
          </td>
          <td colspan="2">
        <?php
        echo template::createPulldown("date[$i]", $date[$i],
            get_date_select_array($today, conf::get("interface.max.days")));
        echo translate("days ago");
        ?>
          </td>
        </tr>
        <?php
    }
    /* photos last modified */

    $timestamp = getvar('timestamp');
    $_timestamp_conj = getvar('_timestamp_conj');
    $_timestamp_op = getvar('_timestamp_op');

    $count = $timestamp ? sizeof($timestamp) - 1 : 0;
    if ($timestamp[$count] == "+") {
        $timestamp[$count] = "";
    }
    for ($i = 0; $i <= $count; $i++) {
        ?>
            <tr>
              <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment"
                name="timestamp[<?php echo $count + 1; ?>]" value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
              </td>
              <td>
        <?php echo create_conjunction_pulldown("_timestamp_conj[$i]", $_timestamp_conj[$i]) ?>
              </td>
              <td><?php echo translate("photos modified") ?></td>
              <td>
        <?php echo create_inequality_operator_pulldown("_timestamp_op[$i]", $_timestamp_op[$i]) ?>
          </td>
          <td colspan="2">
        <?php
        echo template::createPulldown("timestamp[$i]", $timestamp[$i],
            get_date_select_array($today, conf::get("interface.max.days")));
        echo translate("days ago")
        ?>
          </td>
        </tr>
        <?php
    }
    /* photo album */

    $album_id = getvar('album_id');
    if (!is_array($album_id) && !empty($album_id)) {
        $album_id=explode(",", $album_id);
        foreach ($album_id as $key => $album) {
            $_album_id_conj[$key]="or";
            $_album_id_opp[$key]="=";
            $_album_id_children[$key]="";
        }
    } else {
        $_album_id_conj = getvar('_album_id_conj');
        $_album_id_op = getvar('_album_id_op');
        $_album_id_children = getvar('_album_id_children');
    }

    $count = $album_id ? sizeof($album_id) - 1 : 0;
    if ($album_id[$count] == "+") {
        $album_id[$count] = "";
    }

    for ($i = 0; $i <= $count; $i++) {
        if ($_album_id_children[$i]) {
            $checked="checked";
        } else {
            $checked="";
        }
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment"
                name="album_id[<?php echo $count + 1; ?>]" value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
        <?php echo create_conjunction_pulldown("_album_id_conj[$i]", $_album_id_conj[$i]) ?>
          </td>
          <td><?php echo translate("album") ?></td>
          <td>
        <?php echo create_binary_operator_pulldown("_album_id_op[$i]", $_album_id_op[$i]) ?>
          </td>
          <td colspan="2">
        <?php
        echo album::createPulldown("album_id[$i]", $album_id[$i]);
        ?>
          <br><input type="checkbox" name="_album_id_children[<?php echo $i ?>]" value="yes"
            <?php echo $checked ?>>
          <label for="_album_id_children[<?php echo $i ?>]">
            <?php echo translate("include sub-albums") ?>
          </label>
          </td>
        </tr>
        <?php
    }
    /* photo category */

    $category_id = getvar('category_id');
    if (!is_array($category_id) && !empty($category_id)) {
        $category_id=explode(",", $category_id);
        foreach ($category_id as $key => $cat) {
            $_category_id_conj[$key]="or";
            $_category_id_opp[$key]="=";
            $_category_id_children[$key]="";
        }
    } else {
        $_category_id_conj = getvar('_category_id_conj');
        $_category_id_op = getvar('_category_id_op');
        $_category_id_children = getvar('_category_id_children');
    }

    $count = $category_id ? sizeof($category_id) - 1 : 0;
    if ($category_id[$count] == "+") {
        $category_id[$count] = "";
    }

    for ($i = 0; $i <= $count; $i++) {
        if ($_category_id_children[$i]) {
            $checked="checked";
        } else {
            $checked="";
        }
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment"
                name="category_id[<?php echo $count + 1; ?>]" value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
        <?php echo create_conjunction_pulldown("_category_id_conj[$i]",
            $_category_id_conj[$i]) ?>
          </td>
          <td><?php echo translate("category") ?></td>
          <td>
        <?php echo create_binary_operator_pulldown("_category_id_op[$i]", $_category_id_op[$i]) ?>
          </td>
          <td colspan="2">
        <?php
        echo category::createPulldown("category_id[$i]", $category_id[$i]);
        ?>
          <br>
            <input type="checkbox" name="_category_id_children[<?php echo $i ?>]"
              value="yes" <?php echo $checked ?>>
            <label for="_category_id_children[<?php echo $i ?>]">
              <?php echo translate("include sub-categories") ?>
            </label>
          </td>
        </tr>
    <?php
    }
    /* photo location */

    $location_id = getvar('location_id');
    if (!is_array($location_id) && !empty($location_id)) {
        $location_id=explode(",", $location_id);
        foreach ($location_id as $key => $loc) {
            $_location_id_conj[$key]="or";
            $_location_id_opp[$key]="=";
            $_location_id_children[$key]="";
        }
    } else {
        $_location_id_conj = getvar('_location_id_conj');
        $_location_id_op = getvar('_location_id_op');
        $_location_id_children = getvar('_location_id_children');
    }

    $count = $location_id ? sizeof($location_id) - 1 : 0;
    if ($location_id[$count] == "+") {
        $location_id[$count] = "";
    }

    for ($i = 0; $i <= $count; $i++) {
        if ($_location_id_children[$i]) {
            $checked="checked";
        } else {
            $checked="";
        }
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment"
                name="location_id[<?php echo $count + 1; ?>]" value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
            <?php echo create_conjunction_pulldown("_location_id_conj[$i]",
                $_location_id_conj[$i]) ?>
          </td>
          <td><?php echo translate("location") ?></td>
          <td>
            <?php echo create_binary_operator_pulldown("_location_id_op[$i]",
                $_location_id_op[$i]) ?>
          </td>
          <td colspan="2">
            <?php echo place::createPulldown("location_id[$i]", $location_id[$i]); ?>
            <br>
            <input type="checkbox" name="_location_id_children[<?php echo $i ?>]"
                value="yes" <?php echo $checked ?>>
            <label for="_location_id_children[<?php echo $i ?>]">
                <?php echo translate("include sub-places") ?>
            </label>
          </td>
        </tr>
    <?php
    }
    /* photo rating */

    $rating = getvar('rating');
    $_rating_conj = getvar('_rating_conj');
    $_rating_op = getvar('_rating_op');

    $count = $rating ? sizeof($rating) - 1 : 0;
    if ($rating[$count] == "+") {
        $rating[$count] = "";
    }

    for ($i = 0; $i <= $count; $i++) {
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment" name="rating[<?php echo $count + 1; ?>]"
                value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
            <?php echo create_conjunction_pulldown("_rating_conj[$i]", $_rating_conj[$i]) ?>
          </td>
          <td><?php echo translate("rating") ?></td>
          <td>
            <?php echo create_operator_pulldown("_rating_op[$i]", $_rating_op[$i]) ?>
          </td>
          <td colspan="2">
            <?php echo create_rating_pulldown($rating[$i], "rating[$i]") ?>
          </td>
        </tr>
        <?php
    }
    /* photo person */

    $person_id = getvar('person_id');
    $_person_id_conj = getvar('_person_id_conj');
    $_person_id_op = getvar('_person_id_op');

    $count = $person_id ? sizeof($person_id) - 1 : 0;
    if ($person_id[$count] == "+") {
        $person_id[$count] = "";
    }

    for ($i = 0; $i <= $count; $i++) {
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment" name="person_id[<?php echo $count + 1; ?>]"
                value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
        <?php echo create_conjunction_pulldown("_person_id_conj[$i]", $_person_id_conj[$i]) ?>
          </td>
          <td><?php echo translate("person") ?></td>
          <td>
        <?php echo create_present_operator_pulldown("_person_id_op[$i]", $_person_id_op[$i]) ?>
          </td>
          <td colspan="2">
        <?php echo person::createPulldown("person_id[$i]", $person_id[$i]); ?>
          </td>
        </tr>
        <?php
    }
    /* photographer */

    $photographer_id = getvar('photographer_id');
    $_photographer_id_conj = getvar('_photographer_id_conj');
    $_photographer_id_op = getvar('_photographer_id_op');

    $count = $photographer_id ? sizeof($photographer_id) - 1 : 0;
    if ($photographer_id[$count] == "+") {
        $photographer_id[$count] = "";
    }

    for ($i = 0; $i <= $count; $i++) {
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment" name="photographer_id[<?php
                echo $count + 1; ?>]" value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
        <?php echo create_conjunction_pulldown("_photographer_id_conj[$i]",
            $_photographer_id_conj[$i]) ?>
          </td>
          <td><?php echo translate("photographer") ?></td>
          <td>
        <?php echo create_binary_operator_pulldown("_photographer_id_op[$i]",
            $_photographer_id_op[$i]) ?>
          </td>
          <td colspan="2">
        <?php echo photographer::createPulldown("photographer_id[$i]",
            $photographer_id[$i]); ?>
          </td>
        </tr>
        <?php
    }
    /* photo exif field data */

    $field = getvar('field');
    $_field = getvar('_field');
    $_field_conj = getvar('_field_conj');
    $_field_op = getvar('_field_op');

    $count = $_field ? sizeof($_field) - 1 : 0;
    if ($_field[$count] == "+") {
        $_field[$count] = "";
    }
    for ($i = 0; $i <= $count; $i++) {
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment" name="_field[<?php
                echo $count + 1; ?>]" value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
        <?php echo create_conjunction_pulldown("_field_conj[$i]", $_field_conj[$i]) ?>
          </td>
          <td>
        <?php echo template::createPhotoFieldPulldown("_field[$i]", $_field[$i]) ?>
          </td>
          <td>
        <?php echo create_operator_pulldown("_field_op[$i]", $_field_op[$i]) ?>
          </td>
          <td colspan="2">
            <input type="text" name="field[<?php echo $i; ?>]" value="<?php
                echo e($field[$i]); ?>" size="24" maxlength="64">
          </td>
        </tr>
        <?php
    }
    /* Text search for albums/categories/people/photographers */

    $text = getvar('text');
    $_text = getvar('_text');
    $_text_conj = getvar('_text_conj');
    $_text_op = getvar('_text_op');

    $count = $text ? sizeof($_text) - 1 : 0;
    if ($_text[$count] == "+") {
        $_text[$count] = "";
    }
    for ($i = 0; $i <= $count; $i++) {
        ?>
        <tr>
          <td>
        <?php
        if ($i==$count) {
            ?>
            <input type="submit" class="increment" name="_text[<?php echo $count + 1; ?>]"
                value="+">
            <?php
        } else {
            ?>
            &nbsp;
            <?php
        }
        ?>
          </td>
          <td>
        <?php echo create_conjunction_pulldown("_text_conj[$i]", $_text_conj[$i]) ?>
          </td>
          <td>
        <?php echo create_photo_text_pulldown("_text[$i]", $_text[$i]) ?>
          </td>
          <td>
        <?php echo translate("like"); ?>
          </td>
          <td colspan=2>
            <input type="text" name="text[<?php echo $i; ?>]"
                value="<?php echo e($text[$i]); ?>" size="24" maxlength="64">
          </td>
          </tr>
        <?php
    }
    // Search for location
    $lat = getvar('lat');
    $lon = getvar('lon');
    $distance = getvar('_latlon_distance');
    $entity = getvar('_latlon_entity');
    $_latlon_conj = getvar('_latlon_conj');
    $_latlon_op = getvar('_latlon_op');
    ?>
          <tr>
          <td>&nbsp;</td>
          <td>
            <?php echo create_conjunction_pulldown("_latlon_conj", $_latlon_conj) ?>
          </td>
          <td>
            <input type="checkbox" name="_latlon_photos" value="photos" checked>
            <?php echo translate("photos taken") ?><br>
            <input type="checkbox" name="_latlon_places" value="places">
            <?php echo translate("locations") ?>
          </td>
          <td>
            &lt; <?php echo create_text_input("_latlon_distance", $distance, 5, 5) ?>
          </td>
          <td>
            <?php echo template::createPulldown("_latlon_entity", $entity,
                array("km" => "km", "miles" => "miles")) ?>
            <?php echo translate("from"); ?>
          </td>
        </tr>
        <tr>
          <td colspan=5>
            <fieldset class="map">
              <legend><?php echo translate("map"); ?></legend>
              <label for="lat"><?php echo translate("latitude") ?></label>
              <?php echo create_text_input("lat", $lat, 10, 10) ?><br>
              <label for="lon"><?php echo translate("longitude") ?></label>
              <?php echo create_text_input("lon", $lon, 10, 10) ?><br>
            </fieldset>
          </td>
        </tr>
      </table><br>
      <!-- And another search button for consistancy -->
      <span>
        <input type="submit" name="_action" value="<?php echo translate("search", 0); ?>">
      </span>
    <?php
    echo search::getList();
    ?>

    </div>
    <?php
    if (conf::get("maps.provider")) {
        $map=new geo\map();
        $map->setEditable();
        $map->setCenterAndZoom(0,0,2);
        echo $map;
    }
    require_once "footer.inc.php";
}
?>

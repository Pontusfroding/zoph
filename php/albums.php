<?php
/**
 * Show albums
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
 * @package Zoph
 * @author Jason Geiger
 * @author Jeroen Roos
 */
use template\block;
use template\template;

require_once "include.inc.php";

$_view=getvar("_view");
if (empty($_view)) {
    $_view=$user->prefs->get("view");
}
$_autothumb=getvar("_autothumb");
if (empty($_autothumb)) {
    $_autothumb=$user->prefs->get("autothumb");
}

$parent_album_id = getvar("parent_album_id");
if (!$parent_album_id) {
    $album = album::getRoot();
} else {
    $album = new album($parent_album_id);
}

$pagenum = getvar("_pageset_page");

$album->lookup();
$obj=&$album;
$ancestors = $album->getAncestors();
$order = $user->prefs->get("child_sortorder");
$children = $album->getChildren($order);
$totalPhotoCount = $album->getTotalPhotoCount();
$photoCount = $album->getPhotoCount();

$title = $album->get("parent_album_id") ? $album->get("album") : translate("Albums");

require_once "header.inc.php";

try {
    $pageset=$album->getPageset();
    $page=$album->getPage($request_vars, $pagenum);
    $showOrig=$album->showOrig($pagenum);
} catch (pageException $e) {
    $showOrig=true;
    $page=null;
}

?>
<h1>
<?php
if ($user->canEditOrganizers()) {
    ?>
      <ul class="actionlink">
        <li>
            <a href="album.php?_action=new&amp;parent_album_id=<?php
                echo $album->get("album_id") ?>"><?php echo translate("new") ?>
            </a>
        </li>
        <li>
            <a href="album.php?_action=edit&amp;album_id=<?php
                echo $album->get("album_id") ?>">
                <?php echo translate("edit") ?>
          </a>
        </li>
        <?php if ($album->get("coverphoto")): ?>
        <li>
            <a href="album.php?_action=update&amp;album_id=<?php
                echo $album->get("album_id") ?>&amp;coverphoto=NULL">
                <?php echo translate("unset coverphoto") ?>
            </a>
        </li>
        <?php endif; ?>
      </ul>
    <?php
}
?>
    <?php echo $title . "\n" ?>
</h1>
<?php
if ($user->isAdmin()) {
    include "selection.inc.php";
}
if ($album->showPageOnTop()) {
    echo $page;
}
if ($showOrig) {
    ?>
    <div class="main">
      <form class="viewsettings" method="get" action="albums.php">
        <?php echo create_form($request_vars, array ("_view", "_autothumb", "_button")) ?>
        <?php echo translate("Album view", 0) . "\n" ?>
        <?php echo template::createViewPulldown("_view", $_view, true) ?>
        <?php echo translate("Automatic thumbnail", 0) . "\n" ?>
        <?php echo template::createAutothumbPulldown("_autothumb", $_autothumb, true) ?>
      </form>
      <br>
      <h2>
    <?php
    if ($ancestors) {
        while ($parent = array_pop($ancestors)) {
            echo $parent->getLink() . " &gt; ";
        }
    }
    echo $title . "\n";
    ?>
    </h2>
    <?php
    echo $album->displayCoverPhoto();
    ?>
    </p>
    <?php
    if ($album->get("album_description")) {
        ?>
        <div class="description">
            <?php echo $album->get("album_description") ?>
        </div>
        <?php
    }
    $fragment = translate("in this album");
    $sortorder = $album->get("sortorder");
    $sort="";
    if ($sortorder) {
        $sort = "&amp;_order=" . $sortorder;
    }
    if ($totalPhotoCount > 0) {
        if ($totalPhotoCount > $photoCount && $children) {
            ?>
            <ul class="actionlink">
                <li><a href="photos.php?album_id=<?php
                    echo $album->getBranchIds() . $sort ?>">
                  <?php echo translate("view photos") ?>
                </a></li>
            </ul>
            <?php
            $fragment .= " " . translate("or its children");
            if ($totalPhotoCount>1) {
                echo sprintf(translate("There are %s photos"), $totalPhotoCount);
                echo " $fragment.<br>\n";
            } else {
                echo sprintf(translate("There is %s photo"), $totalPhotoCount);
                echo " $fragment.<br>\n";
            }
            $fragment = translate("in this album");
            if (!$album->get("parent_album_id")) { // root album
                $fragment = translate("available");
            }
        }
    }
    if ($photoCount > 0) {
        ?>
          <ul class="actionlink">
            <li><a href="photos.php?album_id=<?php
                echo $album->getId() . $sort ?>">
              <?php echo translate("view photos")?>
            </a></li>
          </ul>
        <?php
        if ($photoCount > 1) {
            echo sprintf(translate("There are %s photos"), $photoCount);
            echo " $fragment.\n";
        } else {
            echo sprintf(translate("There is %s photo"), $photoCount);
            echo " $fragment.\n";
        }
    }
    if ($children) {
        $tpl=new block("view_" . $_view, array(
            "id" => $_view . "view",
            "items" => $children,
            "autothumb" => $_autothumb,
            "topnode" => true,
            "links" => array(
                translate("view photos") => "photos.php?album_id="
            )
        ));
        echo $tpl;
    }
    ?>
    </div>
    <?php
} // if show_orig
if ($album->showPageOnBottom()) {
    echo $page;
}
require_once "footer.inc.php";
?>

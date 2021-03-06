<?php
/**
 * Show overview of users
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

require_once "include.inc.php";

if (!$user->isAdmin()) {
    redirect("zoph.php");
}

$title = translate("Users");
require_once "header.inc.php";
?>
    <h1>
      <ul class="actionlink">
        <li><a href="user.php?_action=new"><?php echo translate("new") ?></a></li>
      </ul>
      <?php echo translate("users") ?>
    </h1>
    <div class="main">
      <table id="users">
<?php
$users = user::getAll();

if ($users) {
    foreach ($users as $u) {
        $u->lookupPerson();
        ?>
        <tr>
          <td>
            <a href="user.php?user_id=<?php echo $u->get("user_id") ?>">
              <?php echo $u->get("user_name") ?>
            </a>
          </td>
          <td>
            <?php echo $u->person->getLink() ?>
          </td>
          <td>
            <ul class="actionlink">
        <?php
        if ((count(album::getNewer($u, $u->getLastNotify())) > 0)) {
            ?>
              <li><a href="notify.php?_action=notify&amp;user_id=<?php
                echo $u->get("user_id") ?>&amp;shownewalbums=1">
                <?php echo translate("Notify User", 0) ?>
              </a></li>
            <?php
        }
        ?>
              <li><a href="user.php?user_id=<?php echo $u->get("user_id") ?>">
                <?php echo translate("display") ?>
              </a></li>
            </ul>
            <?php echo $u->get("lastlogin"); ?>
          </td>
        </tr>
        <?php
    }
}
?>
  </table>
</div>
<?php
require_once "footer.inc.php";
?>

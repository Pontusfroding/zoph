<?php
/*
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
 */

if(!ZOPH) {
    die("Illegal call");
}
?>
    <form enctype="multipart/form-data" action="<?php echo $tpl_action; ?>" method="POST" 
        onSubmit="<?php echo $tpl_onsubmit; ?>">
        <input type="hidden" name="APC_UPLOAD_PROGRESS"
            id="upload_<?php echo $tpl_num; ?>"
            value="<?php echo $tpl_upload_num ?>">
        <input type="hidden" name="_action" value="upload">
        <input type="file" name="file">
        <input type="submit" name="submit" value="<?php echo translate("import", false); ?>">
    </form>

<?php
/**
 * Show the login screen
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
use conf\conf;
use template\template;

define("LOGON", true);

require_once "include.inc.php";

if (isset($_GET["redirect"])) {
    $redirect = urlencode($_GET["redirect"]);
} else {
    $redirect = "";
}
if (conf::get("ssl.force") != "never") {
    if (!array_key_exists('HTTPS', $_SERVER)) {
        redirect(getZophURL("https") . "/logon.php?redirect=" . $redirect, "https required");
    }
}

$user = new user();
$lang=$user->loadLanguage();

$error="";
if (!is_null(getvar("error"))) {
    switch (getvar("error")) {
    case "PWDFAIL":
        $error=translate("You have entered an incorrect username/password combination");
        break;
    }
}

$tpl=new template("logon", array(
    "title"     =>  conf::get("interface.title"),
    "redirect"  =>  $redirect,
    "error"     =>  $error
));

echo $tpl;

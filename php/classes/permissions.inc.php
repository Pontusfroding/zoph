<?php
/**
 * Permissions contain a (group, album) tuple to determine which albums
 * a group can see, along with a few parameters to fine tune
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

/**
 * Permissions contain a (group, album) tuple to determine which albums
 * a group can see, along with a few parameters to fine tune
 *
 * This class corresponds to the group_permissions table which maps a group_id
 * to a ablum_id + access_level + writable flag.  If the user is not an admin,
 * access to any photo must involve a join with this table to make sure the
 * user has access to an album that the photo is in.
 */
class permissions extends zophTable {
    /** @var string The name of the database table */
    protected static $tableName="group_permissions";
    /** @var array List of primary keys */
    protected static $primaryKeys=array("group_id", "album_id");
    /** @var array Fields that may not be empty */
    protected static $notNull=array();
    /** @var bool keep keys with insert. In most cases the keys are set by
                  the db with auto_increment */
    protected static $keepKeys = true;
    /** @var string URL for this class */
    protected static $url="group.php?group_id=";


    /**
     * Create a new permissions object
     * @param int group id
     * @param int album id
     */
    public function __construct($gid = -1, $aid = -1) {
        if ($gid && !is_numeric($gid)) {
            die("group_id must be numeric");
        }
        if ($aid && !is_numeric($aid)) {
            die("album_id must be numeric");
        }
        $this->set("group_id", $gid);
        $this->set("album_id", $aid);
    }

    /**
     * Get the Id of this object
     * since this object has a composite Id, it will return an array
     * @return array [ group_id , album_id ]
     */
    public function getId() {
        return array(
            "group_id" => (int) $this->get("group_id"),
            "album_id" => (int) $this->get("album_id")
            );
    }

    /**
     * Get name of the group in this permission
     * @return string group name
     */
    public function getGroupName() {
        $group=new group($this->get("group_id"));
        $group->lookup();
        return $group->getName();
    }

    /**
     * Get name of the album in this permission
     * @return string album name
     */
    public function getAlbumName() {
        $album=new album($this->get("album_id"));
        $album->lookup();
        return $album->getName();
    }

    /**
     * Insert a new permissions object into the db
     * Because of the way permissions work, if the album in question is a child of another
     * album (which it will be in most cases - except for the root album), this will
     * work it's way up in the album tree, until there is an album that this user already
     * has access to.
     */
    public function insert() {
        // check if this entry already exists
        if ($this->lookup()) {
            return;
        }

        // insert records for ancestor albums if they don't exist
        $album = new album($this->get("album_id"));
        $album->lookup();

        if ($album->get("parent_album_id") > 0) {
            $gp = new self($this->get("group_id"), $album->get("parent_album_id"));

            $gp->set("access_level", $this->get("access_level"));
            $gp->set("watermark_level", $this->get("watermark_level"));
            $gp->set("writable", $this->get("writable"));

            $gp->insert();
        }

        parent::insert();
        $this->permitSubalbums();
    }

    /**
     * Update an already existing permission in the database
     * Permissions are propagated to subalbums if the setting is changed.
     */
    public function update() {
        $current = new self($this->get("group_id"), $this->get("album_id"));
        $current->lookup();
        parent::update();
        if ($current->get("subalbums") === "0" && $this->get("subalbums") === "1") {
            $this->permitSubalbums();
        }
    }

    /**
     * Delete a Permissions object from the db
     * Because of the way permissions work, if the album in question has children,
     * this will work it's way DOWN in the album tree, to remove access rights to
     * any descendant albums.
     */
    public function delete() {

        // delete records for descendant albums if they exist
        $album = new album($this->get("album_id"));
        $album->lookup();

        $children = $album->getChildren();
        foreach ($children as $child) {
            $gp = new self($this->get("group_id"), $child->get("album_id"));

            if ($gp->lookup()) {
                $gp->delete();
            }
        }

        parent::delete();
    }

    /**
     * If this permission has "grant to subalbums" set, we will go through the
     * children of the album in question and add permissions for those albums as
     * well
     */
    private function permitSubalbums() {
        if ($this->get("subalbums")) {
            $this->lookup();
            $album = new album($this->get("album_id"));
            $album->lookup();
            $children = $album->getChildren();
            foreach ($children as $child) {
                $gp = new self($this->get("group_id"), $child->get("album_id"));
                $gp->set("access_level", $this->get("access_level"));
                $gp->set("watermark_level", $this->get("watermark_level"));
                $gp->set("writable", $this->get("writable"));
                $gp->set("subalbums", $this->get("subalbums"));
                $gp->insert();
            }
        }
    }


}

?>

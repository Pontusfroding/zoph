<?php
/**
 * A track is a collection of points, which are used for geotagging
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
 * @author Jeroen Roos
 * @package Zoph
 */

class track extends zoph_table {
    private $points=array();

    /**
     * Create a track object
     *
     * Calling this function without an id, will create a new track, setting
     * the id will make it possible to lookup an existing track from the db
     * @see lookup
     */
    public function __construct($id = 0) {
        if($id && !is_numeric($id)) { die("track_id must be numeric"); }
        parent::zoph_table("track", array("track_id"), array("name"));
        $this->set("track_id", $id);
    }

    /**
     * Insert a track into the database
     */
    public function insert() {
        parent::insert();
        $this->updatePoints();
        $this->insertPoints();
    }

    /**
     * Lookup a track in the database.
     *
     * This will fill the object with the info already in the db
     */
    public function lookup() {
        $result=parent::lookup();
        $this->points=$this->getPoints();
        return $result;
    }

    /**
     * Return the ID of the current object
     *
     * @todo Once the refactoring of the Zoph objects has been done, this
     *       method should be in the zoph_table object
     */
    public function getId() {
        return $this->get("track_id");
    }

    /**
     * Deletes a track
     *
     * Also deletes all point in the track
     * @todo: maybe the deletion of points should be done via the point class?
     * @see point
     */
    public function delete() {
        if(!$this->get("track_id")) { return; }
        parent::delete();
        
        $sql = "delete from " . DB_PREFIX . "point where track_id=";
        $sql .= $this->get("track_id");
    
        query($sql, "Could not remove point from track: ");
    }

    /**
     * Add a new point to a track
     */
    public function addPoint(point $point) {
        $point->set("track_id", $this->get("track_id"));
        $this->points[]=$point;
    }

    /**
     * This sets the track_id on all points in this track
     */
    private function updatePoints() {
        foreach ($this->points as $point) { 
            $point->set("track_id", $this->get("track_id"));
        }
    }
    
    /**
     * Insert points into database
     */
    private function insertPoints() {
        foreach ($this->points as $point) { 
            $point->insert();
        }
    }

    /**
     * Read a GPX file and create track & point objects from there
     */
    public static function getFromGPX($file) {
        $track = new track;
        if(minimum_version("5.1.2") && class_exists("XMLReader")) {
            $xml=new XMLReader();
            $xml->open($file);
            
            $track->set("name", substr($file, strrpos($file, "/") + 1, strrpos($file, ".")));

            $xml->read();
            if($xml->name != "gpx") {
                die("Not a gpx file");
            } else {
                $stack[]="gpx";
            }
            while($xml->read()) {
                if($xml->nodeType==XMLReader::ELEMENT) {
                    // Keep track of the current open tags  
                    if(!$xml->isEmptyElement) {
                        $stack[]=$xml->name;
                    }
                    switch ($xml->name) {
                    case "name":
                        $current=$stack[count($stack) - 2];
                        if($current=="gpx") {
                            // only set the name if we're in <gpx>
                            $xml->read();
                            $track->set("name", $xml->value);
                        }
                        break;
                    case "wpt":
                        // not (yet?) supported
                        $xml->next();
                        array_pop($stack);
                        break;
                    case "trkpt":
                        // For now we are ignoring multiple tracks or segments
                        // in the same file and we simply look at the points
                        $xml_point=$xml->readOuterXML();
                        $point=point::readFromXML($xml_point);
                        $track->addpoint($point);
                        array_pop($stack);
                        $xml->next();
                        break;
                    }
                } else if ($xml->nodeType==XMLReader::END_ELEMENT) {
                    $element=array_pop($stack);
                    if($element!=$xml->name) {
                        die("GPX not well formed: expected &lt;$element&gt;, found &lt;$xml->name&gt;");
                    }
                }
            }
            return $track;
        }
    }

    /**
     * Get all points for this track
     */
    public function getPoints() {
        return point::getAll(array("track_id" => $this->get("track_id")),null,array("="), "datetime");
    }

    /**
     * Create Javascript code for this track
     */
    public function get_js($map="mapstraction") {
        $js="    points=new Array();\n";
        foreach($this->points as $point) {
            $lat=$point->get("lat");
            $lon=$point->get("lon");
            $js .= "    points.push(new mxn.LatLonPoint(" . $lat .", " .$lon . "));\n";
        }
        $js .= "    track=new mxn.Polyline(points);\n";
        $js .= "    " . $map . ".addPolyline(track);\n";
        return $js;
    }
    
    public function getFirstPoint() {
        $points=$this->getPoints();
        $first=$points[0];
        if(($first instanceof point)) {
            return $first;
        } else {
            return new point;
        }
    }

    public function getLastPoint() {
        $points=$this->getPoints();
        $last=end($points);
        if(($last instanceof point)) {
            return $last;
        } else {
            return new point;
        }
    }
    
    public function getPointCount() {
        $points=$this->getPoints();
        return count($points);
    }

    /**
     * Get array that can be used to generate view for this track
     */
    public function getDisplayArray() {
        $first=$this->getFirstPoint();
        $last=$this->getLastPoint();
        $count=$this->getPointCount();
        
        $return[translate("name")] = $this->get("name");
        $return[translate("time of first point")] = $first->get("datetime") . " UTC";
        $return[translate("time of last point")] = $last->get("datetime") . " UTC";
        $return[translate("number of points")] = $count;

        return $return;
    }

    /**
     * Get all tracks
     * @todo should move to zoph_table when refactoring classes
     */
    public static function getAll($constraints = null, $conj = "and", 
        $ops = null, $order = "name") {

        return get_records("track", $order, $constraints, $conj, $ops);
    }

}
?>
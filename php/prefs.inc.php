<?php

/*
 * A class corresponding to the prefs table.  A row of prefs is mapped
 * to a user_id.
 */
class prefs extends zoph_table {

    var $color_scheme;

    function prefs($id = 0) {
        parent::zoph_table("prefs", array("user_id"));
        $this->set("user_id", $id);
    }

    function insert() {
        parent::insert(1); // insert user_id primary key
    }

    function lookup_color_scheme($force = 0) {

        // avoid unnecessary lookups
        if ($this->color_scheme && $this->color_scheme->get("name") != null
            && !$force) {

            return $this->color_scheme;
        }

        if ($this->get("color_scheme_id")) {
            $this->color_scheme =
                new color_scheme($this->get("color_scheme_id"));
            $this->color_scheme->lookup();

            // make sure it was actually found
            if ($this->color_scheme->get("name") != null) {
                return $this->color_scheme;
            }
        }

        return 0;
    }

    function load($force = 0) {

        // these are global vars because originally they were set in
        // config.inc.php instead of stored in the db
        global $SHOW_BREADCRUMBS;
        global $MAX_CRUMBS_TO_SHOW;
        global $DEFAULT_ROWS;
        global $DEFAULT_COLS;
        global $MAX_PAGER_SIZE;
        global $RANDOM_PHOTO_MIN_RATING;
        global $TOP_N;
        global $SLIDESHOW_TIME;

        global $PAGE_BG_COLOR;
        global $TEXT_COLOR;
        global $LINK_COLOR;
        global $VLINK_COLOR;
        global $TABLE_BG_COLOR;
        global $TABLE_BORDER_COLOR;
        global $BREADCRUMB_BG_COLOR;
        global $TITLE_BG_COLOR;
        global $TITLE_FONT_COLOR;
        global $TAB_BG_COLOR;
        global $TAB_FONT_COLOR;
        global $SELECTED_TAB_BG_COLOR;
        global $SELECTED_TAB_FONT_COLOR;

        $SHOW_BREADCRUMBS = $this->get("show_breadcrumbs");
        $MAX_CRUMBS_TO_SHOW = $this->get("num_breadcrumbs");
        $DEFAULT_ROWS = $this->get("num_rows");
        $DEFAULT_COLS = $this->get("num_cols");
        $MAX_PAGER_SIZE = $this->get("max_pager_size");
        $RANDOM_PHOTO_MIN_RATING = $this->get("random_photo_min_rating");
        $TOP_N = $this->get("reports_top_n");
        $SLIDESHOW_TIME = $this->get("slideshow_time");

        if ($this->lookup_color_scheme($force)) {
            $cs = $this->color_scheme;
            $PAGE_BG_COLOR = "#" . $cs->get("page_bg_color");
            $TEXT_COLOR = "#" . $cs->get("text_color");
            $LINK_COLOR = "#" . $cs->get("link_color");
            $VLINK_COLOR = "#" . $cs->get("vlink_color");
            $TABLE_BG_COLOR = "#" . $cs->get("table_bg_color");
            $TABLE_BORDER_COLOR = "#" . $cs->get("table_border_color");
            $BREADCRUMB_BG_COLOR = "#" . $cs->get("breadcrumb_bg_color");
            $TITLE_BG_COLOR = "#" . $cs->get("title_bg_color");
            $TITLE_FONT_COLOR = "#" . $cs->get("title_font_color");
            $TAB_BG_COLOR = "#" . $cs->get("tab_bg_color");
            $TAB_FONT_COLOR = "#" . $cs->get("tab_font_color");
            $SELECTED_TAB_BG_COLOR = "#" . $cs->get("selected_tab_bg_color");
            $SELECTED_TAB_FONT_COLOR = "#" . $cs->get("selected_tab_font_color");
        }
    }

}

?>
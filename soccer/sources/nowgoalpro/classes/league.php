<?php

class NgpLeague {
    public $id = null;
    public $title = null;
    public $title_short = null;
    public $url = null;
    
    public function _construct($id, $url, $title, $title_short) {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
        $this->title_short = $title_short;
    }    
}

?>
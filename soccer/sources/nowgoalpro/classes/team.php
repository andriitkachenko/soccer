<?php
declare(strict_types=1);

class NgpTeam {
    public $id = null;
    public $title = null;
    public $url = null;
    public $rank = null;
    
    public function _construct($id, $url, $title) {
        $this->id = $id;
        $this->url = $url;
        $this->title = $title;
    }
}

?>
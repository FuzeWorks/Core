<?php

use \FuzeWorks\Event;

/**
 * Class routerSetPathEvent
 *
 * Fired when the router's path is changing
 *
 * @package System\Events
 */
class routerSetPathEvent extends Event{

	/**
	 * @var string The new path
	 */
	public $path;

    public function init($path){

        $this->path = $path;
    }
}

?>
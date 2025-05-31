<?php
// Load libSSE via autoloader
require_once '/home/foxholestats/public_html/vendor/autoload.php';

use Sse\Event;
use Sse\SSE;

if(isset($_GET['id']))$id = $_GET['id'];
else $id = 0;

// A simple time event to push server time to clients 
class TimeEvent implements Event {

   var $id;

   function __construct($id)
   {
       $this->id = $id;

   }

	public function check(){
		// Time always updates, so always return true
		return true;
	}

	public function update(){
		// Send formatted time
		return date('l, F jS, Y, h:i:s A'). $this->id;
	}
}

// Create the SSE handler
$sse = new SSE();

// You can limit how long the SSE handler to save resources 
$sse->exec_limit = 10;

// Add the event handler to the SSE handler
$sse->addEventListener('time', new TimeEvent($id));

// Kick everything off!
$sse->start();
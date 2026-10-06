<?php

namespace view;

class DateTimeView {

	/**
	 * Function to return the current date and time.
	 * 
	 * @return string sanitized datetime HTML block
	 */
	public function show() {
		date_default_timezone_set("Europe/Stockholm");
		$timeString = date("l") . ", the " . date("dS") . " of " . date("F Y") . ", The time is " . date("H:i:s");

		return '<p class="datetime-info">' . e($timeString) . '</p>';
	}
}
<?php

class BrokenExternalLink extends DataObject {

	private static $db = array(
		'Link' => 'Varchar(2083)', // 2083 is the maximum length of a URL in Internet Explorer.
		'HTTPCode' =>'Int'
	);

	private static $has_one = array(
		'Page' => 'Page',
		'Track' => 'BrokenExternalLink'
	);

	public static $summary_fields = array(
		'Page.Title' => 'Page',
		'HTTPCode' => 'HTTP Code',
		'Created' => 'Created'
	);

	public static $searchable_fields = array(
		'HTTPCode' => array('title' => 'HTTP Code')
	);

	function canEdit($member = false) {
		return false;
	}

	function canView($member = false) {
		$member = $member ? $member : Member::currentUser();
		$codes = array('content-authors', 'administrators');
		return Permission::checkMember($member, $codes);
	}
}

class BrokenExternalPageTrackStatus extends DataObject {
	private static $db = array(
		'Status' => 'Enum("Completed, Running", "Running")',
		'TotalPages' => 'Int',
		'CompletedPages' => 'Int',
		'JobInfo' => 'Varchar(255)'
	);
}

class BrokenExternalPageTrack extends DataObject {
	private static $db = array(
		'TrackID' => 'Int',
		'Processed' => 'Boolean'
	);

	private static $has_one = array(
		'Page' => 'Page'
	);
}

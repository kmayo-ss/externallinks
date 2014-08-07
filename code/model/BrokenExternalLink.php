<?php

/**
 * Represents a single link checked for a single run that is broken
 *
 * @method BrokenExternalPageTrack Track()
 * @method BrokenExternalPageTrackStatus Status()
 */
class BrokenExternalLink extends DataObject {

	private static $db = array(
		'Link' => 'Varchar(2083)', // 2083 is the maximum length of a URL in Internet Explorer.
		'HTTPCode' =>'Int'
	);

	private static $has_one = array(
		'Track' => 'BrokenExternalPageTrack',
		'Status' => 'BrokenExternalPageTrackStatus'
	);

	/**
	 * @return SiteTree
	 */
	public function Page() {
		return $this->Track()->Page();
	}

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



<?php

class BrokenExternalLinks extends DataObject {

	private static $db = array(
		'Link' => 'Varchar',
		'HTTPCode' =>'Int'
	);

	private static $has_one = array(
		'Page' => 'Page'
	);

	public static $summary_fields = array(
		'Page.Title' => 'Page',
		'HTTPCode' => 'HTTP Code',
		'Created' => 'Created'
	);

	public static $searchable_fields = array(
		'HTTPCode'
	);

	function canEdit($member = false) {
		return false;
	}

}

class BrokenExternalLinksAdmin extends ModelAdmin {

	public static $url_segment = 'broken-external-links-admin';

	public static $managed_models = array(
		'BrokenExternalLinks'
	);

	public static $menu_title = 'Broken Ext. links';

	public function init() {
		parent::init();
	}

}

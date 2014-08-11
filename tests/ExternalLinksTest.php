<?php

class ExternalLinks extends FunctionalTest {

	protected static $fixture_file = 'ExternalLinksTest.yml';

	public function testLinks() {
		// uses http://127.0.0.1 to test a working link
		$working = $this->objFromFixture('SiteTree', 'working');
		$working->write();
		$task = new CheckExternalLinks();
		$task->run(null);
		$brokenLinks = BrokenExternalLink::get()->column('Link');;
		// confirm the working link has not been added as a broken link
		$this->assertNotEquals($working->Link, $brokenLinks[0]);
	}

	public function testBrokenLink() {
		// uses http://192.0.2.1 for a broken link
		$broken = $this->objFromFixture('SiteTree', 'broken');
		$broken->write();
		$task = new CheckExternalLinks();
		$task->run(null);
		$brokenLinks = BrokenExternalLink::get();
		$this->assertEquals(1, $brokenLinks->count());
	}

	public function testReportExists() {
		$mock = $this->objFromFixture('SiteTree', 'broken');
		$reports = SS_Report::get_reports();
		$reportNames = array();
		foreach($reports as $report) {
			$reportNames[] = $report->class;
		}
		$this->assertContains('BrokenExternalLinksReport',$reportNames,
			'BrokenExternalLinksReport is in reports list');
	}
}


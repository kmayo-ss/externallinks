<?php

class ExternalLinks extends FunctionalTest {

	protected static $fixture_file = 'ExternalLinksTest.yml';

	public function testLinks() {
		// uses http://127.0.0.1 to test a working link
		$working = $this->objFromFixture('SiteTree', 'working');
		$working->publish('Stage', 'Live');
		$task = new CheckExternalLinks();
		$task->run(null);
		$brokenLinks = BrokenExternalLink::get();
		$this->assertEquals(0, $brokenLinks->count());
	}

	public function testBrokenLink() {
		// uses http://192.0.2.1 for a broken link
		$broken = $this->objFromFixture('SiteTree', 'broken');
		$broken->publish('Stage', 'Live');
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


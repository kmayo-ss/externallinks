<?php

class ExternalLinks extends FunctionalTest {

	protected static $fixture_file = 'ExternalLinksTest.yml';

	public function testWorkingLink() {
		// uses http://127.0.0.1 to test a working link
		$page = $this->objFromFixture('Page', 'working');
		$task = new CheckExternalLinks();
		$task->run($page);
		$brokenLinks = BrokenExternalLinks::get();
		$this->assertEquals(0, $brokenLinks->count());
	}

	public function testBrokenLink() {
		// uses http://192.0.2.1 for a broken link
		$page = $this->objFromFixture('Page', 'broken');
		$task = new CheckExternalLinks();
		$task->run($page);
		$brokenLinks = BrokenExternalLinks::get();
		$this->assertEquals(1, $brokenLinks->count());
	}

	public function testReportExists() {
		$reports = SS_Report::get_reports();
		$reportNames = array();
		foreach($reports as $report) {
			$reportNames[] = $report->class;
		}
		$this->assertContains('BrokenExternalLinksReport',$reportNames,
			'BrokenExternalLinksReport is in reports list');
	}
}

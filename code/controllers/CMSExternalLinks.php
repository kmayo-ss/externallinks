<?php

class CMSExternalLinks_Controller extends Controller {

	private static $allowed_actions = array('getJobStatus', 'start');

	/*
	 * Respond to Ajax requests for info on a running job
	 *
	 * @return string JSON string detailing status of the job
	 */
	public function getJobStatus() {
		$track = CheckExternalLinks::getLatestTrack();
		if (!$track || !$track->exists()) return null;
		echo json_encode(array(
			'TrackID' => $track->ID,
			'Status' => $track->Status,
			'Completed' => $track->CompletedPages,
			'Total' => $track->TotalPages
		));
	}


	/*
	 * Starts a broken external link check
	 */
	public function start() {
		$status = checkExternalLinks::getLatestTrackStatus();
		// return if the a job is already running
		if ($status == 'Running') {
			return;
		}
		if (class_exists('QueuedJobService')) {
			$pages = Versioned::get_by_stage('SiteTree', 'Stage');
			$noPages = count($pages);

			$track = BrokenExternalPageTrackStatus::create();
			$track->TotalPages = $noPages;
			$track->Status = 'Running';
			$track->write();

			foreach ($pages as $page) {
				$trackPage = BrokenExternalPageTrack::create();
				$trackPage->PageID = $page->ID;
				$trackPage->TrackID = $track->ID;
				$trackPage->write();
			}

			$checkLinks = new CheckExternalLinksJob();
			singleton('QueuedJobService')
				->queueJob($checkLinks, date('Y-m-d H:i:s', time() + 1));
		} else {
			//TODO this hangs as it waits for the connection to be released
			// should return back and continue processing	
			// http://us3.php.net/manual/en/features.connection-handling.php
			$task = new CheckExternalLinks();
			$task->run();
		}
	}
}

<?php

class CMSExternalLinks_Controller extends Controller {

	private static $allowed_actions = array('getJobStatus', 'clear', 'start');

	/*
	 * Respond to Ajax requests for info on a running job
	 * also calls continueJob and clear depending on the status of the job
	 *
	 * @return string JSON string detailing status of the job
	 */
	public function getJobStatus() {
		$trackID = Session::get('ExternalLinksTrackID');
		if (!$trackID) return;
		$noPages = Versioned::get_by_stage('SiteTree', 'Live')->count();
		$result = BrokenExternalPageTrack::get()
			->filter('TrackID', $trackID)
			->exclude('PageID', 0);
		$completedPages = count($result);

		echo json_encode(array(
			'TrackID' => $trackID,
			'Completed' => $completedPages,
			'Total' => $noPages
		));

		if ($completedPages >= $noPages) {
			$this->clear();
		} else {
			$this->continueJob();
		}
	}

	/*
	 * Clears the tracking id and any surplus entries for the BrokenExternalPageTrack model
	 */
	public function clear() {
		// clear any old entries
		$trackID = Session::get('ExternalLinksTrackID');
		$oldEntries = BrokenExternalPageTrack::get()
			->exclude('TrackID', $trackID);
		foreach ($oldEntries as $entry) {
			$entry->delete();
		}
		Session::clear('ExternalLinksTrackID');
	}

	/*
	 * Starts a broken external link check
	 */
	public function start() {
		$track = BrokenExternalPageTrack::create();
		$track->write();
		$track->TrackID = $track->ID;
		$track->write();

		Session::set('ExternalLinksTrackID', $track->ID);

		$this->continueJob();
	}

	/*
	 * Continues a broken external link check
	 */
	public function continueJob() {
		$task = new CheckExternalLinks();
		$task->run(null);
	}
}

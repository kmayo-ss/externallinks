<?php

class CheckExternalLinks extends BuildTask {
	public $limit = 10;

	protected $title = 'Checking broken External links in the SiteTree';

	protected $description = 'A task that records external broken links in the SiteTree';

	protected $enabled = true;

	private $completedPages;
	private $totalPages;

	function run($request) {
		$this->runLinksCheck($this->limit);
	}

	/**
	 * Runs the links checker and returns the track used
	 *
	 * @param int $limit Limit to number of pages to run
	 * @return BrokenExternalPageTrackStatus
	 */
	public function runLinksCheck($limit) {
		$track = CheckExternalLinks::getLatestTrack();

		// if the script has already been started
		if ($track && $track->Status == 'Running') {
			$batch = BrokenExternalPageTrack::get()
				->filter(array(
					'TrackID' => $track->ID,
					'Processed' => 0
				))->limit($limit)->column('PageID');
			$pages = Versioned::get_by_stage('SiteTree', 'Live')
				->filter('ID', $batch)
				->limit($limit);
			$this->updateJobInfo('Fetching pages to check');
			if ($track->CompletedPages == $track->TotalPages) {
				$track->Status = 'Completed';
				$track->write();
				$this->updateJobInfo('Setting to completed');
			}
		// if the script is to be started
		} else {
			$pages = Versioned::get_by_stage('SiteTree', 'Live')->column('ID');
			$noPages = count($pages);

			$track = BrokenExternalPageTrackStatus::create();
			$track->TotalPages = $noPages;
			$track->write();
			$this->updateJobInfo('Creating new tracking object');

			foreach ($pages as $page) {
				$trackPage = BrokenExternalPageTrack::create();
				$trackPage->PageID = $page;
				$trackPage->TrackID = $track->ID;
				$trackPage->write();
			}

			$batch = BrokenExternalPageTrack::get()
				->filter(array(
					'TrackID' => $track->ID
				))->limit($limit)->column('PageID');

			$pages = Versioned::get_by_stage('SiteTree', 'Live')
				->filter('ID', $batch);
		}
		$trackID = $track->ID;
		foreach ($pages as $page) {
			++$this->totalPages;

			if ($track->ID) {
				$trackPage = BrokenExternalPageTrack::get()
					->filter(array(
						'PageID' => $page->ID,
						'TrackID' => $track->ID
					))->first();
				$trackPage->Processed = 1;
				$trackPage->write();
			}

			$htmlValue = Injector::inst()->create('HTMLValue', $page->Content);
			if (!$htmlValue->isValid()) {
				continue;
			}

			// Populate link tracking for internal links & links to asset files.
			if($links = $htmlValue->getElementsByTagName('a')) foreach($links as $link) {
				$class = $link->getAttribute('class');
				$pos = stripos($class, 'ss-broken');
				if ($pos !== false && $page->HasBrokenLink == 1) continue;

				$href = Director::makeRelative($link->getAttribute('href'));
				if ($href == 'admin/') continue;

				// ignore SiteTree, anchor and assets links as they will be caught
				// by SiteTreeLinkTracking
				if(preg_match('/\[(file_link|sitetree_link),id=([0-9]+)\]/i', $href, $matches)) {
					continue;
				} else if (isset($href[0]) && $href[0] == '#') {
					continue;
				} else if(substr($href, 0, strlen(ASSETS_DIR) + 1) == ASSETS_DIR.'/') {
					continue;
				}

				if($href && function_exists('curl_init')) {
					$handle = curl_init($href);
					curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
					curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt($handle, CURLOPT_TIMEOUT, 10);
					$response = curl_exec($handle);
					$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);  
					curl_close($handle);
					// do we have any whitelisted codes
					$ignoreCodes = Config::inst()->get('CheckExternalLinks', 'IgnoreCodes');
					// if the code is whitelisted set it to 200
					$httpCode  = (is_array($ignoreCodes) && in_array($httpCode, $ignoreCodes)) ?
						200 : $httpCode;

					// ignore empty hrefs and internal links
					if (($httpCode < 200 || $httpCode > 302) || ($href == '' || $href[0] == '/')) {
						$brokenLink = new BrokenExternalLink();
						$brokenLink->PageID = $page->ID;
						$brokenLink->Link = $href;
						$brokenLink->HTTPCode = $httpCode;
						$brokenLink->write();

						// set the broken link class
						$class = ($class && stripos($class, 'ss-broken')) ?
							$class . ' ss-broken' : 'ss-broken';
						$link->setAttribute('class', ($class ? $class : 'ss-broken'));
						$htmlValue->__call('saveHTML', array());

						$page->Content = $htmlValue->getContent();
						$page->owner->write();

						if (!$page->HasBrokenLink) {

							// bypass the ORM as syncLinkTracking does not allow you
							// to update HasBrokenLink to true
							$query = "UPDATE \"SiteTree_Live\" SET \"HasBrokenLink\" = 1 ";
							$query .= "WHERE \"ID\" = " . (int)$page->ID;   
							$result = DB::query($query);
							if (!$result) {
								$this->debugMessage('Error updating HasBrokenLink');
							}
						}

					}
				}
			}
			++$this->completedPages;
		}

		// run this outside the foreach loop to stop it locking DB rows
		$this->updateJobInfo('Updating completed pages');
		$this->updateCompletedPages($trackID);

		// do we need to carry on running the job
		$track = $this->getLatestTrack();
		if ($track->CompletedPages >= $track->TotalPages) {
			$track->Status = 'Completed';
			$track->write();

			// clear any old previous data
			$rows = BrokenExternalPageTrack::get()
				->exclude('TrackID', $track->ID);
			foreach ($rows as $row) {
				$row->delete();
			}
			return $track;
		}

			// if running via the queued job module return to the queued job after each iteration
		if ($limit == 1) {
			return $track;
			} else {
				$this->updateJobInfo("Running next batch {$track->CompletedPages}/{$track->TotalPages}");
			return $this->runLinksCheck($limit);
		}
	}

	public static function getLatestTrack() {
		$track = BrokenExternalPageTrackStatus::get()->sort('ID', 'DESC')->first();
		if (!$track || !$track->exists()) return null;
		return $track;
	}

	public static function getLatestTrackID() {
		$track = CheckExternalLinks::getLatestTrack();
		if (!$track || !$track->exists()) return null;
		return $track->ID;
	}

	public static function getLatestTrackStatus() {
		$track = CheckExternalLinks::getLatestTrack();
		if (!$track || !$track->exists()) return null;
		return $track->Status;
	}

	private function updateCompletedPages($trackID = 0) {
		$noPages = BrokenExternalPageTrack::get()
			->filter(array('TrackID' => $trackID, 'Processed' => 1))->count();
		$track = $this->getLatestTrack($trackID);
		$track->CompletedPages = $noPages;
		$track->write();
		return $noPages;
	}

	private function updateJobInfo($message) {
		$track = CheckExternalLinks::getLatestTrack();
		if (!$track || !$track->exists()) return null;
		$track->JobInfo = $message;
		$track->write();
	}
}

<?php

class CheckExternalLinks extends BuildTask {
	protected $title = 'Checking broken External links in the SiteTree';

	protected $description = 'A task that records external broken links in the SiteTree';

	protected $enabled = true;

	private $completedPages;
	private $totalPages;

	public function getCompletedPages() {
		return $this->completedPages;
	}

	public function getTotalPages() {
		return $this->totalPages;
	}

	function run($request) {
		if (isset($request->ID)) {
			$pages = $request;
		} else {
			$pages = Versioned::get_by_stage('SiteTree', 'Live');
		}
		foreach ($pages as $page) {
			++$this->totalPages;

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
					if (($httpCode < 200 || $httpCode > 302)
						|| ($href == '' || $href[0] == '/'))
					{
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

		// run this again if queued jobs exists and is a valid int
		$queuedJob = Config::inst()->get('CheckExternalLinks', 'Delay');
		if (isset($queuedJob) && is_int($queuedJob) && class_exists('QueuedJobService')) {
			$checkLinks = new CheckExternalLinksJob();
			singleton('QueuedJobService')
				->queueJob($checkLinks, date('Y-m-d H:i:s', time() + $queuedJob));
		}

	}
}

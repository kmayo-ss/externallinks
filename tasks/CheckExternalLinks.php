<?php

class CheckExternalLinks extends BuildTask {
	protected $title = 'Checking broken External links in the SiteTree';

	protected $description = 'A task that records external broken links in the SiteTree';

	protected $enabled = true;

	function run($request) {
		// clear broken external link table
		$table = 'BrokenExternalLinks';
		if(method_exists(DB::getConn(), 'clearTable')) DB::getConn()->clearTable($table);
		else DB::query("TRUNCATE \"$table\"");
		$pages = SiteTree::get();
		foreach ($pages as $page) {
			$htmlValue = Injector::inst()->create('HTMLValue', $page->Content);

			// Populate link tracking for internal links & links to asset files.
			if($links = $htmlValue->getElementsByTagName('a')) foreach($links as $link) {
				$href = Director::makeRelative($link->getAttribute('href'));
				if ($href == 'admin/') continue;

				// ignore SiteTree and assets links as they will be caught by SiteTreeLinkTracking
				if(preg_match('/\[sitetree_link,id=([0-9]+)\]/i', $href, $matches)) {
					continue;
				} else if(substr($href, 0, strlen(ASSETS_DIR) + 1) == ASSETS_DIR.'/') {
					continue;
				}
				if($href && function_exists('curl_init')) {
					$handle = curl_init($href);
					curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
					$response = curl_exec($handle);
					$httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);  
					curl_close($handle);
					if (($httpCode < 200 || $httpCode > 302)
						|| ($href == '' || $href[0] == '/'))
					{
						$brokenLink = new BrokenExternalLinks();			
						$brokenLink->PageID = $page->ID;
						$brokenLink->Link = $href;
						$brokenLink->HTTPCode = $httpCode;
						$brokenLink->write();

						// set the broken link class
						/*
						$class = $link->getAttribute('class');
						$class = ($class) ? $class . 'ss-broken' : 'ss-broken';
						$link->setAttribute('class', ($class ? "$class ss-broken" : 'ss-broken'));
						*/

						// use raw sql query to set broken link as calling the dataobject write
						// method will reset the links if no broken internal links are found
						$query = 'UPDATE SiteTree SET HasBrokenLink = 1 ';	
						$query .= 'WHERE ID = ' . (int)$page->ID;	
						$result = DB::query($query);
						if (!$result) {
							// error updating hasBrokenLink
						}

					}
				}
			}
		}

		// run this again in 24 hours if queued jobs exists
		if (class_exists('QueuedJobService')) {
			$checkLinks = new CheckExternalLinksJob();
			singleton('QueuedJobService')
				->queueJob($checkLinks, date('Y-m-d H:i:s', time() + 86400));
		}

	}
}

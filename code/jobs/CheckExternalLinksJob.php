<?php

if(!class_exists('AbstractQueuedJob')) return;

/**
 * A Job for running a external link check for published pages
 *
 */
class CheckExternalLinksJob extends AbstractQueuedJob implements QueuedJob {

	public function getTitle() {
		return _t('CheckExternalLiksJob.TITLE', 'Checking for external broken links');
	}

	public function getJobType() {
		return QueuedJob::QUEUED;
	}

	public function getSignature() {
		return md5(get_class($this));
	}

	/**
	 * Check a individual page
	 */
	public function process() {
		$task = new CheckExternalLinks();
		$pages = Versioned::get_by_stage('SiteTree', 'Live');
		// set the limit so each page is done individually
		$task->limit = 1;
		$this->totalSteps = $pages->count();
		foreach ($pages as $page) {
			$this->currentStep++;
			$task->run();
		}
		$this->isComplete = true;
		return;
	}

}

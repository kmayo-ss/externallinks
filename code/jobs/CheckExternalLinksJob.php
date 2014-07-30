<?php

if(!class_exists('AbstractQueuedJob')) return;

/**
 * A Job for running a external link check for published pages
 *
 */
class CheckExternalLinksJob extends AbstractQueuedJob implements QueuedJob {

	public function __construct() {
		$this->pagesToProcess = Versioned::get_by_stage('SiteTree', 'Live')->column();
		$this->currentStep = 0;
		$this->totalSteps = count($this->pagesToProcess);
	}

	public function getTitle() {
		return _t('CheckExternalLiksJob.TITLE', 'Checking for external broken links');
	}

	public function getJobType() {
		return QueuedJob::QUEUED;
	}

	public function getSignature() {
		return md5(get_class($this));
	}

	public function setup() {
		parent::setup();
		$restart = $this->currentStep == 0;
		if ($restart) {
			$this->pagesToProcess = Versioned::get_by_stage('SiteTree', 'Live')->column();
		}

	}

	/**
	 * Check a individual page
	 */
	public function process() {
		$remainingPages = $this->pagesToProcess;
		if (!count($remainingPages)) {
			$this->isComplete = true;
			return;
		}

		// lets process our first item - note that we take it off the list of things left to do
		$ID = array_shift($remainingPages);

		// get the page
		$page = Versioned::get_by_stage('SiteTree', 'Live', 'ID = '.$ID);

		if (!$page || !$page->Count()) {
			$this->addMessage("Page ID #$ID could not be found, skipping");
		}

		$task = new CheckExternalLinks();
		$task->pageToProcess = $page;
		$task->run();

		// and now we store the new list of remaining children
		$this->pagesToProcess = $remainingPages;
		$this->currentStep++;

		if (!count($remainingPages)) {
			$this->isComplete = true;
			return;
		}
	}

}

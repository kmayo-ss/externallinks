<?php

/**
 * An check external links job
 *
 */
class CheckExternalLinksJob extends AbstractQueuedJob {

	public static $regenerate_time = 43200;

	public function __construct() {
		$this->pagesToProcess = SiteTree::get();
		$this->currentStep = 0;
		$this->totalSteps = count($this->pagesToProcess);
	}

	/**
	 * Sitemap job is going to run for a while...
	 */
	public function getJobType() {
		return QueuedJob::QUEUED;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return 'Checking external links';
	}

	/**
	 * Return a signature for this queued job
	 * 
	 * For the generate sitemap job, we only ever want one instance running, so just use the class name
	 * 
	 * @return String
	 */
	public function getSignature() {
		return md5(get_class($this));
	}

	/**
	 * Note that this is duplicated for backwards compatibility purposes...
	 */
	public function  setup() {
		parent::setup();
		increase_time_limit_to();

		$restart = $this->currentStep == 0;

		if ($restart) {
			$this->pagesToProcess = SiteTree::get();
		}
	}

	/**
	 * On any restart, make sure to check that our temporary file is being created still. 
	 */
	public function prepareForRestart() {
		parent::prepareForRestart();
	}

	public function process() {
		$task = new CheckExternalLinks();
		$task->run();
		$data = $this->getJobData();
		$completedPages = $task->getCompletedPages();
		$totalPages = $task->getTotalPages();
		$this->addMessage("$completedPages/$totalPages pages completed");
		$this->completeJob();
	}

	/**
	 * Outputs the completed file to the site's webroot
	 */
	protected function completeJob() {
		$this->isComplete = 1;
		$nextgeneration = new CheckExternalLinksJob();
		singleton('QueuedJobService')->queueJob($nextgeneration,
			date('Y-m-d H:i:s', time() + self::$regenerate_time));
	}
}
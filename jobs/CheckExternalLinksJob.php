<?php

/**
 * An check external links job
 *
 */
class CheckExternalLinksJob extends AbstractQueuedJob {

	public static $regenerate_time = 43200;

	public function __construct() {
		$this->pagesToProcess = DB::query('SELECT ID FROM "SiteTree_Live" WHERE "ShowInSearch"=1')->column();
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
			$this->pagesToProcess = DB::query('SELECT ID FROM SiteTree_Live WHERE ShowInSearch=1')->column();
		}
	}

	/**
	 * On any restart, make sure to check that our temporary file is being created still. 
	 */
	public function prepareForRestart() {
		parent::prepareForRestart();
	}

	public function process() {
		$task = new CheckExternalLinksTask();
		$task->run();
	}

	/**
	 * Outputs the completed file to the site's webroot
	 */
	protected function completeJob() {

		$nextgeneration = new CheckExternalLinksJob();
		singleton('QueuedJobService')->queueJob($nextgeneration,
			date('Y-m-d H:i:s', time() + self::$regenerate_time));
	}
}
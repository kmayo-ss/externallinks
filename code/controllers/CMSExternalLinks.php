<?php

class CMSExternalLinks_Controller extends Controller {

	private static $allowed_actions = array('createQueuedReport');


	public function createQueuedReport() {
		if (!Permission::check('ADMIN')) return;

		// setup external links job
		$externalLinks = new CheckExternalLinksJob();
		$job = singleton('QueuedJobService');
		$jobID = $job->queueJob($externalLinks);

		// redirect to the jobs page
		$admin = QueuedJobsAdmin::create();
		$this->Redirect($admin->Link());
	}
}

<?php

/**
 * Content side-report listing pages with external broken links
 * @package externallinks
 * @subpackage content
 */

class BrokenExternalLinksReport extends SS_Report {

	/**
	 * Columns in the report
	 * 
	 * @var array
	 * @config
	 */
	private static $columns = array(
		'Created' => 'Checked',
		'Link' => 'External Link',
		'HTTPCode' => 'HTTP Error Code',
		'PageLink' => array(
			'title' => 'Page link is on',
			'link' => true
		),	
	);

	public function init() {
		parent::init();	
	}

	/**
	 * Returns the report title
	 * 
	 * @return string
	 */
	public function title() {
		return _t('ExternalBrokenLinksReport.EXTERNALBROKENLINKS',
			"External broken links report");
	}

	/**
	 * Returns the column names of the report
	 * 
	 * @return array
	 */
	public function columns() {
		return self::$columns;
	}

	/**
	 * Alias of columns(), to support the export to csv action
	 * in {@link GridFieldExportButton} generateExportFileData method.
	 * @return array
	 */
	public function getColumns() {
		return $this->columns();
	}

	public function sourceRecords() {
		$track = CheckExternalLinks::getLatestTrack();
		$returnSet = new ArrayList();
		if ($track && $track->exists()) {
			$links = BrokenExternalLink::get()
				->filter('TrackID', $track->ID);
		} else {
			$links = BrokenExternalLink::get();
		}
		foreach ($links as $link) {
			$link->PageLink = $link->Page()->Title;
			$link->ID = $link->Page()->ID;
			$returnSet->push($link);
		}
		return $returnSet;
	}

	public function getCMSFields() {
		Requirements::javascript('externallinks/javascript/BrokenExternalLinksReport.js');
		$fields = parent::getCMSFields();

		$reportResultSpan = '</ br></ br><h3 id="ReportHolder"></h3>';
		$reportResult = new LiteralField('ResultTitle', $reportResultSpan);
		$fields->push($reportResult);

		$button = '<button id="externalLinksReport" type="button">%s</button>';
		$runReportButton = new LiteralField(
			'runReport',
			sprintf(
				$button,
				_t('ExternalBrokenLinksReport.RUNREPORT', 'Create new report')
			)
		);
		$fields->push($runReportButton);

		return $fields;
	}
}

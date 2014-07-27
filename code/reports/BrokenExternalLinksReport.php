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
		return _t('ExternalBrokenLinksReport.EXTERNALBROKENLINKS',"External broken links report");
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
		$returnSet = new ArrayList();
		$links = BrokenExternalLinks::get();
		foreach ($links as $link) {
			$link->PageLink = $link->Page()->Title;
			$returnSet->push($link);
		}
		return $returnSet;
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		if (class_exists('AbstractQueuedJob')) {
			$button = '<a href = "%s"><button class="externalLinksReport" type="button">%s</button></a>';
			$runReportButton = new LiteralField(
				'runReport',
				sprintf(
					$button,
					'admin/externallinks/createQueuedReport',
					_t('ExternalBrokenLinksReport.RUNREPORT', 'Create new report')
				)
			);
			$fields->push($runReportButton);
			$reportResultSpan = '<span id="ReportHolder"></span></ br><span id="ReportProgress"></span>';
			$reportResult = new LiteralField('ResultTitle', $reportResultSpan);
			$fields->push($reportResult);
		}
		return $fields;
	}
}

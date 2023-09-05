<?php

namespace Logicbrush\FAQPage\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\View\Parsers\URLSegmentFilter;

class QuestionTag extends DataObject
{
	private static $table_name = 'QuestionTag';

	private static array $db = [
		'Title' => 'Varchar(128)',
		'URLSegment' => 'Varchar(128)',
		'SortOrder' => 'Int',
	];

	private static array $has_one = [
		'FAQPage' => FAQPage::class,
	];

	private static array $belongs_many_many = [
		'Questions' => Question::class,
	];

	private static array $summary_fields = [
		'Title',
	];

	private static string $plural_name = 'Tags';
	private static string $singular_name = 'Tag';
	private static string $default_sort = 'SortOrder ASC';

	/**
	 *
	 * @Metrics( crap = 1 )
	 * @return unknown
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->removeByName( 'SortOrder' );
		$fields->removeByName( 'FAQPageID' );
		$fields->removeByName( 'Questions' );
		$fields->removeByName( 'URLSegment' );

		return $fields;
	}


	/**
	 *
	 * @Metrics( crap = 1 )
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();

		$filter = URLSegmentFilter::create();
		$this->URLSegment = $filter->filter( $this->Title );

	}


	/**
	 *
	 * @Metrics( crap = 2 )
	 * @param unknown $action (optional)
	 * @return unknown
	 */
	public function link( $action = null ) {
		if ( ! $this->FAQPage()->exists() ) {
			return false;
		}

		return $this->FAQPage()->Link( 'tag/' . $this->URLSegment );
	}


	/**
	 *
	 * @Metrics( crap = 2 )
	 * @param unknown $action (optional)
	 * @return unknown
	 */
	public function AbsoluteLink( $action = null ) {
		if ( ! $this->FAQPage()->exists() ) {
			return false;
		}

		return $this->FAQPage()->AbsoluteLink( 'tag/' . $this->URLSegment );
	}


}

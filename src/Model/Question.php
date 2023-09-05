<?php

namespace Logicbrush\FAQPage\Model;

use Page;
use PageController;
use SilverStripe\TagField\TagField;

class Question extends Page {

	private static string $icon = 'mysite/images/treeicons/question-page.png';
	private static string $description = 'An faq question page.';
	private static string $singular_name = 'Question';
	private static string $plural_name = 'Questions';
	private static $table_name = 'Question';

	private static bool $show_in_sitetree = false;
	private static array $allowed_children = [];

	private static array $defaults = [
		'ShowInMenus' => false,
	];

	private static array $many_many = [
		'Tags' => QuestionTag::class,
	];

	private static array $summary_fields = [
		'Title',
		'TagsNice',
	];

	/**
	 *
	 * @Metrics( crap = 2 )
	 * @return unknown
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$fields->renameField( 'Title', 'Question' );

		if ( $this->parent ) {
			$tagField = TagField::create(
				'Tags',
				'Tags',
				$this->parent->Tags(),
				$this->Tags()
			)
			->setShouldLazyLoad( true )
			->setCanCreate( false );

			$fields->addFieldToTab( 'Root.Main', $tagField, 'Content' );
		}

		return $fields;
	}


	/**
	 *
	 * @Metrics( crap = 1 )
	 * @return unknown
	 */
	public function getTagsNice() {
		return implode( ', ', $this->Tags()->Column( 'Title' ) );
	}


}


class QuestionController extends PageController {

}

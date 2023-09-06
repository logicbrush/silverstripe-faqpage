<?php
/**
 * src/Model/FAQPage.php
 *
 * @package default
 */


namespace Logicbrush\FAQPage\Model;

use Page;
use PageController;
use SilverStripe\Lumberjack\Forms\GridFieldConfig_Lumberjack;
use SilverStripe\Lumberjack\Model\Lumberjack;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\Requirements;

class FAQPage extends Page {

	private static string $icon_class = 'font-icon-chat';
	private static string $description = 'An faq page that rolls up content from its question children pages.';
	private static string $singular_name = 'FAQ';
	private static string $plural_name = 'FAQs';
	private static $table_name = 'FAQPage';

	private static array $allowed_children = [
		Question::class,
	];

	private static array $extensions = [
		Lumberjack::class,
	];

	private static array $has_many = [
		'Tags' => QuestionTag::class,
	];


	/**
	 *
	 * @Metrics( crap = 2 )
	 * @return unknown
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		if ( $childPages = $fields->fieldByName( 'Root.ChildPages' ) ) {
			$fields->removeByName( 'ChildPages' );

			$fields->insertBefore( $childPages, 'Main' );
		}


		$tagsFieldConfig = GridFieldConfig_RecordEditor::create();
		$tagsFieldConfig = GridFieldConfig::create()
		->addComponent( new GridFieldButtonRow( 'before' ) )
		->addComponent( new GridFieldToolbarHeader() )
		->addComponent( new GridFieldTitleHeader() )
		->addComponent( new GridFieldEditableColumns() )
		->addComponent( new GridFieldDeleteAction() )
		->addComponent( new GridFieldAddNewInlineButton() )
		->addComponent( new GridFieldOrderableRows( 'SortOrder' ) );

		$tagsField = GridField::create(
			'Tags',
			'Tags',
			$this->Tags(),
			$tagsFieldConfig
		);
		$fields->addFieldToTab( 'Root.Tags', $tagsField );


		return $fields;
	}


	/**
	 *
	 * @Metrics( crap = 1 )
	 * @return unknown
	 */
	public function getLumberjackGridFieldConfig() {
		$config = GridFieldConfig_Lumberjack::create();

		$dataColumns = $config->getComponentByType( GridFieldDataColumns::class );

		$dataColumns->setDisplayFields( [
				'Title' => 'Question',
				'TagsNice'=> 'Tags',
			] );

		return $config;
	}


	/**
	 *
	 * @Metrics( crap = 1 )
	 * @return unknown
	 */
	public function getLumberjackTitle() {
		return 'Questions';
	}


}


class FAQPageController extends PageController {

	private static array $allowed_actions = [
		'tag',
		'other',
	];


	/**
	 *
	 * @Metrics( crap = 2, uncovered = true )
	 * @return unknown
	 */
	public function index() {

		Requirements::javascript( 'logicbrush/silverstripe-faqpage:client/dist/js/faq.js' );
		Requirements::css( 'logicbrush/silverstripe-faqpage:client/dist/css/faq.css' );

		$content = $this->AdvancedContent( Question::get()->filter( ['ParentID' => $this->ID] ), false, false );

		return [
			'Content' => DBField::create_field( 'HTMLText', $content ),
		];
	}


	/**
	 *
	 * @Metrics( crap = 12, uncovered = true )
	 * @return unknown
	 */
	public function Tag() {

		$tagUrlSegment = $this->getRequest()->param( 'ID' );

		if ( ! $tagUrlSegment ) {
			return $this->index();
		}

		$tag = QuestionTag::get()->filter( ['URLSegment' => $tagUrlSegment] )->first();

		if ( ! $tag ) {
			return $this->index();
		}

		$content = $this->AdvancedContent( $tag->Questions(), true, true );

		return [
			'Title' => $tag->Title,
			'Content' => DBField::create_field( 'HTMLText', $content ),
		];
	}


	/**
	 *
	 * @Metrics( crap = 2, uncovered = true )
	 * @return unknown
	 */
	public function Other() {

		$taggedQuestions = Question::get()->filter( ['ParentID' => $this->ID, 'Tags.ID:GreaterThan' => 0] );

		$content = $this->AdvancedContent( Question::get()->filter( ['ParentID' => $this->ID, 'Tags.Count()' => 0] ), true, true );

		return [
			'Title' => 'Other',
			'Content' => DBField::create_field( 'HTMLText', $content ),
		];
	}


	/**
	 *
	 * @Metrics( crap = 42, uncovered = true )
	 * @param unknown $questions (optional)
	 * @param unknown $hideForm  (optional)
	 * @param unknown $isTag     (optional)
	 * @return unknown
	 */
	private function AdvancedContent( $questions = [], $hideForm = false, $isTag = false ) {

		$searchText = $this->request->getVar( 'search' ) ?? '';

		if ( ! $hideForm ) {
			$content = $this->Content;
			$content = '<form class="faq-filter-form"><input type="text" class="faq-filter" placeholder="Search the FAQ..." value="' . htmlentities( $searchText ) . '" /></form>';
		} else {
			$content = '<h2>' . htmlentities( $this->Title ) . '</h2>';
		}

		if ( $isTag ) {
			$content .= $this->QuestionsContent( $questions );
			return $content;
		}

		foreach ( $this->Tags() as $tag ) {
			if ( $tag->Questions()->count() ) {
				$content .= '<h2 class="faq-section-heading">' . $tag->Title . '</h2>';
				$content .= $this->QuestionsContent( $tag->Questions() );
			}
		}

		$otherQuestions = Question::get()->filter( ['ParentID' => $this->ID, 'Tags.Count()' => 0] );

		if ( $otherQuestions ) {
			$content .= '<h2 class="faq-section-heading"><a href="' . $this->Link( 'other' ) . '">Other</a></h2>';
			$content .= $this->QuestionsContent( $otherQuestions );
		}

		return $content;

	}


	/**
	 *
	 * @Metrics( crap = 20, uncovered = true )
	 * @param unknown $questions (optional)
	 * @return unknown
	 */
	private function QuestionsContent( $questions = [] ) {
		$content = '<ul class="faq-table-of-contents">';
		foreach ( $questions as $question ) {
			$questionContent = $question->hasMethod( 'Content' ) ? $question->Content() : $question->Content;
			if ( $questionContent ) {
				$content .= '<li><a href="' . $question->Link() . '">' . $question->MenuTitle . '</a>';
				$content .= '<div class="content hidden">';
				$content .= $questionContent;
				$content .= '</div>';
				$content .= '</li>';
			}
		}
		$content .= '</ul>';

		return $content;
	}


}

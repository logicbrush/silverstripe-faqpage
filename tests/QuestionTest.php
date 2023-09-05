<?php

namespace Logicbrush\FAQPage\Tests;


use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Control\Director;
use Logicbrush\FAQPage\Model\Question;
use Logicbrush\FAQPage\Model\QuestionTag;


class QuestionTest extends FunctionalTest
{

	protected $usesDatabase = true;


	/**
	 *
	 */
	function testCanCreateQuestion() {
		$question = new Question();
		$question->write();

		$this->assertEquals( 1, Question::get()->count() );
	}


	/**
	 *
	 */
	public function testQuestionGetCMSFields() {
		$faqPage = new FAQPage();
		$faqPage->write();

		$question = new Question();
		$question->ParentID = $faqPage->ID;
		$question->write();

		$fields = $question->getCMSFields();
		$this->assertNotNull( $fields );
		unset( $fields );
		unset( $question );
	}


	/**
	 *
	 */
	function testCanCreateQuestionTag() {
		$questionTag = new QuestionTag();
		$questionTag->write();

		$this->assertEquals( 1, QuestionTag::get()->count() );
	}


	/**
	 *
	 */
	public function testQuestionTagGetCMSFields() {
		$questionTag = new QuestionTag();
		$questionTag->write();

		$fields = $questionTag->getCMSFields();
		$this->assertNotNull( $fields );
		unset( $fields );
		unset( $questionTag );
	}


	/**
	 *
	 */
	public function testQuestionTagOnBeforeWrite() {
		$questionTag = new QuestionTag();
		$questionTag->Title = 'The future is female';
		$questionTag->write();

		$this->assertEquals( 'the-future-is-female', $questionTag->URLSegment );
	}


	/**
	 *
	 */
	public function testQuestionTagLink() {
		$questionTag = new QuestionTag();
		$questionTag->Title = 'The future is female';
		$questionTag->write();

		$this->assertFalse( $questionTag->Link() );
		$this->assertFalse( $questionTag->AbsoluteLink() );

		$faqPage = new FAQPage();
		$faqPage->Title = 'FAQs';
		$faqPage->write();

		$questionTag->FAQPageID = $faqPage->ID;
		$questionTag->write();

		$this->assertEquals( '/faqs/tag/the-future-is-female', $questionTag->Link() );
		$this->assertEquals( Director::config()->alternate_base_url . 'faqs/tag/the-future-is-female', $questionTag->AbsoluteLink() );
	}


	/**
	 *
	 */
	public function testQuestionTagsNice() {

		$question = new Question();
		$question->write();

		$this->assertEquals( '', $question->getTagsNice() );

		$questionTag1 = new QuestionTag();
		$questionTag1->Title = 'The future is female';
		$questionTag1->write();
		$question->Tags()->add( $questionTag1 );

		$questionTag2 = new QuestionTag();
		$questionTag2->Title = 'Females are strong as hell';
		$questionTag2->write();
		$question->Tags()->add( $questionTag2 );


		$this->assertEquals( 'The future is female, Females are strong as hell', $question->getTagsNice() );
	}


}

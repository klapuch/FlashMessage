<?php
/**
 * @testCase
 * @phpVersion > 7.0.0
 */
namespace Klapuch\FlashMessage\Unit;

use Klapuch\FlashMessage;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class XmlMessage extends Tester\TestCase {
	private $sessions = [];
	/** @var \Klapuch\FlashMessage\Message */
	private $message;

	public function setUp() {
		$this->message = new FlashMessage\XmlMessage($this->sessions);
	}

	public function testSingleFlashing() {
		$this->message->flash('fine', 'success');
		Assert::count(1, $this->sessions);
		Assert::same(['success' => 'fine'], current(current($this->sessions)));
	}

	public function testMultipleFlashingWithoutOverwriting() {
		$this->message->flash('fine', 'success');
		$this->message->flash('wrong', 'danger');
		Assert::count(2, $this->sessions['flashMessage']);
		Assert::same(['success' => 'fine'], current(current($this->sessions)));
		next($this->sessions['flashMessage']);
		Assert::same(['danger' => 'wrong'], current(current($this->sessions)));
	}

	public function testMultipleSameFlashingWithoutOverwriting() {
		$this->message->flash('fine', 'success');
		$this->message->flash('fine', 'success');
		Assert::count(2, $this->sessions['flashMessage']);
		Assert::same(['success' => 'fine'], current(current($this->sessions)));
		next($this->sessions['flashMessage']);
		Assert::same(['success' => 'fine'], current(current($this->sessions)));
	}

	public function testEmptyPrinting() {
		Assert::same(
			'<flashMessages><flashMessage/></flashMessages>',
			$this->message->print()
		);
	}

	public function testEmptyPrintingAsValidXml() {
		Assert::noError(function() {
			new \SimpleXMLElement($this->message->print());
		});
	}

	public function testPrintingSingleMessage() {
		$this->message->flash('fine', 'success');
		$expectation = '<flashMessages>
							<flashMessage>
								<type>success</type>
								<content>fine</content>
							</flashMessage>
						</flashMessages>';
		Assert::same(preg_replace('~\s~', '', $expectation), $this->message->print());
	}

	public function testPrintingMultipleMessages() {
		$this->message->flash('fine', 'success');
		$this->message->flash('wrong', 'danger');
		$expectation = '<flashMessages>
							<flashMessage>
								<type>success</type>
								<content>fine</content>
							</flashMessage>
							<flashMessage>
								<type>danger</type>
								<content>wrong</content>
							</flashMessage>
						</flashMessages>';
		Assert::same(preg_replace('~\s~', '', $expectation), $this->message->print());
	}

	public function testPrintingAsValidXml() {
		$this->message->flash('fine', 'success');
		$this->message->flash('wrong', 'danger');
		$this->message->flash('ěščř<>\'"&:', 'ěščř<>\'"&:');
		Assert::noError(function() {
			new \SimpleXMLElement($this->message->print());
		});
	}

	public function testRemovingAllOldMessagesAfterPrinting() {
		$initialState = $this->sessions;
		$this->message->flash('fine', 'success');
		$this->message->flash('wrong', 'danger');
		Assert::count(2, current($this->sessions));
		$this->message->print();
		Assert::same($initialState, $this->sessions);
	}

	public function testFlashingAfterRemovingWithoutDifferentBehavior() {
		$this->message->flash('fine', 'success');
		$this->message->flash('wrong', 'danger');
		$this->message->print();
		$this->message->flash('cool', 'warning');
		Assert::count(1, $this->sessions);
		Assert::same(['warning' => 'cool'], current(current($this->sessions)));
	}

	public function testRemovingOnlyMessages() {
		$this->sessions['foo'] = 'bar';
		$this->sessions['bar'] = 'foo';
		$initialState = $this->sessions;
		$this->message->flash('fine', 'success');
		$this->message->flash('wrong', 'danger');
		$this->message->print();
		Assert::same($initialState, $this->sessions);
	}

	public function testXss() {
		$this->message->flash('ěščř<>\'"&:', 'ěščř<>\'"&:');
		$expectation = '<flashMessages>
							<flashMessage>
								<type>ěščř&lt;&gt;&apos;&quot;&amp;:</type>
								<content>ěščř&lt;&gt;&apos;&quot;&amp;:</content>
							</flashMessage>
						</flashMessages>';
		Assert::same(preg_replace('~\s~', '', $expectation), $this->message->print());
	}
}

(new XmlMessage())->run();

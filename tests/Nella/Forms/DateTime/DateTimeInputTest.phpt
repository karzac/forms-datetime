<?php
/**
 * Test: Nella\Forms\DateTime\DateTimeInput
 * @testCase
 *
 * This file is part of the Nella Project (http://nella-project.org).
 *
 * Copyright (c) Patrik Votoček (http://patrik.votocek.cz)
 *
 * For the full copyright and license information,
 * please view the file LICENSE.md that was distributed with this source code.
 */

namespace Nella\Forms\DateTime;

use DateTime;
use DateTimeImmutable;
use Tester\Assert;

require __DIR__ . '/../../../bootstrap.php';

class DateTimeInputTest extends \Tester\TestCase
{

	/**
	 * @return array[]|array
	 */
	public function dataValidDateValues()
	{
		return array(
			array(NULL, NULL),
			array(new DateTimeImmutable('1978-01-23 00:00:00'), new DateTimeImmutable('1978-01-23 00:00:00')),
			array(new DateTime('1978-01-23 00:00:00'), new DateTimeImmutable('1978-01-23 00:00:00')),
		);
	}

	/**
	 * @return array[]|array
	 */
	public function dataValidDateTimes()
	{
		return array(
			array(NULL, NULL, NULL),
			array(NULL, '', NULL),
			array('', NULL, NULL),
			array('', '', NULL),
			array('1978-01-23', '12:00', new DateTimeImmutable('1978-01-23 12:00:00')),
		);
	}

	/**
	 * @return array[]|array
	 */
	public function dataInvalidDates()
	{
		return array(
			array(FALSE),
			array('1978/01/23'),
			array(254358000),
		);
	}

	/**
	 * @dataProvider dataValidDateValues
	 *
	 * @param DateTimeImmutable|NULL
	 * @param DateTimeImmutable|NULL
	 */
	public function testValidDateTimes($input, $expected)
	{
		$control = new DateTimeInput;

		$control->setValue($input);

		Assert::equal($expected, $control->getValue());
	}

	/**
	 * @dataProvider dataInvalidDates
	 * @throws \Nette\InvalidArgumentException
	 *
	 * @param string
	 */
	public function testInvalidDateTimes($input)
	{
		$control = new DateTimeInput;
		$control->setValue($input);
	}

	public function testHtml()
	{
		$form = new \Nette\Forms\Form;
		$control = new DateTimeInput;
		$form->addComponent($control, 'datetime');
		$control->setValue(new DateTimeImmutable('1978-01-23 12:00:00'));

		$dq = \Tester\DomQuery::fromHtml((string) $control->getControl());

		Assert::true($dq->has("input[value='1978-01-23']"));
		Assert::true($dq->has("input[value='12:00']"));
	}

	public function testDisabledHtml()
	{
		$form = new \Nette\Forms\Form;
		$control = new DateTimeInput;
		$form->addComponent($control, 'datetime');
		$control->setValue(new DateTimeImmutable('1978-01-23 12:00:00'));
		$control->setDisabled();

		$dq = \Tester\DomQuery::fromHtml((string) $control->getControl());

		Assert::false($dq->has("input[value='1978-01-23']"));
		Assert::false($dq->has("input[value='12:00']"));
	}

	public function testLabelHtml()
	{
		$control = $this->createControl();
		Assert::null($control->getLabelPart());
	}

	/**
	 * @throws \Nette\InvalidArgumentException
	 */
	public function testInvalidControlPart() {
		$control = $this->createControl();
		$control->getControlPart("test");
	}

	public function testLoadHttpDataEmpty()
	{
		$control = $this->createControl();

		Assert::false($control->isFilled());
		Assert::null($control->getValue());
	}

	/**
	 * @dataProvider dataValidDateTimes
	 *
	 * @param mixed
	 * @param DateTimeImmutable|NULL
	 */
	public function testLoadHttpDataValid($date, $time, $expected)
	{
		$control = $this->createControl(array(
			'datetime' => array(
				'date' => $date,
				'time' => $time,
			),
		));

		Assert::equal($expected, $control->getValue());
	}

	public function testLoadHttpDataInvalid()
	{
		$control = $this->createControl(array(
			'datetime' => array(
				'date' => 'test',
				'time' => 'test',
			),
		));

		$control->addRule([$control, 'validateDateTime'], 'test');

		Assert::true($control->isFilled());
		Assert::null($control->getValue());

		$control->validate();

		Assert::true($control->hasErrors());
		Assert::equal(array('test'), $control->getErrors());
	}

	public function testLoadHttpDataInvalidDate()
	{
		$control = $this->createControl(array(
			'datetime' => array(
				'date' => '2015-02-31',
				'time' => '11:59',
			),
		));

		$control->addRule([$control, 'validateDateTime'], 'test');

		Assert::true($control->isFilled());
		Assert::null($control->getValue());

		$control->validate();

		Assert::true($control->hasErrors());
		Assert::equal(array('test'), $control->getErrors());
	}

	public function testLoadHttpDataInvalidTime()
	{
		$control = $this->createControl(array(
			'datetime' => array(
				'date' => '2015-02-28',
				'time' => '11:61',
			),
		));

		$control->addRule([$control, 'validateDateTime'], 'test');

		Assert::true($control->isFilled());
		Assert::null($control->getValue());

		$control->validate();

		Assert::true($control->hasErrors());
		Assert::equal(array('test'), $control->getErrors());
	}

	/**
	 * @throws \Nette\InvalidStateException
	 */
	public function testRegistrationMultiple()
	{
		DateTimeInput::register();
		DateTimeInput::register();
	}

	public function testRegistration()
	{
		DateTimeInput::register();

		$form = new \Nette\Forms\Form;
		$control = $form->addDateTime('test', 'Test');
		Assert::type('Nella\Forms\DateTime\DateTimeInput', $control);
		Assert::equal('test', $control->getName());
		Assert::equal('Test', $control->caption);
		Assert::same($form, $control->getForm());
	}

	public function testRegistrationThroughExtension()
	{
		(new \Nella\Forms\ContainerFactory)->create();
		$form = new \Nette\Forms\Form;
		$control = $form->addDateTime('test', 'Test');
		Assert::type('Nella\Forms\DateTime\DateTimeInput', $control);
	}

	private function createControl($data = array())
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_FILES = array();
		$_POST = $data;

		$form = new \Nette\Forms\Form;
		$control = new DateTimeInput;
		$form->addComponent($control, 'datetime');

		return $control;
	}

}

id(new DateTimeInputTest)->run(isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : NULL);

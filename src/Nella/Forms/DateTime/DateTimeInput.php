<?php
/**
 * This file is part of the Nella Project (http://nella-project.org).
 *
 * Copyright (c) Patrik Votoček (http://patrik.votocek.cz)
 *
 * For the full copyright and license information,
 * please view the file LICENSE.md that was distributed with this source code.
 */

namespace Nella\Forms\DateTime;

use DateTimeImmutable;
use Nette\Forms\Container;
use Nette\Forms\Form;

/**
 * Date time input form control
 *
 * @author Patrik Votoček
 *
 * @property string $value
 */
class DateTimeInput extends \Nette\Forms\Controls\BaseControl
{

	const DEFAULT_DATE_FORMAT = 'Y-m-d';
	const DEFAULT_TIME_FORMAT = 'G:i';

	const FORMAT_PATTERN = '%s %s';

	const NAME_DATE = 'date';
	const NAME_TIME = 'time';

	const VALID = 'Nella\Forms\DateTime\DateTimeInput::validateDateTime';

	/** @var bool */
	private static $registered = FALSE;

	/** @var string */
	private $dateFormat;

	/** @var string */
	private $timeFormat;

	/** @var string */
	private $date;

	/** @var string */
	private $time;

	/**
	 * @param string
	 * @param string
	 * @param string|NULL
	 */
	public function __construct(
		$dateFormat = self::DEFAULT_DATE_FORMAT,
		$timeFormat = self::DEFAULT_TIME_FORMAT,
		$label = NULL
	)
	{
		parent::__construct($label);
		$this->dateFormat = $dateFormat;
		$this->timeFormat = $timeFormat;
	}

	/**
	 * @param \DateTimeInterface|NULL
	 * @return \Nella\Forms\DateTime\DateTimeInput
	 */
	public function setValue($value = NULL)
	{
		if ($value === NULL) {
			$this->date = NULL;
			$this->time = NULL;
			return $this;
		} elseif (!$value instanceof \DateTimeInterface) {
			throw new \Nette\InvalidArgumentException('Value must be DateTimeInterface or NULL');
		}

		$this->date = $value->format($this->dateFormat);
		$this->time = $value->format($this->timeFormat);

		return $this;
	}

	/**
	 * @return \DateTimeImmutable|NULL
	 */
	public function getValue()
	{
		if (empty($this->date) || empty($this->time)) {
			return NULL;
		}

		$format = sprintf(static::FORMAT_PATTERN, $this->dateFormat, $this->timeFormat);
		$datetimeString = sprintf(static::FORMAT_PATTERN, $this->date, $this->time);

		$datetime = DateTimeImmutable::createFromFormat($format, $datetimeString);

		if ($datetime === FALSE || $datetime->format($format) !== $datetimeString) {
			return NULL;
		}

		return $datetime;
	}

	/**
	 * @return bool
	 */
	public function isFilled()
	{
		return !empty($this->date) && !empty($this->time);
	}

	public function loadHttpData()
	{
		$this->date = $this->getHttpData(Form::DATA_LINE, '[' . static::NAME_DATE . ']');
		$this->time = $this->getHttpData(Form::DATA_LINE, '[' . static::NAME_TIME . ']');
	}

	public function getControl()
	{
		return $this->getControlPart(static::NAME_DATE) . $this->getControlPart(static::NAME_TIME);
	}

	public function getControlPart($key)
	{
		$name = $this->getHtmlName();

		if ($key === static::NAME_DATE) {
			$control = \Nette\Utils\Html::el('input')->name($name . '[' . static::NAME_DATE . ']');
			$control->data('nella-date-format', $this->dateFormat);
			$control->value($this->date);
			$control->type('text');

			if ($this->disabled) {
				$control->disabled($this->disabled);
			}

			return $control;
		} elseif ($key === static::NAME_TIME) {
			$control = \Nette\Utils\Html::el('input')->name($name . '[' . static::NAME_TIME . ']');
			$control->data('nella-time-format', $this->timeFormat);
			$control->value($this->time);
			$control->type('text');

			if ($this->disabled) {
				$control->disabled($this->disabled);
			}

			return $control;
		}

		throw new \Nette\InvalidArgumentException('Part ' . $key . ' does not exist');
	}

	public function getLabelPart()
	{
		return NULL;
	}

	/**
	 * @param \Nella\Forms\Control\DateTimeInput
	 * @return bool
	 */
	public function validateDateTime(DateTimeInput $dateTimeInput)
	{
		return $this->isDisabled() || !$this->isFilled() || $this->getValue() !== NULL;
	}

	public static function register()
	{
		if (static::$registered) {
			throw new \Nette\InvalidStateException('DateTimeInput control already registered.');
		}

		static::$registered = TRUE;

		$class = get_called_class();
		$callback = function (
			Container $container,
			$name,
			$label = NULL,
			$dateFormat = self::DEFAULT_DATE_FORMAT,
			$timeFormat = self::DEFAULT_TIME_FORMAT
		) use ($class) {
			$control = new $class($dateFormat, $timeFormat, $label);
			$container->addComponent($control, $name);
			return $control;
		};

		Container::extensionMethod('addDateTime', $callback);
	}

}

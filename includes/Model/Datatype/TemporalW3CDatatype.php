<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use StatusValue;

/**
 * @license GPL-2.0-or-later
 */
class TemporalW3CDatatype extends Datatype {

	const YEAR_FRAG = '\-?(([1-9]\d{3,}))|(0\d{3}))';
	const MONTH_FRAG = '(0[1-9])|(1[0-2])';
	const DAY_FRAG = '(0[1-9])|([12]\d)|(3[01])';
	const HOUR_FRAG = '([01]\d)|(2[0-3])';
	const MINUTE_FRAG = '[0-5]\d';
	const SECOND_FRAG = '([0-5]\d)(\.\d+)?';
	const END_OF_DAY_FRAG = '24:00:00(\.0+)?';
	const TIMEZONE_FRAG = 'Z|(\+|\-)((0\d|1[0-3]):' . self::MINUTE_FRAG . '|14:00)';
	const TIME_REPR = '((' . self::HOUR_FRAG . ':' . self::MINUTE_FRAG . ':' . self::SECOND_FRAG . ')'
					  . '|' . self::END_OF_DAY_FRAG . ')';
	const DATE_REPR = self::YEAR_FRAG . '\-' . self::MONTH_FRAG . '\-' . self::DAY_FRAG;
	const DATE_TIME_REPR = self::DATE_REPR . 'T' . self::TIME_REPR;
	const G_YEAR_MONTH_REPR = self::YEAR_FRAG . '\-' . self::MONTH_FRAG;
	const G_YEAR_REPR = self::YEAR_FRAG;
	const G_MONTH_DAY_REPR = '\-\-' . self::MONTH_FRAG . '\-' . self::DAY_FRAG;
	const G_MONTH_REPR = '\-\-' . self::MONTH_FRAG;
	const G_DAY_REPR = '\-\-\-' . self::DAY_FRAG;
	const REGEX = '/^(' . self::DATE_REPR . '|' .
				  self::G_YEAR_REPR . '|' .
				  self::G_MONTH_REPR . '|' .
				  self::G_DAY_REPR . '|' .
				  self::G_YEAR_MONTH_REPR . '|' .
				  self::G_MONTH_DAY_REPR . '|' .
				  self::TIME_REPR . '|' .
				  self::DATE_TIME_REPR . ')' .
				  '(' . self::TIMEZONE_FRAG . ')?$/';

	public function __construct() {
		parent::__construct( 'teidata.temporal.w3c' );
	}

	/**
	 * @see Datatype::validate
	 *
	 * @param string $attributeName
	 * @param string $attributeValue
	 * @return StatusValue
	 */
	public function validate( $attributeName, $attributeValue ) {
		if ( preg_match( self::REGEX, $attributeValue ) ) {
			return StatusValue::newGood();
		} else {
			return StatusValue::newFatal(
				'tei-validation-temporal-w3c-invalid-value',
				$attributeValue, $attributeName
			);
		}
	}
}

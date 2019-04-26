<?php

namespace MediaWiki\Extension\Tei\Model\Datatype;

use InvalidArgumentException;

/**
 * @license GPL-2.0-or-later
 *
 * Builds a datatype from its definition
 */
class DatatypeFactory {

	/**
	 * @param array $attribute
	 * @return Datatype
	 */
	public static function build( array $attribute ) {
		$datatype = self::buildFromDataRefAndValList(
			$attribute['datatype']['dataRef'],
			$attribute['valList'] ?? []
		);

		$minOccurs = array_key_exists( 'minOccurs', $attribute['datatype'] )
			? $attribute['datatype']['minOccurs']
			: 1;
		$maxOccurs = array_key_exists( 'maxOccurs', $attribute['datatype'] )
			? $attribute['datatype']['maxOccurs']
			: 1;
		if ( $minOccurs !== 1 || $maxOccurs !== 1 ) {
			$datatype = new MultipleValuesDatatype( $datatype, $minOccurs, $maxOccurs );
		}

		return $datatype;
	}

	private static function buildFromDataRefAndValList( array $dataRef, array $valList ) {
		if ( array_key_exists( 'key', $dataRef ) ) {
			$datatypeId = $dataRef['key'];
		} elseif ( array_key_exists( 'name', $dataRef ) ) {
			$datatypeId = $dataRef['name'];
		} else {
			throw new InvalidArgumentException(
				'No datatype id found in ' . json_encode( $dataRef )
			);
		}

		switch ( $datatypeId ) {
			case 'ID':
				return new IDDatatype();
			case 'mw.title':
				return new MediaWikiTitleDatatype();
			case 'teidata.count':
				return new CountDatatype();
			case 'teidata.enumerated':
				if ( $valList ) {
					return new EnumerationDatatype(
						$valList['type'] === 'closed',
						array_keys( $valList['items'] )
					);
				} else {
					return new EnumerationDatatype( false, [] );
				}
			case 'teidata.language':
				return new LanguageDatatype();
			case 'teidata.numeric':
				return new NumericDatatype();
			case 'teidata.outputMeasurement':
				return new OutputMeasurementDatatype();
			case 'teidata.pointer':
				return new MediaWikiTitleDatatype();
			case 'teidata.temporal.w3c':
				return new TemporalW3CDatatype();
			case 'teidata.text':
				return new TextDatatype();
			case 'teidata.truthValue':
				return new TruthValueDatatype();
			case 'teidata.word':
				return new WordDatatype();
			default:
				throw new InvalidArgumentException(
					'Not supported datatype for in ' . json_encode( $dataRef )
				);
		}
	}
}

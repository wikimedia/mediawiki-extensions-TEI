<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel\Evaluation;

use MediaWiki\Extension\Tei\Model\ContentModel\ContentModel;
use RequestContext;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * Validates a list according to a ContentModel
 *
 * TODO: add caching
 */
class ContentModelValidator {

	private $messagePrefix;

	private $getElementsForGroup;

	/**
	 * @param string $messagePrefix prefix for validation error messages like 'tei-validation-tag-'
	 * @param callable $getElementsForGroup return the elements names for a group name
	 */
	public function __construct( $messagePrefix, callable $getElementsForGroup ) {
		$this->messagePrefix = $messagePrefix;
		$this->getElementsForGroup = $getElementsForGroup;
	}

	/**
	 * Evaluates a list of node labels according to a ContentModel
	 *
	 * @param ContentModel $contentModel
	 * @param string[] $nodeLabels The label of text nodes should be #text
	 * @param string ...$errorMessageArgs args to add to the error messages
	 * @return StatusValue
	 */
	public function validate( ContentModel $contentModel, array $nodeLabels, ...$errorMessageArgs ) {
		$automaton = ThompsonAutomatonBuilder::build( $contentModel, $this->getElementsForGroup );

		$currentStates = $automaton->initialStates();
		foreach ( $nodeLabels as $nodeLabel ) {
			$currentStates = $automaton->applyTransition( $currentStates, $nodeLabel );

			// We have just read a tag that is not known
			if ( empty( $currentStates ) ) {
				if ( $nodeLabel === '#text' ) {
					return StatusValue::newFatal(
						$this->messagePrefix . 'unexpected-text',
						...$errorMessageArgs
					);
				} else {
					return StatusValue::newFatal(
						$this->messagePrefix . 'unexpected-node',
						$nodeLabel, ...$errorMessageArgs
					);
				}
			}
		}

		// We are not in an accepting state, some data is missing
		if ( !$this->isInAcceptingState( $currentStates ) ) {
			$expectedTags = $automaton->transitionsLabelsFromStates( $currentStates );
			return StatusValue::newFatal(
				$this->messagePrefix . 'too-short',
				RequestContext::getMain()->getLanguage()->commaList( $expectedTags ),
				...$errorMessageArgs
			);
		}

		return StatusValue::newGood();
	}

	private function isInAcceptingState( array $states ) {
		return in_array( NondeteministicFiniteAutomaton::ACCEPTING_STATE, $states );
	}
}

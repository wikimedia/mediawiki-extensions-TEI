<?php

namespace MediaWiki\Extension\Tei\Model\Content;

use RequestContext;
use StatusValue;

/**
 * @license GPL-2.0-or-later
 *
 * Validates a list according to a content model
 */
class ContentValidator {

	/**
	 * @var NondeteministicFiniteAutomaton
	 */
	private $automaton;

	/**
	 * @var string
	 */
	private $messagePrefix;

	/**
	 * @param NondeteministicFiniteAutomaton $automaton
	 * @param string $messagePrefix prefix for validation error messages like 'ext.tei.editor'
	 */
	public function __construct( NondeteministicFiniteAutomaton $automaton, $messagePrefix ) {
		$this->automaton = $automaton;
		$this->messagePrefix = $messagePrefix;
	}

	/**
	 * Evaluates a list of node labels according to a ContentModel
	 *
	 * @param string[] $nodeLabels The label of text nodes should be #text
	 * @param string ...$errorMessageArgs args to add to the error messages
	 * @return StatusValue
	 */
	public function validate( array $nodeLabels, ...$errorMessageArgs ) {
		$currentStates = $this->automaton->initialStates();
		foreach ( $nodeLabels as $nodeLabel ) {
			$currentStates = $this->automaton->applyTransition( $currentStates, $nodeLabel );

			// We have just read a tag that is not known
			if ( !$currentStates ) {
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
			$expectedTags = $this->automaton->transitionsLabelsFromStates( $currentStates );
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

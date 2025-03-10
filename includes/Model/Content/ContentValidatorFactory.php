<?php

namespace MediaWiki\Extension\Tei\Model\Content;

use InvalidArgumentException;
use MediaWiki\Extension\Tei\Model\TeiRegistry;

/**
 * @license GPL-2.0-or-later
 *
 * Builds ContentValidator from TeiRegistry
 *
 * Text nodes have the #text label
 *
 * @see https://en.wikipedia.org/wiki/Thompson%27s_construction for the automaton construction
 */
class ContentValidatorFactory {

	/**
	 * @var TeiRegistry
	 */
	private $registry;

	/**
	 * @var ContentValidator[]
	 */
	private $cache = [];

	/**
	 * @param TeiRegistry $registry
	 */
	public function __construct( TeiRegistry $registry ) {
		$this->registry = $registry;
	}

	/**
	 * @param string $elementIdent
	 * @return ContentValidator
	 */
	public function getForElement( $elementIdent ) {
		if ( !array_key_exists( $elementIdent, $this->cache ) ) {
			$this->cache[$elementIdent] = $this->buildForElement( $elementIdent );
		}
		return $this->cache[$elementIdent];
	}

	/**
	 * @param string $elementIdent
	 * @return ContentValidator
	 */
	private function buildForElement( $elementIdent ) {
		$automaton = new NondeteministicFiniteAutomaton();
		$this->addPart(
			$this->registry->getElementSpecFromIdent( $elementIdent )->getContent(),
			NondeteministicFiniteAutomaton::INITIAL_STATE,
			NondeteministicFiniteAutomaton::ACCEPTING_STATE,
			$automaton
		);
		return new ContentValidator( $automaton, 'tei-validation-element-content-' );
	}

	/**
	 * @param array $part
	 * @param int $startState
	 * @param int $endState
	 * @param NondeteministicFiniteAutomaton $automaton
	 */
	private function addPart(
		array $part, $startState, $endState, NondeteministicFiniteAutomaton $automaton
	) {
		$minOccurs = $part['minOccurs'] ?? 1;
		$maxOccurs = array_key_exists( 'maxOccurs', $part ) ? $part['maxOccurs'] : 1;

		// Special case: $maxOccurs == 1
		if ( $maxOccurs === 1 ) {
			$this->addInnerPart( $part, $startState, $endState, $automaton );
			if ( $minOccurs === 0 ) {
				$automaton->addEpsilonTransition( $startState, $endState );
			}
			return;
		}

		// We apply all $minOccurs to get it to 0
		while ( $minOccurs > 0 ) {
			$newState = $automaton->newState();
			$this->addInnerPart( $part, $startState, $newState, $automaton );
			$startState = $newState;

			$minOccurs--;
			if ( $maxOccurs !== null ) {
				$maxOccurs--;
			}
		}

		// The $minOccurs is 0
		$automaton->addEpsilonTransition( $startState, $endState );

		if ( $maxOccurs === null ) {
			// kleene start
			$loopStartState = $automaton->newState();
			$loopEndState = $automaton->newState();
			$automaton->addEpsilonTransition( $startState, $loopStartState );
			$this->addInnerPart( $part, $loopStartState, $loopEndState, $automaton );
			$automaton->addEpsilonTransition( $loopEndState, $loopStartState );
			$automaton->addEpsilonTransition( $loopEndState, $endState );
		} else {
			// Limited
			$currentState = $automaton->newState();
			$automaton->addEpsilonTransition( $startState, $currentState );

			for ( $i = 0; $i < $maxOccurs; $i++ ) {
				$newState = $automaton->newState();
				$this->addInnerPart( $part, $currentState, $newState, $automaton );
				$currentState = $newState;
				$automaton->addEpsilonTransition( $currentState, $endState );
			}
		}
	}

	/**
	 * @param array $part
	 * @param int $startState
	 * @param int $endState
	 * @param NondeteministicFiniteAutomaton $automaton
	 */
	private function addInnerPart(
		array $part, $startState, $endState, NondeteministicFiniteAutomaton $automaton
	) {
		switch ( $part['type'] ) {
			case 'alternate':
				foreach ( $part['content'] as $alternate ) {
					$newStart = $automaton->newState();
					$newEnd = $automaton->newState();
					$automaton->addEpsilonTransition( $startState, $newStart );
					$this->addPart( $alternate, $newStart, $newEnd, $automaton );
					$automaton->addEpsilonTransition( $newEnd, $endState );
				}
				break;
			case 'classRef':
				foreach ( $this->registry->getElementIdentsInClass( $part['key'] ) as $tag ) {
					$automaton->addTransition( $startState, $endState, $tag );
				}
				$automaton->addTransition( $startState, $endState, $part['key'] );
				break;
			case 'elementRef':
				$automaton->addTransition( $startState, $endState, $part['key'] );
				break;
			case 'empty':
				$automaton->addEpsilonTransition( $startState, $endState );
				break;
			case 'macroRef':
				$macro = $this->registry->getMacroSpecFromIdent( $part['key'] );
				$this->addPart( $macro->getContent(), $startState, $endState, $automaton );
				break;
			case 'sequence':
				$sequence = $part['content'];
				$len = count( $sequence );
				$currentState = $startState;
				for ( $i = 0; $i < $len; $i++ ) {
					$currentStart = $currentState;
					$currentEnd = ( $i === $len - 1 ) ? $endState : $automaton->newState();
					$this->addPart( $sequence[$i], $currentStart, $currentEnd, $automaton );
					$currentState = $currentEnd;
				}
				break;
			case 'textNode':
				$automaton->addTransition( $startState, $endState, '#text' );
				break;
			default:
				throw new InvalidArgumentException(
					'Unexpected content model content: ' . json_encode( $part )
				);
		}
	}
}

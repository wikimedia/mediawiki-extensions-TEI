<?php

namespace MediaWiki\Extension\Tei\Model\ContentModel\Evaluation;

use MediaWiki\Extension\Tei\Model\ContentModel\AlternateContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ClassRefContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\ElementRefContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\EmptyContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\RepeatableContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\SequenceContentModel;
use MediaWiki\Extension\Tei\Model\ContentModel\TextNodeContentModel;

/**
 * @license GPL-2.0-or-later
 *
 * Builds the Thompson automaton for the ContentModel using the callback to get groups.
 *
 * Text nodes have the #text label
 *
 * @see https://en.wikipedia.org/wiki/Thompson%27s_construction
 */
class ThompsonAutomatonBuilder {

	/**
	 * @param ContentModel $regex
	 * @param callable $getElementsForGroup return the elements names for a group name
	 * @return NondeteministicFiniteAutomaton
	 */
	public static function build( ContentModel $regex, callable $getElementsForGroup ) {
		$builder = new ThompsonAutomatonBuilder( $getElementsForGroup );
		$builder->addPart(
			$regex,
			NondeteministicFiniteAutomaton::INITIAL_STATE,
			NondeteministicFiniteAutomaton::ACCEPTING_STATE
		);
		return $builder->automaton;
	}

	/**
	 * @var callable
	 */
	private $getElementsForGroup;

	/**
	 * @var NondeteministicFiniteAutomaton
	 */
	private $automaton;

	private function __construct( callable $getElementsForGroup ) {
		$this->getElementsForGroup = $getElementsForGroup;
		$this->automaton = new NondeteministicFiniteAutomaton();
	}

	/**
	 * @param ContentModel $part
	 * @param int $startState
	 * @param int $endState
	 */
	private function addPart( ContentModel $part, $startState, $endState ) {
		if ( $part instanceof AlternateContentModel ) {
			foreach ( $part->getAlternate() as $alternate ) {
				$newStart = $this->automaton->newState();
				$newEnd = $this->automaton->newState();
				$this->automaton->addEpsilonTransition( $startState, $newStart );
				$this->addPart( $alternate, $newStart, $newEnd );
				$this->automaton->addEpsilonTransition( $newEnd, $endState );
			}
		} elseif ( $part instanceof ClassRefContentModel ) {
			$getElementsForGroup = $this->getElementsForGroup;
			foreach ( $getElementsForGroup( $part->getKey() ) as $tag ) {
				$this->automaton->addTransition( $startState, $endState, $tag );
			}
			$this->automaton->addTransition( $startState, $endState, $part->getKey() );
		} elseif ( $part instanceof ElementRefContentModel ) {
			$this->automaton->addTransition( $startState, $endState, $part->getKey() );
		} elseif ( $part instanceof EmptyContentModel ) {
			$this->automaton->addEpsilonTransition( $startState, $endState );
		} elseif ( $part instanceof RepeatableContentModel ) {
			$element = $part->getElement();
			$minOccurs = $part->getMinOccurs();
			$maxOccurs = $part->getMaxOccurs();

			// We apply all $minOccurs to get it to 0
			while ( $minOccurs > 0 ) {
				$newState = $this->automaton->newState();
				$this->addPart( $element, $startState, $newState );
				$startState = $newState;

				$minOccurs--;
				$maxOccurs--;
			}

			// The $minOccurs is 0
			$this->automaton->addEpsilonTransition( $startState, $endState );

			if ( $maxOccurs === null ) {
				// kleene start
				$loopStartState = $this->automaton->newState();
				$loopEndState = $this->automaton->newState();
				$this->automaton->addEpsilonTransition( $startState, $loopStartState );
				$this->addPart( $element, $loopStartState, $loopEndState );
				$this->automaton->addEpsilonTransition( $loopEndState, $loopStartState );
				$this->automaton->addEpsilonTransition( $loopEndState, $endState );
			} else {
				// Limited
				$currentState = $this->automaton->newState();
				$this->automaton->addEpsilonTransition( $startState, $currentState );

				for ( $i = 0; $i < $maxOccurs; $i++ ) {
					$newState = $this->automaton->newState();
					$this->addPart( $element, $currentState, $newState );
					$currentState = $newState;
					$this->automaton->addEpsilonTransition( $currentState, $endState );
				}
			}
		} elseif ( $part instanceof SequenceContentModel ) {
			$sequence = $part->getSequence();
			$len = count( $sequence );
			$currentState = $startState;
			for ( $i = 0; $i < $len; $i++ ) {
				$currentStart = $currentState;
				$currentEnd = ( $i === $len - 1 ) ? $endState : $this->automaton->newState();
				$this->addPart( $sequence[$i], $currentStart, $currentEnd );
				$currentState = $currentEnd;
			}
		} elseif ( $part instanceof TextNodeContentModel ) {
			$this->automaton->addTransition( $startState, $endState, '#text' );
		}
	}
}

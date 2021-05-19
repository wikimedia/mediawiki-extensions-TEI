<?php

namespace MediaWiki\Extension\Tei\Model\Content;

/**
 * @license GPL-2.0-or-later
 *
 * @see https://en.wikipedia.org/wiki/Thompson%27s_construction
 *
 * A nondeterministic finite automaton such that:
 *
 * 1. All states are identified by consecutive integers.
 * To create a new state call the newState() method.
 *
 * 2. There is exactly one initial state already created which id is INITIAL_STATE.
 *
 * 3. There is exactly one accepting state which id is ACCEPTING_STATE.
 */
class NondeteministicFiniteAutomaton {
	const INITIAL_STATE = 0;
	const ACCEPTING_STATE = 1;

	/**
	 * @var int[][][] from state => label => to states
	 */
	private $transitionsTable = [
		// Initial state
		[],
		// Accepting state
		[]
	];

	/**
	 * Adds a new state to the automaton and return it.
	 *
	 * @return int
	 */
	public function newState() {
		$tableSize = count( $this->transitionsTable );
		$this->transitionsTable[] = [];
		return $tableSize;
	}

	/**
	 * Adds a new transition from $from to $to labelled $label to the automaton
	 *
	 * @param int $from
	 * @param int $to
	 * @param mixed $label
	 */
	public function addTransition( $from, $to, $label ) {
		$this->transitionsTable[$from][$label][] = $to;
	}

	/**
	 * Adds an epsilon transition from $from to $to labelled $label to the automaton
	 *
	 * @param int $from
	 * @param int $to
	 */
	public function addEpsilonTransition( $from, $to ) {
		$this->transitionsTable[$from][null][] = $to;
	}

	/**
	 * Apply a given transition to move from a set of state to an other set of state
	 *
	 * @param int[] $fromStates
	 * @param mixed $label
	 * @return int[]
	 */
	public function applyTransition( array $fromStates, $label ) {
		$toStates = [];
		foreach ( $fromStates as $fromState ) {
			if (
				!array_key_exists( $fromState, $this->transitionsTable ) ||
				!array_key_exists( $label, $this->transitionsTable[$fromState] )
			) {
				continue;
			}
			foreach ( $this->transitionsTable[$fromState][$label] as $toState ) {
				if ( !in_array( $toState, $toStates ) ) {
					$toStates[] = $toState;
					$this->extendArrayWithEpsilonTransitionsFrom( $toStates, $toState );
				}
			}
		}
		return $toStates;
	}

	private function extendArrayWithEpsilonTransitionsFrom( array &$states, $start ) {
		if (
			!array_key_exists( $start, $this->transitionsTable ) ||
			// @phan-suppress-next-line PhanTypeMismatchArgumentInternalProbablyReal
			!array_key_exists( null, $this->transitionsTable[$start] )
		) {
			return;
		}
		// @phan-suppress-next-line PhanTypeMismatchDimFetchNullable
		foreach ( $this->transitionsTable[$start][null] as $additionalState ) {
			if ( !in_array( $additionalState, $states ) ) {
				$states[] = $additionalState;
				$this->extendArrayWithEpsilonTransitionsFrom( $states, $additionalState );
			}
		}
	}

	/**
	 * @return int[] the initial states after having applied epsilon transitions
	 */
	public function initialStates() {
		$states = [ self::INITIAL_STATE ];
		$this->extendArrayWithEpsilonTransitionsFrom( $states, self::INITIAL_STATE );
		return $states;
	}

	/**
	 * Returns the labels of the transition from a given state
	 *
	 * @param int $state
	 * @return mixed[]
	 */
	private function transitionsLabelsFromState( $state ) {
		return array_filter( array_keys( $this->transitionsTable[$state] ), static function ( $label ) {
			return $label != null;
		} );
	}

	/**
	 * Returns the labels of the transition from a given set of states
	 *
	 * @param int[] $states
	 * @return mixed[]
	 */
	public function transitionsLabelsFromStates( array $states ) {
		return array_unique( array_merge( ...array_map( function ( $state ) {
			return $this->transitionsLabelsFromState( $state );
		}, $states ) ) );
	}
}

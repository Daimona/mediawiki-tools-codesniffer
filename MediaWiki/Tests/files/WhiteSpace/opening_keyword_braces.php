<?php

/**
 * Failed examples.
 * @param array $arg An arg
 * @return array
 */
function wfFailedExamples( $arg ) {
	list  ( $one, $two ) = $arg;
	if ( isset
		( $arg['three'] ) ) {
		unset	( $arg['test'] );
		return $one;
	}
	return $two;
}

/**
 * Passed examples.
 * @param array $arg An arg
 * @return array
 */
function wfPassedExamples( array $arg ) {
	list( $one, $two ) = $arg;
	if ( isset( $arg['three'] ) ) {
		unset( $arg['test'] );
		return $one;
	}
	return $two;
}
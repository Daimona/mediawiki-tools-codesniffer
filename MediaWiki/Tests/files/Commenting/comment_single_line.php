<?php

// @phpcs:disable MediaWiki.Commenting.DocComment

/**
 * @return void
 */
function wfFailedExamples() {
	/* This also should fail **/
}

/**
 * @return void
 */
function wfPassedExamples() {
	/* Correct inline comment */
	/** This is valid */
	/** @var This is valid */
	/*** This is also valid */
}

/*
 * One asterisk, aligned properly
 */
/*
* One asterisk, misaligned
*/
/**
 * Two asterisks, aligned properly
 */
/**
* Two asterisks, misaligned
*/

/* Single line comment missing the end
disabledCode();
/* Next single line comment */

/*
 * Block comment missing the end
disabledCode();
/* Next single line comment */

<?php

/**
 * Not a Test file
 * @coversDefaultClass Test
 */
class NonTestExamples {
	/**
	 * not a test function
	 * @cover Test
	 */
	public function noop() {
	}

	/**
	 * @backupGlobals
	 */
	public function testForbiddenAnnotation() {
	}

	/** Bad comment
	 * @coversNothing
	 */
}

/**
 * A Test file
 * @coverDefaultClass Test
 * @small
 */
class ExamplesTest {

	/**
	 * @cover this::testTagTypos()
	 * @covers
	 */
	public function testTagTypos() {
	}

	/**
	 * @coverNothing
	 */
	public function testNothing() {
	}

	/**
	 * @after
	 */
	public function isAfterTest() {
	}

	/**
	 * @dataProvider isAfterTest
	 */
	public function notATestNamedFunction() {
	}

	/**
	 * @after
	 */
	public function extraTearDown() {
	}

	/**
	 * @expectedException \Exception
	 * @expectedExceptionMessage Foobar
	 */
	public function expectedAnnotations() {
	}
}

trait TestTrait {
	/**
	 * @dataProvider provideNothing
	 */
	public function testNothing() {
	}
}

/**
 * Not a class comment
 * @group Test
 */

/**
 * interface comment
 * @group Test
 */
interface InterfaceTesting {
	/**
	 * @code{.dot}
	 * @endcode
	 *
	 * @dataProvider provideNothing
	 */
	public function testNothing();
}

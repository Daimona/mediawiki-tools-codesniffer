<?php
namespace FooBar;

use FooBar\Baz;
use Wikimedia\Database;
use Something\That\Is\Unused;
use Something\That\Is\Used;
use Something\Something;
use Something\OneTwo as ThreeFour;
use Something\InAVar;
use Something\InAVar2;
use Something\InAVar3;
use Something\InAVar4;
use Something\InAThrows;
use Something\InAExpectedException;
use Something\InAParam;

$a = new Baz();
$b = new Used();
$c = new ThreeFour();

/**
 * @expectedException InAExpectedException
 * @throws InAThrows
 * @param InAParam $a A variable
 * @return Database
 */
function getDatabase( $a ) {
	return;
}

class Foo {
	use SomeThing;
	use AnotherThing;

	/**
	 * @var InAVar
	 */
	private $thing;

	/**
	 * @var InAVar2|null
	 */
	private $thing2;

	/**
	 * @var null|InAVar3
	 */
	private $thing3;

	/**
	 * @var InAVar4[]
	 */
	private $thing4;
}

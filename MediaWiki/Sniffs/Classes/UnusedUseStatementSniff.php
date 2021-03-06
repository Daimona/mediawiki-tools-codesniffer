<?php
/**
 * Originally from Drupal's coding standard <https://github.com/klausi/coder>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 */

namespace MediaWiki\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class UnusedUseStatementSniff implements Sniff {

	/**
	 * Doc tags where a class name is used
	 *
	 * @var string[]
	 */
	private $classTags = [
		'@expectedException',
		'@param',
		'@return',
		'@throws',
		'@var',
		'@property',
		// Deprecated
		'@type',
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		return [ T_USE ];
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int $stackPtr The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Only check use statements in the global scope.
		if ( !empty( $tokens[$stackPtr]['conditions'] ) ) {
			return;
		}

		// Seek to the end of the statement and get the string before the semi colon.
		$semiColon = $phpcsFile->findEndOfStatement( $stackPtr );
		if ( $tokens[$semiColon]['code'] !== T_SEMICOLON ) {
			return;
		}

		$classPtr = $phpcsFile->findPrevious(
			Tokens::$emptyTokens,
			( $semiColon - 1 ),
			null,
			true
		);

		if ( $tokens[$classPtr]['code'] !== T_STRING ) {
			return;
		}

		// Search where the class name is used. PHP treats class names case
		// insensitive, that's why we cannot search for the exact class name string
		// and need to iterate over all T_STRING tokens in the file.
		$classUsed = $semiColon + 1;
		$className = $tokens[$classPtr]['content'];

		// Check if the referenced class is in the same namespace as the current
		// file. If it is then the use statement is not necessary.
		$namespacePtr = $phpcsFile->findPrevious( [ T_NAMESPACE ], $stackPtr - 3 );
		// Check if the use statement does aliasing with the "as" keyword. Aliasing
		// is allowed even in the same namespace.
		$aliasUsed = $phpcsFile->findPrevious( T_AS, $classPtr - 1, $stackPtr + 3 );

		$useNamespacePtr = $phpcsFile->findNext( [ T_STRING ], ( $stackPtr + 1 ) );
		$useNamespaceEnd = $phpcsFile->findNext(
			[
				T_NS_SEPARATOR,
				T_STRING,
			],
			( $useNamespacePtr + 1 ),
			null,
			true
		);
		$use_namespace = rtrim(
			$phpcsFile->getTokensAsString( $useNamespacePtr, ( $useNamespaceEnd - $useNamespacePtr - 1 ) ),
			'\\'
		);

		if ( $namespacePtr !== false && $aliasUsed === false ) {
			$nsEnd = $phpcsFile->findNext(
				[
					T_NS_SEPARATOR,
					T_STRING,
					T_WHITESPACE,
				],
				( $namespacePtr + 1 ),
				null,
				true
			);
			$namespace = trim(
				$phpcsFile->getTokensAsString( ( $namespacePtr + 1 ), ( $nsEnd - $namespacePtr - 1 ) )
			);

			if ( strcasecmp( $namespace, $use_namespace ) === 0 ) {
				$classUsed = $phpcsFile->numTokens;
			}
		}

		// Class has no namespace and use statement has no namespace
		if ( $namespacePtr === false && $use_namespace === '' ) {
			$warning = 'Use statement with non-compound name';
			$fix = $phpcsFile->addFixableWarning( $warning, $stackPtr, 'NonCompoundUse' );
			if ( $fix ) {
				$this->removeUseStatement( $phpcsFile, $tokens, $stackPtr, $semiColon );
			}
			return;
		}

		for ( ; $classUsed < $phpcsFile->numTokens; $classUsed++ ) {
			if ( $tokens[$classUsed]['code'] === T_RETURN_TYPE ) {
				// If the name is used in a PHP 7 function return type declaration
				// stop.
				if ( strcasecmp( $tokens[$classUsed]['content'], $className ) === 0 ) {
					return;
				}
			} elseif ( $tokens[$classUsed]['code'] === T_STRING ) {
				if ( strcasecmp( $tokens[$classUsed]['content'], $className ) === 0 ) {
					$beforeUsage = $phpcsFile->findPrevious(
						Tokens::$emptyTokens,
						( $classUsed - 1 ),
						null,
						true
					);

					// If a backslash is used before the class name then this is some other
					// use statement.
					// T_STRING also used for $this->property or self::function()
					if ( $tokens[$beforeUsage]['code'] !== T_USE
						&& $tokens[$beforeUsage]['code'] !== T_NS_SEPARATOR
						&& $tokens[$beforeUsage]['code'] !== T_OBJECT_OPERATOR
						&& $tokens[$beforeUsage]['code'] !== T_DOUBLE_COLON
					) {
						return;
					}

					// Trait use statement within a class.
					if ( $tokens[$beforeUsage]['code'] === T_USE
						&& !empty( $tokens[$beforeUsage]['conditions'] )
					) {
						return;
					}
				}
			} elseif ( $tokens[$classUsed]['code'] === T_DOC_COMMENT_TAG ) {
				// Usage in a doc comment
				if ( in_array( $tokens[$classUsed]['content'], $this->classTags )
					&& $tokens[$classUsed + 2]['code'] === T_DOC_COMMENT_STRING
					// We aren't interested in the later, whitespace-separated parts of comments
					// like `@param (Class1|Class2)[]|Class3<Class4,Class5> $var Description`.
					&& preg_match(
						'{^\S*?\b(?<!\\\\)' . preg_quote( $className ) . '\b}i',
						$tokens[$classUsed + 2]['content']
					)
				) {
					return;
				}
			}
		}

		$warning = 'Unused use statement';
		$fix = $phpcsFile->addFixableWarning( $warning, $stackPtr, 'UnusedUse' );
		if ( $fix ) {
			$this->removeUseStatement( $phpcsFile, $tokens, $stackPtr, $semiColon );
		}
	}

	/**
	 * @param File $phpcsFile
	 * @param array[] $tokens
	 * @param int $stackPtr
	 * @param int $semiColon Token position of the ending semicolon
	 */
	private function removeUseStatement( File $phpcsFile, array $tokens, $stackPtr, $semiColon ) {
		// Remove the whole use statement line.
		$phpcsFile->fixer->beginChangeset();
		for ( $i = $stackPtr; $i <= $semiColon; $i++ ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
		}

		// Also remove whitespace after the semicolon (new lines).
		while ( isset( $tokens[$i] ) && $tokens[$i]['code'] === T_WHITESPACE ) {
			$phpcsFile->fixer->replaceToken( $i, '' );
			if ( $tokens[$i]['content'] === $phpcsFile->eolChar ) {
				break;
			}

			$i++;
		}

		$phpcsFile->fixer->endChangeset();
	}

}

<?php

namespace MediaWiki\Extension\Tei\Converter;

use File;
use MediaWiki\BadFileLookup;
use RepoGroup;
use Title;

/**
 * @license GPL-2.0-or-later
 *
 * A conversion from HTML to TEI
 */
class FileLookup {

	/**
	 * @var RepoGroup
	 */
	private $repoGroup;

	/**
	 * @var BadFileLookup
	 */
	private $badFileLookup;

	/**
	 * @param RepoGroup $repoGroup
	 * @param BadFileLookup $badFileLookup
	 */
	public function __construct( RepoGroup $repoGroup, BadFileLookup $badFileLookup ) {
		$this->repoGroup = $repoGroup;
		$this->badFileLookup = $badFileLookup;
	}

	/**
	 * @param string $fileName
	 * @param Title|null $pageTitle
	 * @return File|null
	 */
	public function getFileForPage( $fileName, ?Title $pageTitle = null ) {
		$file = $this->repoGroup->findFile( $fileName );
		if (
			$file === false ||
			$this->badFileLookup->isBadFile( $file->getTitle()->getDBkey(), $pageTitle )
		) {
				return null;
		}
		return $file;
	}
}

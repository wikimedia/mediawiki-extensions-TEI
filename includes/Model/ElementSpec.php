<?php

namespace MediaWiki\Extension\Tei\Model;

use MediaWiki\Extension\Tei\Model\ContentModel\ContentModel;

/**
 * @license GPL-2.0-or-later
 *
 * Definition of a TEI tag
 */
class ElementSpec {

	/**
	 * @var string
	 */
	private $ident;

	/**
	 * @var string[]
	 */
	private $classes;

	/**
	 * @var ContentModel
	 */
	private $contentModel;

	/**
	 * @var AttributeDef[]
	 */
	private $attributes;

	/**
	 * @param string $ident tag name
	 * @param string[] $classes the classes this tag is in
	 * @param ContentModel $contentModel the content model of the children of this tag
	 * @param AttributeDef[] $attributes
	 */
	public function __construct(
		$ident, array $classes, ContentModel $contentModel, array $attributes
	) {
		$this->ident = $ident;
		$this->classes = $classes;
		$this->contentModel = $contentModel;
		$this->attributes = $attributes;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->ident;
	}

	/**
	 * @return string[]
	 */
	public function getClasses() {
		return $this->classes;
	}

	/**
	 * @return ContentModel
	 */
	public function getContentModel() {
		return $this->contentModel;
	}

	/**
	 * @return AttributeDef[]
	 */
	public function getAttributes() {
		return $this->attributes;
	}
}

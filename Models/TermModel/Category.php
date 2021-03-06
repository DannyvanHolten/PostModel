<?php

namespace WordPressModels\TermModel;

/**
 * This is the model if you want to get the terms of the Category Taxonomy
 *
 * Class Category
 * @package WordPressModels\TermModel
 */
class Category extends TermModel
{
	/**
	 * Category constructor.
	 *
	 * Set all the arguments that are default for this Model
	 */
	public function __construct()
	{
		$this->type('category');
	}
}
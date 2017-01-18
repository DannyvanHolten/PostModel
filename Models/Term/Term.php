<?php

namespace App\Models\Term;

use WP_Term;
use WP_Post;

/**
 * The BaseTaxonomyModel is used to implement the core functions of get_terms
 *
 * The BaseTaxonomyModel is used for all functions that should be usable in all the other Taxonomy Models.
 * If you want to extend the BaseTaxonomyModel do so by at least defining a taxonomy in your extended class
 *
 * @see https://developer.wordpress.org/reference/functions/get_terms/
 *
 * Class BaseTaxonomyModel
 * @package App\Models
 */
abstract class Term
{
	/**
	 * An array of arguments to get the terms
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * The Terms
	 *
	 * @var array
	 */
	protected $terms = null;

	/**
	 * The Post
	 *
	 * @var array
	 */
	protected $post = null;

	/**
	 * Send trough all our functions to real functions, so we don't need to make a new instance for everything
	 * inspired by Laravel
	 *
	 * @param $name
	 * @param $arguments
	 * @return $this
	 */
	public function __call($name, $arguments)
	{
		call_user_func_array([$this, $name], $arguments);

		return $this;
	}

	/**
	 * Send trough all our static functions to real functions, so we don't need to make a new instance for everything
	 * inspired by Laravel
	 *
	 * @param $name
	 * @param $arguments
	 * @return static
	 */
	public static function __callStatic($name, $arguments)
	{
		$instance = new static;
		call_user_func_array([$instance, $name], $arguments);

		return $instance;

	}

	/**
	 * Return all terms.
	 * Note: Default limits to 0 (ALL). If you need less, adjust the limit.
	 *
	 * @see BaseTaxonomyModel::take()
	 * @see BaseTaxonomyModel::hideEmpty()
	 * @see BaseTaxonomyModel::get()
	 *
	 * @example ExampleTaxonomyModel::all();
	 * @example ExampleTaxonomyModel::all(10);
	 *
	 * @param null|int $take
	 * @return $instance
	 */
	public static function all($take = 0)
	{
		$instance = new static;

		return $instance->take($take)
			->hideEmpty(false)
			->get();
	}

	/**
	 * Find a single term by ID | Slug.
	 *
	 * @see BaseTaxonomyModel::whereIn();
	 * @see BaseTaxonomyModel::hideEmpty()
	 * @see BaseTaxonomyModel::get()
	 * @see BaseTaxonomyModel::first()
	 *
	 * @example PageModel::find();
	 * @example ExampleTaxonomyModel::find([1,2,3]);
	 *
	 * @param int|string|array $id
	 * @return $instance
	 */
	public static function find($id = null)
	{
		$instance = new static;

		$instance->id($id)
			->hideEmpty(false);

		if (is_array($id)) {
			return $instance->get();
		} else {
			return $instance->first();
		}
	}

	/**
	 * Get only certain fields instead of entire Post objects
	 *
	 * @see https://codex.wordpress.org/Class_Reference/WP_Query#Return_Fields_Parameter
	 *
	 * @example ExampleModel::fields('ids')->get();
	 *
	 * @param null|int $fields
	 * @return $this
	 */
	protected function fields($fields = null)
	{
		if ($fields !== null) {
			$this->args['fields'] = $fields;
		}

		return $this;
	}

	/**
	 * Define if we want to see all the empty terms
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_terms/#parameters
	 *
	 * @example ExampleTaxonomyModel::hideEmpty(false)->get();
	 *
	 * @param bool $hide
	 * @return $this
	 */
	protected function hideEmpty($hide = true)
	{
		$this->args['hide_empty'] = $hide;

		return $this;
	}

	/**
	 * Include certain terms
	 * Note: You can enter either an integer, string or an array.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_terms/#parameters
	 * @see https://developer.wordpress.org/reference/functions/get_queried_object/
	 *
	 * @example ExampleTaxonomyModel::id(10)->get();
	 * @example ExampleTaxonomyModel::id([10,20,30])->get();
	 *
	 * @param string|array $id
	 * @param bool $exclude
	 * @param bool $hideDescendants
	 * @return $this
	 * @throws \Exception
	 */
	protected function id($id = null, $exclude = false, $hideDescendants = false)
	{

		if ($id === null) {
			$queriedObject = get_queried_object();

			if ($queriedObject instanceof WP_Term) {
				$id = $queriedObject->term_id;
			} else {
				throw new \Exception('BaseTaxonomyModel::id is null and cannot verify the queried object is a WP_Term object');
			}
		}

		if (!is_array($id)) {
			$id = [$id];
		}

		if (is_string(current($id))) {
			$this->args['slug'] = $id;
		} elseif ($hideDescendants === true && $exclude === true) {
			$this->args['exclude_tree'] = $id;
		} elseif ($exclude === true) {
			$this->args['exclude'] = $id;
		} else {
			$this->args['include'] = $id;
		}

		return $this;
	}

	/**
	 * Order the results within the Query
	 * The first value is the order by value, the second is either ascending or descending
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_terms/#parameters
	 *
	 * @example ExampleTaxonomyModel::orderBy('date','ASC')->get();
	 * @example ExampleTaxonomyModel::take(3)->orderBy('online','ASC', 'status')->get();
	 *
	 * @param string $orderBy
	 * @param null|string $order (ASC or DESC)
	 * @param null|string $meta_key
	 * @return $this
	 */
	protected function orderBy($orderBy, $order = null, $meta_key = null)
	{

		$this->args['orderby'] = $orderBy;

		if ($order !== null) {
			$this->args['order'] = $order;
		}

		if ($meta_key !== null) {
			$this->args['meta_key'] = $meta_key;
		}

		return $this;
	}

	/**
	 * Skip the number of posts defined by skip
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_terms/#parameters
	 *
	 * @example ExampleTaxonomyModel::skip(3)->get();
	 *
	 * @param int $skip
	 * @return $this
	 */
	protected function skip($skip = 0)
	{
		if ($skip !== 0) {
			$this->args['offset'] = $skip;
		}

		return $this;
	}

	/**
	 * Return all the terms of a certain taxonomy type (or multiple)
	 * Note: DO NOT use this function as is. Create a new Model and extend the TaxonomyModel. For example ExampleTaxonomyModel.
	 * And define it in the constructor.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_terms/#parameters
	 *
	 * @example ExampleTaxonomyModel::all();
	 *
	 * @param string|array $taxonomy
	 * @return $this
	 */
	protected function type($taxonomy = null)
	{
		if ($taxonomy !== null) {
			$this->args['taxonomy'] = $taxonomy;
		}

		return $this;
	}

	/**
	 * Limit the terms
	 * Use 0 for everything.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_terms/#parameters
	 *
	 * @example ExampleTaxonomyModel::take(3)->get();
	 *
	 * @param $take
	 * @return $this
	 */
	protected function take($take = null)
	{
		if ($take !== null) {

			// Check if the take value is -1, and make it 0 to maintain unity with the Postmodel
			if ($take === -1) {
				$take = 0;
			}

			$this->args['number'] = $take;
		}

		return $this;
	}

	/**
	 * Get terms by a certain meta query.
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_terms/#parameters
	 *
	 * @example ExampleTaxonomyModel::where('active', 1)->where('spotlight', 'front')->get();
	 * @example ExampleTaxonomyModel::where('spotlight', 'footer', 'IN', 'OR')->where('spotlight', 'front')->get();
	 *
	 * @param $meta_key
	 * @param $meta_value
	 * @param string $meta_compare
	 * @return $this
	 */
	protected function where($meta_key, $meta_value, $meta_compare = '=', $meta_relation = 'AND')
	{
		// Check if there already is a query. If not, create a relation field first
		if (!isset($this->args['meta_query'])) {
			$this->args['meta_query'] = [
				'relation' => $meta_relation
			];
		}

		// Check if this key has already been set. If so, overwrite it.
		foreach ($this->args['meta_query'] as $key => $meta) {
			if (is_array($meta) && in_array($meta_key, $meta)) {
				unset($this->args['meta_query'][$key]);
			}
		}

		// Create a new query argument
		$this->args['meta_query'][] = [
			'key'     => $meta_key,
			'value'   => $meta_value,
			'compare' => $meta_compare
		];

		return $this;
	}

	/**
	 * Get terms by post
	 * Note: You can enter either an integer, string or an array.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_get_post_terms/
	 * @see https://developer.wordpress.org/reference/functions/get_queried_object/
	 *
	 * @example ExampleTaxonomyModel::wherePost()->get();
	 * @example ExampleTaxonomyModel::wherePost(10)->get();
	 *
	 * @param null|int $postID
	 * @return $this
	 * @throws \Exception
	 */
	protected function wherePost($postID = null)
	{
		if ($postID === null) {
			$queriedObject = get_queried_object();

			if ($queriedObject instanceof WP_Post) {
				$postID = $queriedObject->ID;
			} else {
				throw new \Exception('BaseTaxonomyModel::wherePost is null and cannot verify the queried object is a WP_Post object');
			}
		}

		$this->post = $postID;

		return $this;
	}

	/**
	 * Parse our query and execute all the functions to make our content super fancy
	 *
	 * @see https://developer.wordpress.org/reference/functions/wp_get_post_terms/
	 * @see https://developer.wordpress.org/reference/functions/get_terms/
	 *
	 * @see BasePostModel::runquery();
	 * @see BasePostModel::appendAcfFields();
	 * @see BasePostModel::appendContent();
	 * @see BasePostModel::appendExcerpt();
	 * @see BasePostModel::appendPermalink();
	 *
	 * @example ExampleModel::take(10)->get();
	 *
	 * @return array|int|\WP_Error
	 */
	public function get()
	{
		if ($this->post !== null) {
			$this->terms = wp_get_post_terms($this->post, $this->args['taxonomy'], $this->args);
		} else {
			$this->terms = get_terms($this->args);
		}

		$this->appendAcfFields()
			->appendDescription()
			->appendPermalink();

		return $this->terms;
	}

	/**
	 * Get the first result of our Query
	 *
	 * @see BaseTaxonomyModel::take();
	 * @see BaseTaxonomyModel::get();
	 *
	 * @example ExampleTaxonomyModel::whereIn(1)->first();
	 *
	 * @return mixed
	 */
	public function first()
	{
		$this->take(1)
			->get();

		if (isset($this->terms[0])) {
			return $this->terms[0];
		}

		return false;
	}

	/**
	 * Count the results of our Query
	 *
	 * @see BaseTaxonomyModel::get();
	 *
	 * @example ExampleTaxonomyModel::where('active', 1)->count();
	 *
	 * @return mixed
	 */
	public function count()
	{
		if (!isset($this->terms)) {
			$this->get();
		}

		return count($this->terms);
	}

	/**
	 * Get all the ACF fields that are related to our posts
	 *
	 * @see https://www.advancedcustomfields.com/resources/get_fields/
	 *
	 * @return mixed
	 */
	private function appendAcfFields()
	{
		if (function_exists('get_fields')) {
			foreach ($this->terms as $term) {
				if ($term instanceof WP_Term) {
					$term->fields = get_fields($term->taxonomy . '_' . $term->term_id);
				}
			}
		}

		return $this;
	}

	/**
	 * Add P tags around our description.
	 *
	 * @see https://developer.wordpress.org/reference/functions/wpautop/
	 *
	 * @return mixed
	 */
	private function appendDescription()
	{
		foreach ($this->terms as $term) {
			if ($term instanceof WP_Term) {
				$term->description = wpautop($term->description);
			}
		}

		return $this;
	}

	/**
	 * Add the permalink to our query result
	 *
	 * @see https://developer.wordpress.org/reference/functions/get_term_link/
	 *
	 * @return mixed
	 */
	private function appendPermalink()
	{
		foreach ($this->terms as $term) {
			if ($term instanceof WP_Term) {
				$term->permalink = get_term_link($term->term_id);
			}
		}

		return $this;
	}

}
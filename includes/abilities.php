<?php
/**
 * WordPress Abilities API integration.
 *
 * Registers three read-only abilities so MCP clients, REST automation and the
 * WordPress AI client can read this site's references as structured data instead
 * of scraping the rendered page.
 *
 * Everything here reads through includes/references-data.php. The visibility rule
 * is not restated in these callbacks, because a second statement of a rule becomes
 * a second rule the moment either side is touched.
 *
 * That applies WITHIN this file only. references-data.php is not yet the single
 * home of the rule: its builder is called from here and nowhere else, while
 * includes/shortcodes.php still assembles the same clauses inline. The two were
 * verified to agree, but they are two implementations — a change to the rule has
 * to be made in both until the shortcode is moved over.
 *
 * @package   JPKCom_ACF_References
 * @author    Jean Pierre Kolb <jpk@jpkc.com>
 * @license   GPL-2.0-or-later
 * @link      https://github.com/JPKCom/jpkcom-acf-references
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {

	exit;

}


if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_CATEGORY' ) ) {

	/**
	 * Ability category shared with the sibling JPKCom content plugins.
	 *
	 * Categories are global and registration is FIRST-WINS: the second plugin to
	 * register the same slug gets null back and _doing_it_wrong() fires. Which
	 * plugin wins depends on load order, so registration goes through
	 * wp_has_ability_category() rather than assuming.
	 *
	 * @since 1.2.0
	 */
	define( 'JPKCOM_ACFREFERENCES_ABILITY_CATEGORY', 'jpkcom-content' );

}

if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_DEFAULT' ) ) {

	/**
	 * Default page size for the query ability.
	 *
	 * NOT the shortcode's default. That one is -1, which means "all" there and
	 * would mean an unbounded response here.
	 *
	 * @since 1.2.0
	 */
	define( 'JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_DEFAULT', 10 );

}

if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_MAX' ) ) {

	/**
	 * Largest page size the query ability will honour.
	 *
	 * @since 1.2.0
	 */
	define( 'JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_MAX', 50 );

}

if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES' ) ) {

	/**
	 * Largest number of values accepted in one filter axis.
	 *
	 * @since 1.2.0
	 */
	define( 'JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES', 20 );

}

if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT' ) ) {

	/**
	 * Largest number of entries list-filters reports per axis.
	 *
	 * @since 1.2.0
	 */
	define( 'JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT', 500 );

}

if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_SEARCH_MAX_BYTES' ) ) {

	/**
	 * Longest search term, in BYTES, that WordPress will actually apply.
	 *
	 * WP_Query::parse_query() empties `s` when strlen() exceeds this — an anti-DoS
	 * guard that runs INSIDE the query, after every check on the arguments has
	 * passed. The result is not an error: the search simply stops narrowing and
	 * every reference matches, while the response still echoes the term back as
	 * applied.
	 *
	 * strlen(), so the unit is BYTES. Counting characters would hand a 900-
	 * character accented term to a guard that counts bytes and reproduce the
	 * defect for non-ASCII callers only.
	 *
	 * @since 1.2.0
	 */
	define( 'JPKCOM_ACFREFERENCES_ABILITY_SEARCH_MAX_BYTES', 1600 );

}

if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_PAGE_MAX' ) ) {

	/**
	 * Highest page number the query ability will ask the database for.
	 *
	 * Derived, not picked. WP_Query computes its LIMIT offset as
	 * absint( ( $page - 1 ) * $posts_per_page ), a plain integer multiplication:
	 * past PHP_INT_MAX it becomes a float and absint() casts rather than throws,
	 * collapsing the offset to 0 — so page one's records come back labelled as a
	 * page far beyond total_pages.
	 *
	 * max() guards the divisor: a site may redefine PER_PAGE_MAX, and intdiv() by
	 * zero is a fatal at file load.
	 *
	 * @since 1.2.0
	 */
	define(
		'JPKCOM_ACFREFERENCES_ABILITY_PAGE_MAX',
		intdiv( num1: PHP_INT_MAX, num2: max( 1, JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_MAX ) )
	);

}

if ( ! defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITY_INPUT_KEYS' ) ) {

	/**
	 * Top-level input keys each ability declares.
	 *
	 * One list, one place, and checked against the registered input schemas by
	 * tests/test-abilities.php. Neither schema that carries `properties` declares
	 * additionalProperties, on purpose: core's validate_input() runs BEFORE the
	 * execute callback, so declaring it would preempt the guard below and replace
	 * a message naming the accepted keys with core's "not a valid property of the
	 * object". Self-correction in one turn is the point of these messages.
	 *
	 * list-filters is the exception and must stay one: it declares no properties
	 * at all, so additionalProperties => false is the only thing that can refuse a
	 * key there.
	 *
	 * @since 1.2.0
	 */
	define(
		'JPKCOM_ACFREFERENCES_ABILITY_INPUT_KEYS',
		[
			'jpkcom-acf-references/list-filters'      => [],
			'jpkcom-acf-references/query-references'  => [ 'type', 'filter_1', 'filter_2', 'customer', 'location', 'search', 'page', 'per_page', 'order' ],
			'jpkcom-acf-references/get-reference'     => [ 'id' ],
		]
	);

}


if ( ! function_exists( function: 'jpkcom_acf_references_abilities_enabled' ) ) {

	/**
	 * Decide whether the abilities should be registered at all.
	 *
	 * `Requires Plugins` only blocks activation; core does not stop a dependency
	 * being deactivated while dependents are active. get_field() is therefore
	 * checked rather than assumed.
	 *
	 * @since 1.2.0
	 *
	 * @return bool True when registration should proceed.
	 */
	function jpkcom_acf_references_abilities_enabled(): bool {

		if ( defined( constant_name: 'JPKCOM_ACFREFERENCES_ABILITIES' ) && ! JPKCOM_ACFREFERENCES_ABILITIES ) {

			return false;

		}

		return function_exists( function: 'wp_register_ability' )
			&& function_exists( function: 'wp_register_ability_category' )
			&& function_exists( function: 'get_field' );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_log' ) ) {

	/**
	 * Write a debug line, and only with WP_DEBUG.
	 *
	 * @since 1.2.0
	 *
	 * @param string $message Message.
	 * @return void
	 */
	function jpkcom_acf_references_ability_log( string $message ): void {

		if ( defined( constant_name: 'WP_DEBUG' ) && WP_DEBUG ) {

			error_log( message: '[jpkcom-acf-references] ' . $message );

		}

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_error' ) ) {

	/**
	 * Build a WP_Error carrying an HTTP status.
	 *
	 * The status is not decoration. The REST run controller returns the WP_Error
	 * verbatim and rest_ensure_response() defaults to 500 without data['status'] —
	 * and a 5xx tells an agent "transient fault, retry unchanged", which is the
	 * exact opposite of what a caller mistake needs to hear.
	 *
	 * @since 1.2.0
	 *
	 * @param string $code    Error code.
	 * @param string $message Human-readable message.
	 * @param int    $status  HTTP status.
	 * @return WP_Error Error.
	 */
	function jpkcom_acf_references_ability_error( string $code, string $message, int $status = 400 ): WP_Error {

		return new WP_Error( $code, $message, [ 'status' => $status ] );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_boundary' ) ) {

	/**
	 * Turn a Throwable out of a callback into a WP_Error.
	 *
	 * ACF throws while READING corrupt meta, so no check on the shape beforehand
	 * catches it. This rim does not catch everything: schema validation and the
	 * permission check both run before it.
	 *
	 * @since 1.2.0
	 *
	 * @param callable $body    Callback returning the ability result.
	 * @param string   $ability Ability name, for the log line.
	 * @return array<string, mixed>|WP_Error Result or error.
	 */
	function jpkcom_acf_references_ability_boundary( callable $body, string $ability ): array|WP_Error {

		try {

			return $body();

		} catch ( \Throwable $e ) {

			jpkcom_acf_references_ability_log(
				$ability . ' failed while reading stored data: ' . $e->getMessage()
			);

			// A site-side data condition, not a caller mistake: no change to the
			// request fixes it, so a 4xx would send an agent into a correction loop
			// that cannot terminate.
			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_read_failed',
				__( 'This site holds a stored value for one of the requested references that cannot be read. This is a data condition on the site, not a problem with the request, so repeating the call unchanged will not help; the details are in the site error log. Other references are unaffected.', 'jpkcom-acf-references' ),
				500
			);

		}

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_json_object' ) ) {

	/**
	 * Wrap an empty map so it encodes as {} rather than [].
	 *
	 * PHP serialises an empty array as the JSON array [], and a client validating
	 * against a schema declaring `type: object` rejects that. Only the empty case
	 * is wrapped, so PHP callers keep array access where there is data.
	 *
	 * @since 1.2.0
	 *
	 * @param array<string, mixed> $map Map.
	 * @return array<string, mixed>|stdClass Map, or an empty object.
	 */
	function jpkcom_acf_references_ability_json_object( array $map ): array|stdClass {

		return $map === [] ? (object) [] : $map;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_capability' ) ) {

	/**
	 * Capability required to run an ability.
	 *
	 * Defaults to `read`. Every query is hard-scoped to published references, so
	 * this cannot expose drafts — but it is bulk machine-readable access, and a
	 * site may want it narrower.
	 *
	 * @since 1.2.0
	 *
	 * @param string $ability Ability name.
	 * @return bool True when the current user may run it.
	 */
	function jpkcom_acf_references_ability_capability( string $ability ): bool {

		/**
		 * Filter the capability required to run a JPKCom ACF References ability.
		 *
		 * @since 1.2.0
		 *
		 * @param string $capability Capability name.
		 * @param string $ability    Ability name.
		 */
		$capability = apply_filters( 'jpkcom_acf_references_ability_capability', 'read', $ability );

		return current_user_can( is_string( value: $capability ) ? $capability : 'read' );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_meta' ) ) {

	/**
	 * Build the meta array for an ability.
	 *
	 * Three independent switches: show_in_rest (core REST), public (WP 7.1; inert
	 * passthrough before that), and mcp.public — not a core key at all, but the
	 * MCP Adapter's own gate for discovery AND execution.
	 *
	 * All three annotations are set explicitly. They default to null, and the REST
	 * run controller derives the HTTP verb from them: readonly makes the run route
	 * GET-only and POST answers 405.
	 *
	 * @since 1.2.0
	 *
	 * @param string $ability Ability name.
	 * @return array<string, mixed> Meta array.
	 */
	function jpkcom_acf_references_ability_meta( string $ability ): array {

		$meta = [
			'show_in_rest' => true,
			'public'       => true,
			'mcp'          => [ 'public' => true ],
			'annotations'  => [
				'readonly'    => true,
				'destructive' => false,
				'idempotent'  => true,
			],
		];

		/**
		 * Filter the meta array of a JPKCom ACF References ability.
		 *
		 * @since 1.2.0
		 *
		 * @param array<string, mixed> $meta    Meta array.
		 * @param string               $ability Ability name.
		 */
		$filtered = apply_filters( 'jpkcom_acf_references_ability_meta', $meta, $ability );

		return is_array( value: $filtered ) ? $filtered : $meta;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_validate_input_keys' ) ) {

	/**
	 * Refuse a top-level input key the ability does not declare.
	 *
	 * On EVERY ability, not a subset. A guard on some of them is a trap: a caller
	 * that learned the refusal on one assumes it everywhere, and the ability that
	 * silently accepts is the one it will trust. The sibling plugin shipped
	 * exactly that gap for a whole release.
	 *
	 * Without this, an axis the ability does not declare is not applied and the
	 * answer is the complete unfiltered set behind an HTTP 200 — a caller has to
	 * notice an absence to notice the failure.
	 *
	 * @since 1.2.0
	 *
	 * @param array<string, mixed> $input   Raw ability input.
	 * @param string[]             $allowed Declared keys.
	 * @return true|WP_Error True when every key is declared.
	 */
	function jpkcom_acf_references_ability_validate_input_keys( array $input, array $allowed ): true|WP_Error {

		$unknown = [];

		foreach ( array_keys( $input ) as $key ) {

			if ( ! in_array( needle: (string) $key, haystack: $allowed, strict: true ) ) {

				$unknown[] = (string) $key;

			}

		}

		if ( $unknown === [] ) {

			return true;

		}

		return jpkcom_acf_references_ability_error(
			'jpkcom_acf_references_unknown_input_key',
			sprintf(
				/* translators: 1: comma-separated rejected keys, 2: comma-separated accepted keys. */
				__( 'Unknown input key: %1$s. This ability accepts: %2$s. A key it does not declare is never read, so the request would be answered as though that key had not been sent — on the filtering ability that means an unfiltered result set that looks like a filtered one.', 'jpkcom-acf-references' ),
				implode( ', ', $unknown ),
				implode( ', ', $allowed ) !== '' ? implode( ', ', $allowed ) : __( 'no input at all', 'jpkcom-acf-references' )
			),
			400
		);

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_normalise_input' ) ) {

	/**
	 * Bring the value core hands the callback into array form.
	 *
	 * normalize_input() substitutes the schema's TOP-LEVEL default when the input
	 * is exactly null, and that default is a stdClass — so the callback receives
	 * an object and must read it. A callback that only accepts an array answers
	 * 400 to the most obvious call it has. This exact defect shipped twice in the
	 * sibling plugins.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Raw input.
	 * @return array<string, mixed>|null Array form, or null when unusable.
	 */
	function jpkcom_acf_references_ability_normalise_input( mixed $input ): ?array {

		if ( $input === null ) {

			return [];

		}

		if ( is_object( value: $input ) ) {

			return get_object_vars( object: $input );

		}

		return is_array( value: $input ) ? $input : null;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_slug_list' ) ) {
	/**
	 * Normalise a filter axis into a bounded list of term slugs.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Raw value.
	 * @return string[] Slugs, deduplicated and capped.
	 */
	function jpkcom_acf_references_ability_slug_list( mixed $value ): array {

		if ( is_scalar( value: $value ) ) {

			$value = [ $value ];

		}

		if ( ! is_array( value: $value ) ) {

			return [];

		}

		$out = [];

		foreach ( $value as $item ) {

			if ( ! is_scalar( value: $item ) ) {

				continue;

			}

			$slug = sanitize_title( title: (string) $item );

			if ( $slug !== '' ) {

				$out[ $slug ] = true;

			}

		}

		return array_slice( array_keys( $out ), 0, JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_id_list' ) ) {

	/**
	 * Normalise a filter axis into a bounded list of positive post IDs.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Raw value.
	 * @return int[] IDs, deduplicated and capped.
	 */
	function jpkcom_acf_references_ability_id_list( mixed $value ): array {

		if ( is_scalar( value: $value ) ) {

			$value = [ $value ];

		}

		if ( ! is_array( value: $value ) ) {

			return [];

		}

		$out = [];

		foreach ( $value as $item ) {

			if ( ! is_scalar( value: $item ) ) {

				continue;

			}

			$id = absint( $item );

			if ( $id > 0 ) {

				$out[ $id ] = true;

			}

		}

		return array_slice( array_keys( $out ), 0, JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_validate_search' ) ) {

	/**
	 * Refuse a search term core would silently discard.
	 *
	 * @since 1.2.0
	 *
	 * @param string $search Search term.
	 * @return true|WP_Error True when the term is usable.
	 */
	function jpkcom_acf_references_ability_validate_search( string $search ): true|WP_Error {

		$bytes = strlen( string: $search );

		if ( $bytes <= JPKCOM_ACFREFERENCES_ABILITY_SEARCH_MAX_BYTES ) {

			return true;

		}

		return jpkcom_acf_references_ability_error(
			'jpkcom_acf_references_search_too_long',
			sprintf(
				/* translators: 1: length of the submitted term in bytes, 2: the limit in bytes. */
				__( 'The search term is %1$d bytes long; WordPress applies no search term over %2$d bytes and discards it inside the query without reporting anything. Shorten it and call again. The limit counts bytes, so accented characters and emoji cost more than one each.', 'jpkcom-acf-references' ),
				$bytes,
				JPKCOM_ACFREFERENCES_ABILITY_SEARCH_MAX_BYTES
			),
			400
		);

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_vocabulary' ) ) {

	/**
	 * Read one taxonomy's terms as the filter vocabulary.
	 *
	 * hide_empty is false: a term with no reference today is still a valid filter
	 * value, and reporting only used terms would make the vocabulary shift under a
	 * caller that cached it.
	 *
	 * @since 1.2.0
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return array{terms: array<int, array<string, mixed>>, truncated: bool} Vocabulary.
	 */
	function jpkcom_acf_references_ability_vocabulary( string $taxonomy ): array {

		if ( ! taxonomy_exists( taxonomy: $taxonomy ) ) {

			return [
				'terms'     => [],
				'truncated' => false,
			];

		}

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'number'     => JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT + 1,
				'orderby'    => 'name',
				'order'      => 'ASC',
			]
		);

		if ( ! is_array( value: $terms ) ) {

			return [
				'terms'     => [],
				'truncated' => false,
			];

		}

		$truncated = count( $terms ) > JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT;
		$terms     = array_slice( $terms, 0, JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT );

		$out = [];

		foreach ( $terms as $term ) {

			if ( ! $term instanceof WP_Term ) {

				continue;

			}

			$out[] = [
				'slug'  => (string) $term->slug,
				'name'  => (string) $term->name,
				'count' => (int) $term->count,
			];

		}

		return [
			'terms'     => $out,
			'truncated' => $truncated,
		];

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_related_vocabulary' ) ) {

	/**
	 * Read the customers or locations a caller may filter by.
	 *
	 * Published only, and projected to id/title. Never a WP_Post: ACF resolves
	 * these relations with post_status => 'any' elsewhere, and WP_Post exposes
	 * post_password as a public property.
	 *
	 * @since 1.2.0
	 *
	 * @param string $post_type Post type.
	 * @return array{items: array<int, array<string, mixed>>, truncated: bool} Vocabulary.
	 */
	function jpkcom_acf_references_ability_related_vocabulary( string $post_type ): array {

		if ( ! post_type_exists( post_type: $post_type ) ) {

			return [
				'items'     => [],
				'truncated' => false,
			];

		}

		$posts = get_posts(
			[
				'post_type'        => $post_type,
				'post_status'      => 'publish',
				'posts_per_page'   => JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT + 1,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'suppress_filters' => false,
				'no_found_rows'    => true,
			]
		);

		if ( ! is_array( value: $posts ) ) {

			return [
				'items'     => [],
				'truncated' => false,
			];

		}

		$truncated = count( $posts ) > JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT;
		$posts     = array_slice( $posts, 0, JPKCOM_ACFREFERENCES_ABILITY_VOCABULARY_LIMIT );

		$out = [];

		foreach ( $posts as $post ) {

			if ( ! $post instanceof WP_Post || $post->post_password !== '' ) {

				continue;

			}

			$out[] = [
				'id'    => (int) $post->ID,
				'title' => (string) get_the_title( $post ),
			];

		}

		return [
			'items'     => $out,
			'truncated' => $truncated,
		];

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_count_query' ) ) {

	/**
	 * Count posts matching a set of query arguments, cheaply.
	 *
	 * @since 1.2.0
	 *
	 * @param array<string, mixed> $args Query arguments.
	 * @return int Count.
	 */
	function jpkcom_acf_references_ability_count_query( array $args ): int {

		$args['posts_per_page'] = 1;
		$args['fields']         = 'ids';
		$args['no_found_rows']  = false;

		$query = new WP_Query( $args );

		return (int) $query->found_posts;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_visibility_counts' ) ) {

	/**
	 * Report how many published references the listing rule excludes, and why.
	 *
	 * Both numbers are DIFFERENCES, derived from the rule itself. Restating what
	 * "expired" means as its own meta_query is the defect this shape exists to
	 * prevent: the rule's expiry clause is an OR group of three branches, and
	 * negating only the first is not its complement — MariaDB casts '' to
	 * '0000-00-00', so every reference whose date had been saved and cleared would
	 * be counted as expired in the same response that lists it.
	 *
	 *   hidden_expired          = ( published + featured row ) - listed
	 *   hidden_missing_featured = published - ( published + featured row )
	 *
	 * @since 1.2.0
	 *
	 * @return array<string, int> Counts.
	 */
	function jpkcom_acf_references_ability_visibility_counts(): array {

		$counts    = wp_count_posts( 'reference' );
		$published = isset( $counts->publish ) ? (int) $counts->publish : 0;

		$listed = jpkcom_acf_references_ability_count_query(
			jpkcom_acf_references_build_reference_query_args()
		);

		// The featured row alone, without the expiry group. Two independent causes
		// exclude a reference with no row - the EXISTS clause AND the meta_key used
		// for ordering, whose own condition lands in the WHERE clause - so this
		// number cannot be read off the listing query and needs one of its own.
		$with_featured = jpkcom_acf_references_ability_count_query(
			[
				'post_type'   => 'reference',
				'post_status' => 'publish',
				'meta_query'  => [
					[
						'key'     => 'reference_featured',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		return [
			'published'               => $published,
			'listed'                  => $listed,
			'hidden_expired'          => max( 0, $with_featured - $listed ),
			'hidden_missing_featured' => max( 0, $published - $with_featured ),
		];

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_archive_url' ) ) {

	/**
	 * The reference archive URL, or an empty string when there is none.
	 *
	 * @since 1.2.0
	 *
	 * @return string Archive URL.
	 */
	function jpkcom_acf_references_ability_archive_url(): string {

		$url = get_post_type_archive_link( post_type: 'reference' );

		return is_string( value: $url ) ? $url : '';

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_permission_list_filters' ) ) {

	/**
	 * Permission callback for list-filters.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Validated input, unused.
	 * @return bool True when the current user may run it.
	 */
	function jpkcom_acf_references_ability_permission_list_filters( mixed $input = null ): bool {

		return jpkcom_acf_references_ability_capability( 'jpkcom-acf-references/list-filters' );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_permission_query_references' ) ) {

	/**
	 * Permission callback for query-references.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Validated input, unused.
	 * @return bool True when the current user may run it.
	 */
	function jpkcom_acf_references_ability_permission_query_references( mixed $input = null ): bool {

		return jpkcom_acf_references_ability_capability( 'jpkcom-acf-references/query-references' );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_permission_get_reference' ) ) {

	/**
	 * Permission callback for get-reference.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Validated input, unused.
	 * @return bool True when the current user may run it.
	 */
	function jpkcom_acf_references_ability_permission_get_reference( mixed $input = null ): bool {

		return jpkcom_acf_references_ability_capability( 'jpkcom-acf-references/get-reference' );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_list_filters_inner' ) ) {

	/**
	 * Report the values a caller may filter references by.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array<string, mixed>|WP_Error Result.
	 */
	function jpkcom_acf_references_ability_list_filters_inner( mixed $input = null ): array|WP_Error {

		$normalised = jpkcom_acf_references_ability_normalise_input( $input );

		if ( $normalised === null ) {

			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_invalid_input',
				__( 'This ability takes no parameters. Call it with no input at all.', 'jpkcom-acf-references' )
			);

		}

		$keys_valid = jpkcom_acf_references_ability_validate_input_keys(
			$normalised,
			JPKCOM_ACFREFERENCES_ABILITY_INPUT_KEYS['jpkcom-acf-references/list-filters']
		);

		if ( $keys_valid instanceof WP_Error ) {

			return $keys_valid;

		}

		$types    = jpkcom_acf_references_ability_vocabulary( 'reference-type' );
		$filter_1 = jpkcom_acf_references_ability_vocabulary( 'reference-filter-1' );
		$filter_2 = jpkcom_acf_references_ability_vocabulary( 'reference-filter-2' );

		$customers = jpkcom_acf_references_ability_related_vocabulary( 'reference_customer' );
		$locations = jpkcom_acf_references_ability_related_vocabulary( 'reference_location' );

		return [
			'types'                 => $types['terms'],
			'filter_1'              => $filter_1['terms'],
			'filter_2'              => $filter_2['terms'],
			'customers'             => $customers['items'],
			'locations'             => $locations['items'],
			'vocabulary_truncated'  => $types['truncated'] || $filter_1['truncated'] || $filter_2['truncated']
				|| $customers['truncated'] || $locations['truncated'],
			'archive_url'           => jpkcom_acf_references_ability_archive_url(),
			'visibility'            => jpkcom_acf_references_ability_visibility_counts(),
			'language'              => determine_locale(),
		];

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_query_references_inner' ) ) {

	/**
	 * Run a filtered, paginated query over publicly listed references.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array<string, mixed>|WP_Error Result.
	 */
	function jpkcom_acf_references_ability_query_references_inner( mixed $input = null ): array|WP_Error {

		$normalised = jpkcom_acf_references_ability_normalise_input( $input );

		if ( $normalised === null ) {

			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_invalid_input',
				__( 'The input has to be an object, for example {"type": ["web-design"], "per_page": 5}. Every parameter is optional; calling this ability with no input at all returns the first page of all listed references.', 'jpkcom-acf-references' )
			);

		}

		$keys_valid = jpkcom_acf_references_ability_validate_input_keys(
			$normalised,
			JPKCOM_ACFREFERENCES_ABILITY_INPUT_KEYS['jpkcom-acf-references/query-references']
		);

		if ( $keys_valid instanceof WP_Error ) {

			return $keys_valid;

		}

		$requested = [
			'type'     => jpkcom_acf_references_ability_slug_list( $normalised['type'] ?? null ),
			'filter_1' => jpkcom_acf_references_ability_slug_list( $normalised['filter_1'] ?? null ),
			'filter_2' => jpkcom_acf_references_ability_slug_list( $normalised['filter_2'] ?? null ),
			'customer' => jpkcom_acf_references_ability_id_list( $normalised['customer'] ?? null ),
			'location' => jpkcom_acf_references_ability_id_list( $normalised['location'] ?? null ),
		];

		$search = '';

		if ( isset( $normalised['search'] ) && is_scalar( value: $normalised['search'] ) ) {

			$search = trim( string: (string) $normalised['search'] );

		}

		$search_valid = jpkcom_acf_references_ability_validate_search( $search );

		if ( $search_valid instanceof WP_Error ) {

			return $search_valid;

		}

		$per_page = JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_DEFAULT;

		if ( isset( $normalised['per_page'] ) && is_numeric( value: $normalised['per_page'] ) ) {

			$per_page = max( 1, min( JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_MAX, (int) $normalised['per_page'] ) );

		}

		$page = 1;

		if ( isset( $normalised['page'] ) && is_numeric( value: $normalised['page'] ) ) {

			$page = max( 1, min( JPKCOM_ACFREFERENCES_ABILITY_PAGE_MAX, (int) $normalised['page'] ) );

		}

		$order = 'DESC';

		if ( isset( $normalised['order'] ) && is_string( value: $normalised['order'] )
			&& strtoupper( string: $normalised['order'] ) === 'ASC' ) {

			$order = 'ASC';

		}

		// Unknown values are reported rather than silently dropped: an empty result
		// with no explanation reads as "nothing matches" when the real answer is
		// "that term does not exist on this site".
		$unknown = [];

		// Set when an axis was requested but not one of its values resolves.
		$impossible = false;

		$taxonomies = [
			'type'     => 'reference-type',
			'filter_1' => 'reference-filter-1',
			'filter_2' => 'reference-filter-2',
		];

		$tax_query = [ 'relation' => 'AND' ];

		foreach ( $taxonomies as $axis => $taxonomy ) {

			if ( $requested[ $axis ] === [] ) {

				continue;

			}

			$known = [];

			foreach ( $requested[ $axis ] as $slug ) {

				$term = get_term_by( field: 'slug', value: $slug, taxonomy: $taxonomy );

				if ( $term instanceof WP_Term ) {

					$known[] = $slug;

				} else {

					$unknown[ $axis ][] = $slug;

				}

			}

			if ( $known === [] ) {

				// Requested, and not one value resolves. Skipping the clause here
				// would answer with the COMPLETE unfiltered set behind an HTTP 200
				// while `unknown` quietly names the reason - and a caller reading
				// `total` gets the whole corpus as a filtered answer. Asking for
				// something that exists nowhere must return nothing.
				$impossible = true;

				continue;

			}

			// include_children false, deliberately: these taxonomies are hierarchical
			// and tax_query defaults to true, which would widen a filter to posts
			// filed under child terms. The list shortcode makes the same choice, and
			// tests/test-tax-query.php pins it there.
			$tax_query[] = [
				'taxonomy'         => $taxonomy,
				'field'            => 'slug',
				'terms'            => $known,
				'operator'         => 'IN',
				'include_children' => false,
			];

		}

		// Customer and location stay meta-based: they are ACF post-object fields,
		// not taxonomies, so there is no term relationship to query. ACF stores them
		// serialised, hence LIKE with the quoted id.
		$meta_filters = [];

		foreach ( [ 'customer' => 'reference_customer', 'location' => 'reference_location' ] as $axis => $meta_key ) {

			if ( $requested[ $axis ] === [] ) {

				continue;

			}

			$clause = [ 'relation' => 'OR' ];

			foreach ( $requested[ $axis ] as $id ) {

				$post = get_post( $id );

				if ( ! $post instanceof WP_Post || $post->post_status !== 'publish' ) {

					$unknown[ $axis ][] = $id;

					continue;

				}

				$clause[] = [
					'key'     => $meta_key,
					'value'   => '"' . $id . '"',
					'compare' => 'LIKE',
				];

			}

			if ( count( $clause ) > 1 ) {

				$meta_filters[] = $clause;

			} else {

				// Same rule as the taxonomy axes above: every requested id was
				// unusable, so the honest answer is an empty set.
				$impossible = true;

			}

		}

		// One statement of what the response echoes back, used by both return paths.
		$echo_filters = array_filter(
			[
				'type'     => $requested['type'],
				'filter_1' => $requested['filter_1'],
				'filter_2' => $requested['filter_2'],
				'customer' => $requested['customer'],
				'location' => $requested['location'],
				'search'   => $search,
				'order'    => $order,
			],
			static fn( $value ): bool => $value !== [] && $value !== ''
		);

		$args = jpkcom_acf_references_build_reference_query_args(
			[
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'orderby'        => [
					'meta_value_num' => 'DESC',
					'date'           => $order,
				],
			]
		);

		if ( count( $tax_query ) > 1 ) {

			$args['tax_query'] = $tax_query;

		}

		if ( $meta_filters !== [] ) {

			$args['meta_query'] = array_merge( $args['meta_query'], $meta_filters );

		}

		if ( $search !== '' ) {

			$args['s'] = $search;

			// Search text is free-form, so caching it would let a caller fill the
			// object cache with one entry per phrase.
			$args['cache_results'] = false;

		}

		if ( $impossible ) {

			return [
				'filters'          => jpkcom_acf_references_ability_json_object( $echo_filters ),
				'unknown'          => jpkcom_acf_references_ability_json_object( $unknown ),
				'total'            => 0,
				'page'             => $page,
				'per_page'         => $per_page,
				'total_pages'      => 0,
				'archive_url'      => jpkcom_acf_references_ability_archive_url(),
				'language'         => determine_locale(),
				'references'       => [],
				'unreadable_total' => 0,
			];

		}

		$query = new WP_Query( $args );

		$total       = (int) $query->found_posts;
		$total_pages = (int) $query->max_num_pages;

		// WP_Query reports 0 of everything for a page past the end: set_found_posts()
		// returns early when there are no posts, so found_posts and max_num_pages
		// stay at 0 and the answer contradicts itself. Re-run for page one on that
		// path only, and keep the posts empty.
		if ( $query->posts === [] && $page > 1 ) {

			$recover          = $args;
			$recover['paged'] = 1;

			$first       = new WP_Query( $recover );
			$total       = (int) $first->found_posts;
			$total_pages = (int) $first->max_num_pages;

		}

		$references     = [];
		$unreadable     = 0;

		foreach ( $query->posts as $post ) {

			if ( ! $post instanceof WP_Post ) {

				++$unreadable;

				continue;

			}

			$data = jpkcom_acf_references_get_reference_data( (int) $post->ID, false );

			if ( $data === [] ) {

				++$unreadable;

				continue;

			}

			$references[] = $data;

		}

		return [
			// The REQUEST, not the executed query. A site callback on pre_get_posts
			// can rewrite paging, ordering or the search term after these arguments
			// were built, and this ability cannot vouch for a window it did not get
			// to see.
			'filters'          => jpkcom_acf_references_ability_json_object( $echo_filters ),
			'unknown'          => jpkcom_acf_references_ability_json_object( $unknown ),
			'total'            => $total,
			'page'             => $page,
			'per_page'         => $per_page,
			'total_pages'      => $total_pages,
			'archive_url'      => jpkcom_acf_references_ability_archive_url(),
			'language'         => determine_locale(),
			'references'       => $references,
			'unreadable_total' => $unreadable,
		];

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_get_reference_inner' ) ) {

	/**
	 * Return one reference by ID.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array<string, mixed>|WP_Error Result.
	 */
	function jpkcom_acf_references_ability_get_reference_inner( mixed $input = null ): array|WP_Error {

		$normalised = jpkcom_acf_references_ability_normalise_input( $input );

		if ( $normalised === null ) {

			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_invalid_input',
				__( 'The input has to be an object carrying an "id" property, for example {"id": 42}.', 'jpkcom-acf-references' )
			);

		}

		$keys_valid = jpkcom_acf_references_ability_validate_input_keys(
			$normalised,
			JPKCOM_ACFREFERENCES_ABILITY_INPUT_KEYS['jpkcom-acf-references/get-reference']
		);

		if ( $keys_valid instanceof WP_Error ) {

			return $keys_valid;

		}

		if ( ! array_key_exists( 'id', $normalised ) ) {

			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_invalid_input',
				__( 'The "id" parameter is required and names the reference to return. It is a reference post ID as returned by jpkcom-acf-references/query-references, not a title and not a slug.', 'jpkcom-acf-references' )
			);

		}

		$raw = $normalised['id'];

		// No cast: a cast of an object without __toString throws. A JSON number
		// arrives as int or float depending on how it was written.
		if ( ! is_scalar( value: $raw ) ) {

			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_invalid_input',
				__( 'The "id" parameter has to be a positive whole number.', 'jpkcom-acf-references' )
			);

		}

		$id = absint( $raw );

		if ( $id < 1 ) {

			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_invalid_input',
				__( 'The "id" parameter has to be a positive whole number.', 'jpkcom-acf-references' )
			);

		}

		$data = jpkcom_acf_references_get_reference_data( $id, true );

		if ( $data === [] ) {

			// Deliberately the same answer for an id that does not exist and one
			// that exists but cannot be read, so this cannot be used to probe which
			// IDs are present.
			return jpkcom_acf_references_ability_error(
				'jpkcom_acf_references_not_found',
				__( 'That id does not resolve to a reference that can be read. The answer is deliberately identical for an id that does not exist, one that is not a reference, and one that is not publicly readable.', 'jpkcom-acf-references' ),
				404
			);

		}

		// `listed` is answered BY the visibility rule restricted to this one ID, not
		// by a PHP re-derivation of it. A paraphrase disagreed with the SQL in the
		// sibling plugin for a stored date of '2025-11-30 00:00:00', because
		// CAST(... AS DATE) parses it and DateTimeImmutable does not - the listing
		// excluded the record while the single-record ability reported it as listed.
		$listed_args          = jpkcom_acf_references_build_reference_query_args();
		$listed_args['p']     = $id;
		$data['listed']       = jpkcom_acf_references_ability_count_query( $listed_args ) === 1;
		$data['language']     = determine_locale();

		return $data;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_list_filters' ) ) {

	/**
	 * Execute callback for jpkcom-acf-references/list-filters.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array<string, mixed>|WP_Error Result.
	 */
	function jpkcom_acf_references_ability_list_filters( mixed $input = null ): array|WP_Error {

		return jpkcom_acf_references_ability_boundary(
			static fn(): array|WP_Error => jpkcom_acf_references_ability_list_filters_inner( $input ),
			'jpkcom-acf-references/list-filters'
		);

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_query_references' ) ) {

	/**
	 * Execute callback for jpkcom-acf-references/query-references.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array<string, mixed>|WP_Error Result.
	 */
	function jpkcom_acf_references_ability_query_references( mixed $input = null ): array|WP_Error {

		return jpkcom_acf_references_ability_boundary(
			static fn(): array|WP_Error => jpkcom_acf_references_ability_query_references_inner( $input ),
			'jpkcom-acf-references/query-references'
		);

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_ability_get_reference' ) ) {

	/**
	 * Execute callback for jpkcom-acf-references/get-reference.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $input Ability input.
	 * @return array<string, mixed>|WP_Error Result.
	 */
	function jpkcom_acf_references_ability_get_reference( mixed $input = null ): array|WP_Error {

		return jpkcom_acf_references_ability_boundary(
			static fn(): array|WP_Error => jpkcom_acf_references_ability_get_reference_inner( $input ),
			'jpkcom-acf-references/get-reference'
		);

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_get_ability_definitions' ) ) {

	/**
	 * Build the registration arguments for every ability this plugin provides.
	 *
	 * Reads no WordPress state and touches no registry, which is what lets the CI
	 * harness assert the shape of these arrays without a WordPress installation.
	 * Not free of side effects though: __() and the meta filter each fire
	 * apply_filters(), so third-party callbacks run whenever this is called.
	 *
	 * @since 1.2.0
	 *
	 * @return array<string, array<string, mixed>> Ability name => registration args.
	 */
	function jpkcom_acf_references_get_ability_definitions(): array {

		$term_schema = [
			'type'       => 'object',
			'properties' => [
				'slug'  => [
					'type'        => 'string',
					'description' => __( 'Term slug. Use this value as filter input, never the name.', 'jpkcom-acf-references' ),
				],
				'name'  => [
					'type'        => 'string',
					'description' => __( 'Human-readable term name. Locale-dependent, and not valid as filter input.', 'jpkcom-acf-references' ),
				],
				'count' => [
					'type'        => 'integer',
					'description' => __( 'Number of posts carrying this term across the whole site. NOT narrowed by any other filter, so it does not describe the size of a combined result set, and it counts every post status the taxonomy is attached to.', 'jpkcom-acf-references' ),
				],
			],
		];

		$related_schema = [
			'type'       => 'object',
			'properties' => [
				'id'    => [
					'type'        => 'integer',
					'description' => __( 'Post ID. This is the value the "customer" and "location" filters take.', 'jpkcom-acf-references' ),
				],
				'title' => [
					'type'        => 'string',
					'description' => __( 'Human-readable title.', 'jpkcom-acf-references' ),
				],
			],
		];

		$reference_schema = [
			'id'        => [ 'type' => 'integer', 'description' => __( 'Reference post ID. Pass this to jpkcom-acf-references/get-reference.', 'jpkcom-acf-references' ) ],
			'title'     => [ 'type' => 'string', 'description' => __( 'Reference title.', 'jpkcom-acf-references' ) ],
			'url'       => [ 'type' => 'string', 'description' => __( 'Where this reference sends a visitor: the editor-supplied external URL when one is set, otherwise the permalink. Check "redirects" to tell the two apart.', 'jpkcom-acf-references' ) ],
			'redirects' => [ 'type' => 'boolean', 'description' => __( 'True when an external URL is set. The reference page then answers every visitor with a 307 redirect instead of rendering, so no detail data was ever published for it.', 'jpkcom-acf-references' ) ],
			'date'      => [ 'type' => 'string', 'description' => __( 'Publication date in ISO 8601, UTC.', 'jpkcom-acf-references' ) ],
			'year'      => [ 'type' => 'string', 'description' => __( 'Project year as stored, empty when unset.', 'jpkcom-acf-references' ) ],
			'expired'   => [ 'type' => 'boolean', 'description' => __( 'True when the stored expiry date lies before today in the site timezone.', 'jpkcom-acf-references' ) ],
			'customers' => [ 'type' => 'array', 'items' => $related_schema, 'description' => __( 'Linked customers, published ones only.', 'jpkcom-acf-references' ) ],
			'locations' => [ 'type' => 'array', 'items' => $related_schema, 'description' => __( 'Linked locations, published ones only.', 'jpkcom-acf-references' ) ],
			'types'     => [ 'type' => 'array', 'items' => $term_schema, 'description' => __( 'Assigned reference-type terms.', 'jpkcom-acf-references' ) ],
			'filter_1'  => [ 'type' => 'array', 'items' => $term_schema, 'description' => __( 'Assigned reference-filter-1 terms.', 'jpkcom-acf-references' ) ],
			'filter_2'  => [ 'type' => 'array', 'items' => $term_schema, 'description' => __( 'Assigned reference-filter-2 terms.', 'jpkcom-acf-references' ) ],
		];

		return [

			'jpkcom-acf-references/list-filters' => [
				'label'       => __( 'List available reference filters', 'jpkcom-acf-references' ),
				'description' => __( 'Returns the values this site can filter references by: the three taxonomies with their terms, plus the customers and locations. Call this before jpkcom-acf-references/query-references so term slugs and post IDs never have to be guessed. Also reports how many published references the public listing rule excludes and why.', 'jpkcom-acf-references' ),
				'category'    => JPKCOM_ACFREFERENCES_ABILITY_CATEGORY,

				'input_schema' => [
					'type'    => 'object',
					// Top level, deliberately. WP_Ability::normalize_input() substitutes
					// this value when the input is exactly null, and nothing else does -
					// so without it the most obvious call there is, this ability taking
					// no parameters, fails validate_input() before the callback runs.
					//
					// An object rather than []: PHP serialises an empty array as a JSON
					// array, core's REST list controller rewrites that special case for
					// its own response, and the MCP adapter does NOT - it publishes
					// get_input_schema() verbatim.
					'default' => (object) array(),
					// NO `properties` key at all, and additionalProperties => false.
					// Declaring properties as an empty stdClass so the schema encodes as
					// {} is worse than the [] it replaces: core's
					// rest_validate_object_value_from_schema() does an array offset on
					// it, which is a hard Error in PHP 8 - measured in the sibling
					// plugin as an anonymous HTTP 500 for any request carrying any key.
					// Omitting the key is the only combination that yields a clean
					// WP_Error, and additionalProperties => false is what makes an
					// unknown key a 400 rather than something ignored.
					'additionalProperties' => false,
				],

				'output_schema' => [
					'type'       => 'object',
					'properties' => [
						'types'                => [ 'type' => 'array', 'items' => $term_schema, 'description' => __( 'Every reference-type term, whether or not a reference currently uses it. Filter with the slug.', 'jpkcom-acf-references' ) ],
						'filter_1'             => [ 'type' => 'array', 'items' => $term_schema, 'description' => __( 'Every reference-filter-1 term.', 'jpkcom-acf-references' ) ],
						'filter_2'             => [ 'type' => 'array', 'items' => $term_schema, 'description' => __( 'Every reference-filter-2 term.', 'jpkcom-acf-references' ) ],
						'customers'            => [ 'type' => 'array', 'items' => $related_schema, 'description' => __( 'Published customers, as accepted by the "customer" filter.', 'jpkcom-acf-references' ) ],
						'locations'            => [ 'type' => 'array', 'items' => $related_schema, 'description' => __( 'Published locations, as accepted by the "location" filter.', 'jpkcom-acf-references' ) ],
						'vocabulary_truncated' => [ 'type' => 'boolean', 'description' => __( 'True when at least one list above was cut off at this site\'s reporting limit, so it is not the complete vocabulary.', 'jpkcom-acf-references' ) ],
						'archive_url'          => [ 'type' => 'string', 'description' => __( 'Front-end archive URL for references, empty when the post type has none.', 'jpkcom-acf-references' ) ],
						'visibility'           => [
							'type'        => 'object',
							'description' => __( 'Site-level counts, not counts for any particular query. published = all published references; listed = those the public listing shows; the two hidden_* numbers are the difference and partition the remainder exactly.', 'jpkcom-acf-references' ),
							'properties'  => [
								'published'               => [ 'type' => 'integer', 'description' => __( 'Published references, regardless of the listing rule.', 'jpkcom-acf-references' ) ],
								'listed'                  => [ 'type' => 'integer', 'description' => __( 'References the public listing shows.', 'jpkcom-acf-references' ) ],
								'hidden_expired'          => [ 'type' => 'integer', 'description' => __( 'Excluded because their expiry date has passed.', 'jpkcom-acf-references' ) ],
								'hidden_missing_featured' => [ 'type' => 'integer', 'description' => __( 'Excluded because they carry no reference_featured meta row at all. The row\'s value is irrelevant; a stored 0 is listed, a missing row is not.', 'jpkcom-acf-references' ) ],
							],
						],
						'language'             => [ 'type' => 'string', 'description' => __( 'Locale these labels were read in.', 'jpkcom-acf-references' ) ],
					],
				],

				'execute_callback'    => 'jpkcom_acf_references_ability_list_filters',
				'permission_callback' => 'jpkcom_acf_references_ability_permission_list_filters',
				'meta'                => jpkcom_acf_references_ability_meta( 'jpkcom-acf-references/list-filters' ),
			],

			'jpkcom-acf-references/query-references' => [
				'label'       => __( 'Query references', 'jpkcom-acf-references' ),
				'description' => __( 'Runs a filtered, paginated query over the references this site lists publicly and returns compact records. Only values reported by jpkcom-acf-references/list-filters are accepted; anything else is reported back under "unknown" rather than silently ignored.', 'jpkcom-acf-references' ),
				'category'    => JPKCOM_ACFREFERENCES_ABILITY_CATEGORY,

				'input_schema' => [
					'type'    => 'object',
					// See the note on list-filters: this rescues execute( null ), and it
					// is an object rather than [] because MCP clients read this value
					// unmodified.
					'default' => (object) array(),
					'properties' => [
						'type'     => [
							'type'        => 'array',
							'description' => __( 'reference-type slugs to filter by, as returned by list-filters. Several slugs in one axis are combined with OR; different axes with AND.', 'jpkcom-acf-references' ),
							'items'       => [ 'type' => 'string' ],
							'maxItems'    => JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES,
						],
						'filter_1' => [
							'type'        => 'array',
							'description' => __( 'reference-filter-1 slugs to filter by.', 'jpkcom-acf-references' ),
							'items'       => [ 'type' => 'string' ],
							'maxItems'    => JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES,
						],
						'filter_2' => [
							'type'        => 'array',
							'description' => __( 'reference-filter-2 slugs to filter by.', 'jpkcom-acf-references' ),
							'items'       => [ 'type' => 'string' ],
							'maxItems'    => JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES,
						],
						'customer' => [
							'type'        => 'array',
							'description' => __( 'Customer post IDs to filter by, as returned by list-filters.', 'jpkcom-acf-references' ),
							'items'       => [ 'type' => 'integer' ],
							'maxItems'    => JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES,
						],
						'location' => [
							'type'        => 'array',
							'description' => __( 'Location post IDs to filter by, as returned by list-filters.', 'jpkcom-acf-references' ),
							'items'       => [ 'type' => 'integer' ],
							'maxItems'    => JPKCOM_ACFREFERENCES_ABILITY_MAX_VALUES,
						],
						'search'   => [
							'type'        => 'string',
							'description' => __( 'Free-text search over the reference TITLE only. It is WordPress core search, which reads post_title, post_excerpt and post_content — and this plugin holds the descriptive text in ACF fields with post_content empty, so a term appearing in the short description will NOT be found here, and an empty result is not evidence that nothing matches. To search those, filter by the axes that are indexed.', 'jpkcom-acf-references' ),
						],
						'page'     => [
							'type'        => 'integer',
							'description' => __( 'Page number, starting at 1.', 'jpkcom-acf-references' ),
							'minimum'     => 1,
							'default'     => 1,
						],
						'per_page' => [
							'type'        => 'integer',
							'description' => __( 'References per page.', 'jpkcom-acf-references' ),
							'minimum'     => 1,
							'maximum'     => JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_MAX,
							'default'     => JPKCOM_ACFREFERENCES_ABILITY_PER_PAGE_DEFAULT,
						],
						'order'    => [
							'type'        => 'string',
							'description' => __( 'Direction of the date component of the sort. Featured references always sort first.', 'jpkcom-acf-references' ),
							'enum'        => [ 'ASC', 'DESC' ],
							'default'     => 'DESC',
						],
					],
				],

				'output_schema' => [
					'type'       => 'object',
					'properties' => [
						'filters'          => [ 'type' => 'object', 'description' => __( 'The filters as REQUESTED, after normalisation. This describes the request, not the executed query: a site callback on pre_get_posts can rewrite paging, ordering or the search term afterwards, and this ability cannot vouch for a window it did not see.', 'jpkcom-acf-references' ) ],
						'unknown'          => [ 'type' => 'object', 'description' => __( 'Requested values that match nothing on this site, keyed by axis. A non-empty value explains why a result set is empty.', 'jpkcom-acf-references' ) ],
						'total'            => [ 'type' => 'integer', 'description' => __( 'Total number of listed references matching the filters.', 'jpkcom-acf-references' ) ],
						'page'             => [ 'type' => 'integer', 'description' => __( 'The page that was returned.', 'jpkcom-acf-references' ) ],
						'per_page'         => [ 'type' => 'integer', 'description' => __( 'The page size that was applied after clamping.', 'jpkcom-acf-references' ) ],
						'total_pages'      => [ 'type' => 'integer', 'description' => __( 'Number of pages available for these filters.', 'jpkcom-acf-references' ) ],
						'archive_url'      => [ 'type' => 'string', 'description' => __( 'Front-end archive URL for references, empty when there is none.', 'jpkcom-acf-references' ) ],
						'language'         => [ 'type' => 'string', 'description' => __( 'Locale this answer was read in.', 'jpkcom-acf-references' ) ],
						'references'       => [ 'type' => 'array', 'items' => [ 'type' => 'object', 'properties' => $reference_schema ], 'description' => __( 'The matching references for the requested page.', 'jpkcom-acf-references' ) ],
						'unreadable_total' => [ 'type' => 'integer', 'description' => __( 'How many records on this page were dropped because their stored data could not be read. Normally 0; a non-zero value means the page holds fewer records than per_page for a site-side reason.', 'jpkcom-acf-references' ) ],
					],
				],

				'execute_callback'    => 'jpkcom_acf_references_ability_query_references',
				'permission_callback' => 'jpkcom_acf_references_ability_permission_query_references',
				'meta'                => jpkcom_acf_references_ability_meta( 'jpkcom-acf-references/query-references' ),
			],

			'jpkcom-acf-references/get-reference' => [
				'label'       => __( 'Get one reference', 'jpkcom-acf-references' ),
				'description' => __( 'Returns one reference by ID, including the detail data its page publishes: the short description and the image gallery. A reference that no listing contains is still resolvable here, together with whether it is listed.', 'jpkcom-acf-references' ),
				'category'    => JPKCOM_ACFREFERENCES_ABILITY_CATEGORY,

				'input_schema' => [
					'type'       => 'object',
					// No top-level default here on purpose: this ability requires an id,
					// so substituting {} for a null input would only move the failure.
					'required'   => [ 'id' ],
					'properties' => [
						'id' => [
							'type'        => 'integer',
							'description' => __( 'Reference post ID, as returned by jpkcom-acf-references/query-references.', 'jpkcom-acf-references' ),
							'minimum'     => 1,
						],
					],
				],

				'output_schema' => [
					'type'       => 'object',
					'properties' => $reference_schema + [
						'listed'         => [ 'type' => 'boolean', 'description' => __( 'Whether the public listing contains this reference. Answered by running the listing rule restricted to this one ID, not by re-deriving it, so it cannot disagree with jpkcom-acf-references/query-references.', 'jpkcom-acf-references' ) ],
						'language'       => [ 'type' => 'string', 'description' => __( 'Locale this answer was read in.', 'jpkcom-acf-references' ) ],
						'detail_omitted' => [
							'type'        => 'string',
							'description' => __( 'Why no detail block is present. "redirects_externally" when an external URL sends every visitor away before the page renders, "expired" when the page redirects to the archive. In both states the description and the gallery of this reference were never published to anybody, so they are withheld here too.', 'jpkcom-acf-references' ),
						],
						'detail'         => [
							'type'        => 'object',
							'description' => __( 'Detail data, present only for a reference whose page actually renders for an anonymous visitor.', 'jpkcom-acf-references' ),
							'properties'  => [
								'short_description' => [ 'type' => 'string', 'description' => __( 'The short description as plain text.', 'jpkcom-acf-references' ) ],
								'gallery'           => [
									'type'        => 'array',
									'description' => __( 'Gallery images, as URL and alt text.', 'jpkcom-acf-references' ),
									'items'       => [
										'type'       => 'object',
										'properties' => [
											'url' => [ 'type' => 'string', 'description' => __( 'Image URL.', 'jpkcom-acf-references' ) ],
											'alt' => [ 'type' => 'string', 'description' => __( 'Alt text stored on the attachment, empty when none is set.', 'jpkcom-acf-references' ) ],
										],
									],
								],
							],
						],
					],
				],

				'execute_callback'    => 'jpkcom_acf_references_ability_get_reference',
				'permission_callback' => 'jpkcom_acf_references_ability_permission_get_reference',
				'meta'                => jpkcom_acf_references_ability_meta( 'jpkcom-acf-references/get-reference' ),
			],

		];

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_register_ability_category' ) ) {

	/**
	 * Register the shared category, unless a sibling plugin already did.
	 *
	 * Categories are global and first-wins. Without the check all abilities still
	 * register - the category exists either way - but _doing_it_wrong() fires, and
	 * which plugin wins depends on load order.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	function jpkcom_acf_references_register_ability_category(): void {

		if ( ! jpkcom_acf_references_abilities_enabled() ) {

			return;

		}

		if ( function_exists( function: 'wp_has_ability_category' )
			&& wp_has_ability_category( JPKCOM_ACFREFERENCES_ABILITY_CATEGORY ) ) {

			return;

		}

		$category = wp_register_ability_category(
			JPKCOM_ACFREFERENCES_ABILITY_CATEGORY,
			[
				'label'       => __( 'JPKCom Content', 'jpkcom-acf-references' ),
				'description' => __( 'Read-only access to content managed by the JPKCom content plugins.', 'jpkcom-acf-references' ),
			]
		);

		if ( $category === null ) {

			jpkcom_acf_references_ability_log( 'ability category registration returned null' );

		}

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_register_abilities' ) ) {

	/**
	 * Register every ability this plugin provides.
	 *
	 * wp_register_ability() returns null on EVERY failure path and reports only
	 * through _doing_it_wrong(), which is silent in production - and so is the
	 * debug log without WP_DEBUG. The return value is checked, but do not expect a
	 * registration failure to announce itself on a customer site.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	function jpkcom_acf_references_register_abilities(): void {

		if ( ! jpkcom_acf_references_abilities_enabled() ) {

			return;

		}

		foreach ( jpkcom_acf_references_get_ability_definitions() as $name => $args ) {

			if ( wp_register_ability( $name, $args ) === null ) {

				jpkcom_acf_references_ability_log( 'registration returned null for ' . $name );

			}

		}

	}

}


add_action( 'wp_abilities_api_categories_init', 'jpkcom_acf_references_register_ability_category' );
add_action( 'wp_abilities_api_init', 'jpkcom_acf_references_register_abilities' );

<?php
/**
 * Behavioural test for the list shortcode's query construction.
 *
 * Unlike tests/test-conventions.php this is not a source scan: it loads
 * includes/shortcodes.php with WordPress stubbed out, runs the shortcode
 * callback, and inspects the arguments it hands to WP_Query. The seam is the
 * existing `jpkcom_acf_references_list_query_args` filter — the stub throws
 * there, which captures the args and aborts before `new WP_Query`.
 *
 * What it pins down:
 * - the three taxonomy filters go through tax_query, not meta LIKE
 * - include_children stays false, so hierarchical children are not pulled in
 * - customer/location remain meta-based (they are post-object fields)
 * - non-numeric input produces no clause instead of one matching nothing
 *
 * Run with:
 *     php tests/test-tax-query.php
 *
 * @package JPKCom_ACF_References
 * @since 1.1.0
 */

declare(strict_types=1);

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

/** Carries the captured WP_Query arguments out of the shortcode callback. */
final class CapturedArgs extends Exception {

	/** @var array<string,mixed> */
	public array $args;

	/**
	 * @param array<string,mixed> $args Query arguments.
	 */
	public function __construct( array $args ) {
		parent::__construct( 'captured' );
		$this->args = $args;
	}
}

/** @var array<string,callable> $shortcodes */
$shortcodes = [];

function add_action( string $hook, callable $cb, int $priority = 10, int $args = 1 ): void {
	// The plugin registers its shortcodes inside an init closure; run it now.
	$cb();
}

function add_shortcode( string $tag, callable $cb ): void {
	global $shortcodes;
	$shortcodes[ $tag ] = $cb;
}

function apply_filters( string $tag, mixed $value, mixed ...$rest ): mixed {
	if ( 'jpkcom_acf_references_list_query_args' === $tag ) {
		throw new CapturedArgs( (array) $value );
	}
	return $value;
}

function shortcode_atts( array $pairs, array $atts, string $shortcode = '' ): array {
	return array_merge( $pairs, array_intersect_key( $atts, $pairs ) );
}

function sanitize_text_field( string $str ): string {
	return trim( strip_tags( $str ) );
}

function absint( mixed $v ): int {
	return abs( (int) $v );
}

function current_time( string $type, int $gmt = 0 ): string {
	return date( $type );
}

function trailingslashit( string $s ): string {
	return rtrim( $s, '/' ) . '/';
}

function __( string $text, string $domain = '' ): string {
	return $text;
}

require_once dirname( __DIR__ ) . '/includes/shortcodes.php';

$pass = 0;
$fail = 0;

/**
 * Report a single assertion.
 *
 * @param string $label Human-readable check name.
 * @param bool   $ok    Whether the check held.
 * @param string $why   Explanation printed on failure.
 */
function check( string $label, bool $ok, string $why = '' ): void {
	global $pass, $fail;

	if ( $ok ) {
		$pass++;
		echo "  PASS  {$label}\n";
		return;
	}

	$fail++;
	echo "  FAIL  {$label}\n";

	if ( '' !== $why ) {
		echo "        {$why}\n";
	}
}

/**
 * Run the list shortcode and return the query args it built.
 *
 * @param array<string,string> $atts Shortcode attributes.
 * @return array<string,mixed>
 */
function query_args_for( array $atts ): array {
	global $shortcodes;

	try {
		$shortcodes['jpkcom_acf_references_list']( $atts );
	} catch ( CapturedArgs $e ) {
		return $e->args;
	}

	throw new RuntimeException( 'shortcode did not reach the query_args filter' );
}

/**
 * Collect every meta_query clause, at any nesting depth.
 *
 * @param array<mixed> $mq meta_query array.
 * @return array<int,array<string,mixed>>
 */
function flatten_clauses( array $mq ): array {
	$out = [];

	foreach ( $mq as $key => $entry ) {
		if ( 'relation' === $key || ! is_array( $entry ) ) {
			continue;
		}

		if ( isset( $entry['key'] ) ) {
			$out[] = $entry;
			continue;
		}

		$out = array_merge( $out, flatten_clauses( $entry ) );
	}

	return $out;
}

echo "\nTaxonomy filters use tax_query\n";

$args = query_args_for( [
	'type'     => '1,5',
	'filter_1' => '2',
	'filter_2' => '3,9',
] );

$tq = $args['tax_query'] ?? [];

$by_taxonomy = [];
foreach ( $tq as $key => $clause ) {
	if ( 'relation' !== $key && is_array( $clause ) && isset( $clause['taxonomy'] ) ) {
		$by_taxonomy[ $clause['taxonomy'] ] = $clause;
	}
}

check(
	'tax_query exists',
	! empty( $tq ),
	'The shortcode still builds no tax_query at all.'
);

check(
	'relation between taxonomies is AND',
	( $tq['relation'] ?? null ) === 'AND',
	'Across taxonomies the filters must combine with AND, as the meta variant did.'
);

foreach ( [ 'reference-type' => [ 1, 5 ], 'reference-filter-1' => [ 2 ], 'reference-filter-2' => [ 3, 9 ] ] as $tax => $expected ) {

	$clause = $by_taxonomy[ $tax ] ?? null;

	check(
		"{$tax}: clause present with term IDs " . implode( ',', $expected ),
		is_array( $clause )
			&& ( $clause['field'] ?? '' ) === 'term_id'
			&& ( $clause['operator'] ?? '' ) === 'IN'
			&& array_values( (array) ( $clause['terms'] ?? [] ) ) === $expected,
		'Expected a term_id/IN clause; got: ' . var_export( $clause, true )
	);

	check(
		"{$tax}: include_children is false",
		is_array( $clause ) && ( $clause['include_children'] ?? null ) === false,
		'These taxonomies are hierarchical. Defaulting include_children to true would '
		. 'also match posts filed under child terms, which the meta LIKE variant never did.'
	);
}

echo "\nNo leading-wildcard meta scans left for the taxonomy fields\n";

$meta_clauses = flatten_clauses( (array) ( $args['meta_query'] ?? [] ) );

foreach ( [ 'reference_type', 'reference_filter_1', 'reference_filter_2' ] as $field ) {

	$offenders = array_filter(
		$meta_clauses,
		static fn( array $c ): bool => ( $c['key'] ?? '' ) === $field && ( $c['compare'] ?? '' ) === 'LIKE'
	);

	check(
		"no LIKE meta clause for {$field}",
		empty( $offenders ),
		'A LIKE clause with a leading wildcard cannot use an index and scans every '
		. 'meta row for that key. The term relationship is indexed — use it.'
	);
}

echo "\nPost-object fields stay meta-based\n";

$args_po = query_args_for( [ 'customer' => '12', 'location' => '78,90' ] );
$po_clauses = flatten_clauses( (array) ( $args_po['meta_query'] ?? [] ) );

foreach ( [ 'reference_customer' => 1, 'reference_location' => 2 ] as $field => $count ) {

	$found = array_filter(
		$po_clauses,
		static fn( array $c ): bool => ( $c['key'] ?? '' ) === $field && ( $c['compare'] ?? '' ) === 'LIKE'
	);

	check(
		"{$field}: still {$count} meta clause(s)",
		count( $found ) === $count,
		'reference_customer and reference_location are ACF post-object fields, not '
		. 'taxonomies. There is no term relationship to query, so these must not be '
		. 'swept into the tax_query rewrite.'
	);
}

check(
	'no tax_query when only post-object filters are set',
	! isset( $args_po['tax_query'] ),
	'A bare [ relation => AND ] is not an empty filter to WP_Query, it is a malformed one.'
);

echo "\nJunk input produces no clause\n";

$args_junk = query_args_for( [ 'type' => 'abc,,0' ] );

check(
	'non-numeric type CSV yields no tax_query',
	! isset( $args_junk['tax_query'] ),
	'absint() turns non-numeric input into 0; those must be dropped rather than '
	. 'becoming a clause that matches nothing.'
);

printf( "\n  %d passed, %d failed\n", $pass, $fail );

exit( $fail > 0 ? 1 : 0 );

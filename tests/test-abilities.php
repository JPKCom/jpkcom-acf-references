<?php
/**
 * Guards for the Abilities API registration.
 *
 * Runs without a WordPress installation: jpkcom_acf_references_get_ability_definitions()
 * reads no WordPress state and touches no registry, which is what lets the shape
 * of the registration arrays be asserted here. The few core functions it does
 * reach are stubbed below.
 *
 * Several checks are STRUCTURAL rather than per-case on purpose. The sibling
 * plugin shipped its unknown-key guard on two abilities of three and stayed
 * green, because every assertion written for that guard happened to target an
 * ability that had it. A check that asks "does every ability do this" cannot be
 * satisfied by the one that does not.
 *
 * @package   JPKCom_ACF_References
 */

declare(strict_types=1);

// --- Stubs ------------------------------------------------------------------

define( 'ABSPATH', dirname( __DIR__ ) . '/' );

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( string $text, string $domain = 'default' ): string {
		return $text;
	}
}

function __( string $text, string $domain = 'default' ): string {
	return $text;
}

function apply_filters( string $hook, mixed $value, mixed ...$args ): mixed {
	return $value;
}

function add_action( string $hook, mixed $callback, int $priority = 10, int $accepted = 1 ): bool {
	return true;
}

$root = dirname( __DIR__ );

require_once $root . '/includes/abilities.php';

// --- Harness ----------------------------------------------------------------

$pass = 0;
$fail = 0;

function section( string $title ): void {
	echo "\n" . $title . "\n";
}

function chk( string $label, bool $ok, string $why = '' ): void {
	global $pass, $fail;

	if ( $ok ) {
		$pass++;
		echo "  PASS  {$label}\n";
		return;
	}

	$fail++;
	echo "  FAIL  {$label}\n";

	if ( $why !== '' ) {
		echo "        why:  {$why}\n";
	}
}

$defs  = jpkcom_acf_references_get_ability_definitions();
$names = array_keys( $defs );
$src   = (string) file_get_contents( $root . '/includes/abilities.php' );
$data  = (string) file_get_contents( $root . '/includes/references-data.php' );

// --- Registration shape -----------------------------------------------------

section( 'Registration shape' );

chk(
	'three abilities are defined',
	count( $defs ) === 3,
	'Got ' . count( $defs ) . '. Every check below iterates this list, so a shrunken list quietly shrinks the suite.'
);

chk(
	'the names are the documented ones',
	$names === [
		'jpkcom-acf-references/list-filters',
		'jpkcom-acf-references/query-references',
		'jpkcom-acf-references/get-reference',
	],
	'Ability names are a public contract; renaming one breaks every caller that stored it.'
);

foreach ( $defs as $name => $args ) {

	chk(
		$name . ': registered into the shared category',
		( $args['category'] ?? null ) === 'jpkcom-content',
		'The three JPKCom content plugins share one category. Categories are global and first-wins.'
	);

	$annotations = $args['meta']['annotations'] ?? [];

	chk(
		$name . ': all three annotations set explicitly',
		( $annotations['readonly'] ?? null ) === true
			&& ( $annotations['destructive'] ?? null ) === false
			&& ( $annotations['idempotent'] ?? null ) === true,
		'They default to null, and the REST run controller derives the HTTP verb from them: without readonly the run route is POST-only.'
	);

	chk(
		$name . ': has label and description',
		is_string( $args['label'] ?? null ) && $args['label'] !== ''
			&& is_string( $args['description'] ?? null ) && $args['description'] !== '',
		'These are what an agent reads to decide whether to call the ability at all.'
	);

}

// --- Input schema -----------------------------------------------------------

section( 'Input schema' );

foreach ( [ 'jpkcom-acf-references/list-filters', 'jpkcom-acf-references/query-references' ] as $name ) {

	$schema = $defs[ $name ]['input_schema'] ?? [];

	chk(
		$name . ': carries a top-level default',
		array_key_exists( 'default', $schema ),
		'WP_Ability::normalize_input() substitutes the TOP-LEVEL default when the input is exactly null, and nothing else does. Without it the most obvious call there is - no input at all - fails validation before the callback runs.'
	);

	chk(
		$name . ': the default encodes as {} and not []',
		wp_json_encode_stub( $schema['default'] ?? null ) === '{}',
		'The schema declares type: object. PHP serialises an empty array as a JSON array; core\'s REST list controller repairs that special case for its own response, the MCP adapter does not - it publishes get_input_schema() verbatim.'
	);

}

$lf = $defs['jpkcom-acf-references/list-filters']['input_schema'] ?? [];

chk(
	'list-filters declares NO properties key at all',
	! array_key_exists( 'properties', $lf ),
	'An empty stdClass here is an anonymous fatal: core does an array offset on it, which is a hard Error in PHP 8. An empty array is invalid JSON Schema. Omitting the key is the only combination that yields a clean WP_Error.'
);

chk(
	'list-filters refuses unknown keys through the schema',
	( $lf['additionalProperties'] ?? null ) === false,
	'It declares no properties, so this is the only thing that can refuse a key there.'
);

foreach ( [ 'jpkcom-acf-references/query-references', 'jpkcom-acf-references/get-reference' ] as $name ) {

	chk(
		$name . ': does NOT declare additionalProperties',
		! array_key_exists( 'additionalProperties', $defs[ $name ]['input_schema'] ?? [] ),
		'validate_input() runs BEFORE the execute callback, so declaring it preempts the plugin guard and replaces a message naming the accepted keys with core\'s "not a valid property of the object". Measured on WP 7.0.3 in the sibling plugin.'
	);

}

$gr = $defs['jpkcom-acf-references/get-reference']['input_schema'] ?? [];

chk(
	'get-reference requires an id',
	in_array( 'id', $gr['required'] ?? [], true ),
	'It has nothing sensible to do without one.'
);

chk(
	'get-reference carries no top-level default',
	! array_key_exists( 'default', $gr ),
	'Substituting {} for a null input would only move the failure one step.'
);

// --- The guarded key list matches the schema --------------------------------

section( 'Guarded input keys agree with the schemas' );

foreach ( $defs as $name => $args ) {

	$declared = array_keys( (array) ( $args['input_schema']['properties'] ?? [] ) );
	$guarded  = JPKCOM_ACFREFERENCES_ABILITY_INPUT_KEYS[ $name ] ?? null;

	if ( is_array( $guarded ) ) {
		sort( $declared );
		sort( $guarded );
	}

	chk(
		$name . ': guarded keys match the schema properties',
		$guarded === $declared,
		'Two statements of one list. A key only in the schema is refused by the guard although it is declared to callers - a guard rejecting a correct request, which is worse than the hole it closes. A key only in the constant is waved through and read by nobody. Got '
			. var_export( $guarded, true ) . ', schema declares ' . var_export( $declared, true ) . '.'
	);

}

// --- Structural guards ------------------------------------------------------

section( 'Structural guards on the callbacks' );

/**
 * Slice a function body out of the source by brace matching.
 */
function body_of( string $source, string $needle ): ?string {
	$start = strpos( $source, $needle );

	if ( $start === false ) {
		return null;
	}

	$open  = strpos( $source, '{', $start );
	$depth = 0;

	for ( $i = $open, $len = strlen( $source ); $i < $len; $i++ ) {
		if ( $source[ $i ] === '{' ) {
			$depth++;
		} elseif ( $source[ $i ] === '}' ) {
			$depth--;
			if ( $depth === 0 ) {
				return substr( $source, $open, $i - $open );
			}
		}
	}

	return null;
}

foreach ( [ 'list_filters', 'query_references', 'get_reference' ] as $slug ) {

	$body = body_of( $src, 'function jpkcom_acf_references_ability_' . $slug . '_inner(' );

	chk(
		$slug . '_inner calls the unknown-key guard',
		$body !== null && str_contains( $body, 'jpkcom_acf_references_ability_validate_input_keys(' ),
		'A guard on a subset of the abilities is a trap: a caller that learned the refusal on one assumes it everywhere, and the ability that silently accepts is the one it will trust. This exact gap shipped in the sibling plugin.'
	);

	$outer = body_of( $src, 'function jpkcom_acf_references_ability_' . $slug . '(' );

	chk(
		$slug . ' runs inside the Throwable boundary',
		$outer !== null && str_contains( $outer, 'jpkcom_acf_references_ability_boundary(' ),
		'ACF throws while READING corrupt meta, so no check on the shape beforehand catches it.'
	);

}

// --- The read-only rule -----------------------------------------------------

section( 'The read-only rule' );

chk(
	'the long-form field is never read in ACF\'s formatted mode',
	preg_match( "/get_field\(\s*'reference_short_description'\s*,\s*[^,]+\s*\)/", $data ) === 0
		&& preg_match( "/get_field\(\s*'reference_short_description'\s*,[^,]+,\s*true\s*\)/", $data ) === 0,
	'get_field()\'s default formatted mode pipes a wysiwyg value through acf_the_content, which carries do_shortcode at 11 and WP_Embed::autoembed at 8. With no post context - which is exactly an ability callback - autoembed fetches the remote URL and wp_insert_post()s an oembed_cache row. The third argument must be present and false.'
);

chk(
	'get_fields() is never called',
	! str_contains( $src, 'get_fields(' ) && ! str_contains( $data, 'get_fields(' ),
	'It would read every field including the bidirectional *_references back-references, which point at every linked record regardless of status and bypass the entire visibility rule.'
);

chk(
	'no ability path emits a WP_Post',
	preg_match( '/=>\s*\$post\s*,/', $data ) === 0,
	'WP_Post implements no JsonSerializable and exposes post_password, post_content and post_status as public properties.'
);

chk(
	'the counts never restate what "expired" means',
	! preg_match( '/reference_expiry_date/', (string) body_of( $src, 'function jpkcom_acf_references_ability_visibility_counts(' ) ),
	'The rule\'s expiry clause is an OR group of three branches and negating only the first is not its complement: MariaDB casts \'\' to \'0000-00-00\', so every reference whose date was saved and cleared would be counted as expired in the same response that lists it. Both numbers must be differences derived from the rule.'
);

chk(
	'the search limit counts BYTES',
	str_contains( $src, 'strlen( string: $search )' ),
	'WP_Query::parse_query() empties `s` over 1600 bytes using strlen(), not mb_strlen(). A character-counting guard leaves the hole open for exactly the input most likely to hit it.'
);

// --- Permission callbacks ---------------------------------------------------

section( 'Permission callbacks' );

foreach ( [ 'list_filters', 'query_references', 'get_reference' ] as $slug ) {

	$body = body_of( $src, 'function jpkcom_acf_references_ability_permission_' . $slug . '(' );

	chk(
		$slug . ': permission callback resolves to a capability check',
		$body !== null && str_contains( $body, 'jpkcom_acf_references_ability_capability(' ),
		'A literal true, or a callback built around WP_REST_Request, cannot gate anything: the argument an ability permission callback receives is the validated input value, never a request object.'
	);

}

chk(
	'the capability helper calls current_user_can',
	str_contains( (string) body_of( $src, 'function jpkcom_acf_references_ability_capability(' ), 'current_user_can(' ),
	'Without it the filter result is never actually checked against the user.'
);

printf( "\n  %d passed, %d failed\n", $pass, $fail );

exit( $fail > 0 ? 1 : 0 );

/**
 * Minimal json_encode for the default-shape assertion.
 */
function wp_json_encode_stub( mixed $value ): string {
	return (string) json_encode( $value );
}

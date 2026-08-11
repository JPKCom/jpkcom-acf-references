<?php
/**
 * Shared data layer for references.
 *
 * Holds the visibility rule and the projection of a reference into plain data.
 * Both existed only inside the list shortcode before; the abilities need the same
 * answers and must not restate them, because a second statement of a rule is a
 * second rule the moment either side is touched. That is not theory: the sibling
 * plugin jpkcom-acf-jobs shipped a "mirror image" of its own expiry rule that
 * counted every reference whose date had been saved and cleared as expired,
 * because MariaDB casts '' to '0000-00-00'.
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


if ( ! function_exists( function: 'jpkcom_acf_references_build_reference_query_args' ) ) {

	/**
	 * Build the WP_Query arguments that define a publicly listed reference.
	 *
	 * Extracted verbatim from the list shortcode, which was its only home. Three
	 * parts carry weight and none of them may be simplified:
	 *
	 * 1. `reference_featured EXISTS` — the site treats a reference without that
	 *    meta row as not listed at all. The row's VALUE is irrelevant; a stored 0
	 *    is listed, a missing row is not, and get_field() cannot tell those apart.
	 *
	 * 2. The expiry OR group has THREE branches: at or after today, no row, and
	 *    the empty string. The third is the ordinary case, not an edge case — ACF
	 *    writes '' when a date is cleared rather than deleting the row, and
	 *    MariaDB casts '' to '0000-00-00', which is less than any real date. A
	 *    negation of only the first branch is not the complement of this group.
	 *
	 * 3. `meta_key` is set for the ordering, and its own `postmeta.meta_key`
	 *    condition lands in the WHERE clause. So a reference with no featured row
	 *    is excluded TWICE, independently. Removing either one changes nothing,
	 *    which is why the number of references carrying the row cannot be read off
	 *    this query and needs one of its own.
	 *
	 * `current_time( 'Y-m-d' )` and not `date()`: WordPress sets the PHP timezone
	 * to UTC in wp-settings.php, so `date()` returns the UTC date and an expired
	 * reference would stay visible for the length of the site's offset past local
	 * midnight.
	 *
	 * @since 1.2.0
	 *
	 * @param array<string, mixed> $args Arguments merged over the base rule.
	 * @return array<string, mixed> WP_Query arguments.
	 */
	function jpkcom_acf_references_build_reference_query_args( array $args = [] ): array {

		$base = [
			'post_type'   => 'reference',
			'post_status' => 'publish',
			'meta_key'    => 'reference_featured',
			'orderby'     => [
				'meta_value_num' => 'DESC',
				'date'           => 'DESC',
			],
			'meta_query'  => [
				'relation' => 'AND',
				[
					'key'     => 'reference_featured',
					'compare' => 'EXISTS',
				],
				[
					'relation' => 'OR',
					[
						'key'     => 'reference_expiry_date',
						'value'   => current_time( type: 'Y-m-d' ),
						'compare' => '>=',
						'type'    => 'DATE',
					],
					[
						'key'     => 'reference_expiry_date',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => 'reference_expiry_date',
						'value'   => '',
						'compare' => '=',
					],
				],
			],
		];

		// A caller may extend the rule but never replace what defines it. The four
		// keys below are the rule; everything else — paging, ordering direction,
		// fields — is the caller's business.
		$protected = [ 'post_type', 'post_status', 'meta_query' ];

		foreach ( $protected as $key ) {

			unset( $args[ $key ] );

		}

		return array_merge( $base, $args );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_normalise_date' ) ) {

	/**
	 * Normalise a stored ACF date into ISO 8601, or null when it cannot be read.
	 *
	 * Never `date( 'Y-m-d', strtotime( $x ) )`: under strict_types a false from
	 * strtotime() makes date() throw a TypeError. The round-trip check is
	 * load-bearing rather than decorative — without it '20251340' becomes
	 * 2026-02-09 and '20259999' becomes 2033-06-07, both silently.
	 *
	 * Wrapped in try/catch because a NUL byte in the stored value makes
	 * createFromFormat() throw a ValueError, and MySQL longtext stores NUL.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $raw Stored value.
	 * @return string|null Y-m-d, or null when the value is unusable.
	 */
	function jpkcom_acf_references_normalise_date( mixed $raw ): ?string {

		if ( ! is_string( value: $raw ) && ! is_int( value: $raw ) ) {

			return null;

		}

		$value = trim( string: (string) $raw );

		if ( $value === '' ) {

			return null;

		}

		foreach ( [ 'Ymd', 'Y-m-d' ] as $format ) {

			try {

				$date = DateTimeImmutable::createFromFormat( format: $format, datetime: $value );

			} catch ( \Throwable ) {

				return null;

			}

			if ( $date instanceof DateTimeImmutable && $date->format( format: $format ) === $value ) {

				return $date->format( format: 'Y-m-d' );

			}

		}

		return null;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_plain_text' ) ) {

	/**
	 * Reduce a stored value to plain text.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Stored value.
	 * @return string Plain text, empty when the value is not a string.
	 */
	function jpkcom_acf_references_plain_text( mixed $value ): string {

		if ( ! is_string( value: $value ) ) {

			return '';

		}

		return trim( string: wp_strip_all_tags( text: $value ) );

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_normalise_related' ) ) {

	/**
	 * Project a post-object field into id/title pairs.
	 *
	 * Never emits a WP_Post. ACF resolves post_object fields through
	 * acf_get_posts() with post_status => 'any', so drafts and private customers
	 * genuinely arrive here — and WP_Post implements no JsonSerializable while
	 * exposing post_password, post_content and post_status as public properties.
	 * A reference linked to a password-protected customer would otherwise hand a
	 * subscriber that password in plain text.
	 *
	 * absint() before get_post(): get_post( 0 ) returns the GLOBAL post, and
	 * absint() maps false, '', null and 'abc' all to 0. ACF returns false for an
	 * unassigned post_object, and both relation fields here allow null — without
	 * this guard a reference with no customer projects itself as its own customer.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Stored value.
	 * @return array<int, array<string, mixed>> Related records.
	 */
	function jpkcom_acf_references_normalise_related( mixed $value ): array {

		if ( $value instanceof WP_Post || is_scalar( value: $value ) ) {

			$value = [ $value ];

		}

		if ( ! is_array( value: $value ) ) {

			return [];

		}

		$out = [];

		foreach ( $value as $item ) {

			if ( $item instanceof WP_Post ) {

				$post = $item;

			} else {

				$id = absint( $item );

				if ( $id < 1 ) {

					continue;

				}

				$post = get_post( $id );

			}

			if ( ! $post instanceof WP_Post || $post->post_status !== 'publish' || $post->post_password !== '' ) {

				continue;

			}

			$out[] = [
				'id'    => (int) $post->ID,
				'title' => (string) get_the_title( $post ),
			];

		}

		return $out;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_attachment_url' ) ) {

	/**
	 * Resolve an attachment field to a URL, or null.
	 *
	 * Accepts the three shapes ACF returns depending on return_format — id, url
	 * string, or array — and refuses anything that is not an attachment. The
	 * post-type check matters: an id pointing at a normal post would otherwise
	 * produce a permalink where the caller expects an image.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Stored value.
	 * @return string|null Attachment URL, or null.
	 */
	function jpkcom_acf_references_attachment_url( mixed $value ): ?string {

		if ( is_array( value: $value ) ) {

			$value = $value['ID'] ?? ( $value['id'] ?? ( $value['url'] ?? null ) );

		}

		if ( is_string( value: $value ) && str_starts_with( haystack: $value, needle: 'http' ) ) {

			return esc_url_raw( url: $value );

		}

		$id = absint( $value );

		if ( $id < 1 || get_post_type( $id ) !== 'attachment' ) {

			return null;

		}

		$url = wp_get_attachment_url( attachment_id: $id );

		return is_string( value: $url ) && $url !== '' ? $url : null;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_normalise_gallery' ) ) {

	/**
	 * Project a gallery field into url/alt records.
	 *
	 * Emits only what a caller can use and nothing that identifies the attachment
	 * post itself. The alt text is read from the attachment meta rather than from
	 * ACF's formatted array, so this does not depend on the field's return_format.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $value Stored value.
	 * @return array<int, array<string, mixed>> Gallery images.
	 */
	function jpkcom_acf_references_normalise_gallery( mixed $value ): array {

		if ( ! is_array( value: $value ) ) {

			return [];

		}

		$out = [];

		foreach ( $value as $item ) {

			if ( is_array( value: $item ) ) {

				$item = $item['ID'] ?? ( $item['id'] ?? null );

			}

			$id = absint( $item );

			if ( $id < 1 || get_post_type( $id ) !== 'attachment' ) {

				continue;

			}

			$url = wp_get_attachment_url( attachment_id: $id );

			if ( ! is_string( value: $url ) || $url === '' ) {

				continue;

			}

			$out[] = [
				'url' => $url,
				'alt' => jpkcom_acf_references_plain_text(
					get_post_meta( post_id: $id, key: '_wp_attachment_image_alt', single: true )
				),
			];

		}

		return $out;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_normalise_terms' ) ) {

	/**
	 * Read a reference's terms in one taxonomy as slug/name records.
	 *
	 * Reads the term relationships rather than the ACF meta, for the same reason
	 * the list shortcode does: the relationships are indexed, and the two stores
	 * can drift (import, direct DB write, a WPML duplication that never ran ACF's
	 * save routine). tools/check-term-sync.php is what detects that drift.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $post_id  Reference ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return array<int, array<string, string>> Assigned terms.
	 */
	function jpkcom_acf_references_normalise_terms( int $post_id, string $taxonomy ): array {

		if ( $post_id < 1 || ! taxonomy_exists( taxonomy: $taxonomy ) ) {

			return [];

		}

		$terms = get_the_terms( $post_id, $taxonomy );

		if ( ! is_array( value: $terms ) ) {

			return [];

		}

		$out = [];

		foreach ( $terms as $term ) {

			// instanceof, not a cast: get_the_terms() can return WP_Error entries
			// and a Throwable out of an ability callback is an error the client
			// cannot act on.
			if ( ! $term instanceof WP_Term ) {

				continue;

			}

			$out[] = [
				'slug' => (string) $term->slug,
				'name' => (string) $term->name,
			];

		}

		return $out;

	}

}


if ( ! function_exists( function: 'jpkcom_acf_references_get_reference_data' ) ) {

	/**
	 * Project one reference into plain data, or [] when it cannot be read.
	 *
	 * This function gates, because nothing else does. The existing readers are
	 * safe only because the shortcode hands them posts a post_type/post_status
	 * query already filtered; this one takes a bare int and inherits none of that,
	 * and the abilities above it answer to any subscriber. It therefore refuses a
	 * non-reference post type, any status but publish, a non-empty post_password
	 * and a non-positive id.
	 *
	 * "Does not exist" and "cannot be read" return the same empty array on
	 * purpose, so the ability above cannot be used to probe which IDs exist.
	 *
	 * `$full` adds the detail block. It is emitted ONLY for a reference whose
	 * detail page would actually render for an anonymous visitor: an external
	 * reference URL 307s every visitor away (redirects.php:59-77) and an expired
	 * reference 307s to the archive (redirects.php:149-206). For those the
	 * address, the gallery and the description have no public render path at all,
	 * so publishing them here would publish what the site never showed.
	 *
	 * @since 1.2.0
	 *
	 * @param int  $post_id Reference ID.
	 * @param bool $full    Whether to include the detail block.
	 * @return array<string, mixed> Reference data, or [] when unreadable.
	 */
	function jpkcom_acf_references_get_reference_data( int $post_id, bool $full = false ): array {

		if ( $post_id < 1 || ! function_exists( function: 'get_field' ) ) {

			return [];

		}

		$post = get_post( $post_id );

		if ( ! $post instanceof WP_Post ) {

			return [];

		}

		if ( $post->post_type !== 'reference' || $post->post_status !== 'publish' || $post->post_password !== '' ) {

			return [];

		}

		$today = current_time( type: 'Y-m-d' );

		// Read formatted, so this mirrors includes/redirects.php, which decides its
		// 307 from exactly this shape. ACF returns '' or false for an unset link
		// field, never null, so ?? does not catch it.
		$reference_url = get_field( 'reference_url', $post_id, true );

		// ! empty(), byte for byte the test redirects.php:61 applies, and NOT a
		// trim(): "   " is not empty, so the site really does 307 a visitor to it.
		$external_url = '';
		$redirects    = false;

		if ( is_array( value: $reference_url ) && ! empty( $reference_url['url'] ) ) {

			$redirects    = true;
			$external_url = esc_url_raw( url: (string) $reference_url['url'] );

		}

		$expiry_date = jpkcom_acf_references_normalise_date( get_field( 'reference_expiry_date', $post_id, true ) );
		$raw_expiry  = get_field( 'reference_expiry_date', $post_id, true );

		// A non-empty raw expiry that does not normalise withholds the detail
		// block. The sibling plugin failed open here once: an unreadable stored
		// date made the normaliser return null and the predicate answer "renders".
		$expiry_unreadable = $expiry_date === null && is_string( value: $raw_expiry ) && trim( string: $raw_expiry ) !== '';
		$is_expired        = $expiry_date !== null && $expiry_date < $today;

		$data = [
			'id'                => (int) $post->ID,
			'title'             => (string) get_the_title( $post ),
			'url'               => $redirects ? $external_url : (string) get_permalink( $post ),
			'redirects'         => $redirects,
			'date'              => (string) get_post_time( 'c', true, $post ),
			'year'              => jpkcom_acf_references_plain_text( get_field( 'reference_year', $post_id, false ) ),
			'expired'           => $is_expired,
			'customers'         => jpkcom_acf_references_normalise_related( get_field( 'reference_customer', $post_id, false ) ),
			'locations'         => jpkcom_acf_references_normalise_related( get_field( 'reference_location', $post_id, false ) ),
			'types'             => jpkcom_acf_references_normalise_terms( $post_id, 'reference-type' ),
			'filter_1'          => jpkcom_acf_references_normalise_terms( $post_id, 'reference-filter-1' ),
			'filter_2'          => jpkcom_acf_references_normalise_terms( $post_id, 'reference-filter-2' ),
		];

		if ( ! $full ) {

			return $data;

		}

		if ( $redirects ) {

			$data['detail_omitted'] = 'redirects_externally';

			return $data;

		}

		if ( $is_expired || $expiry_unreadable ) {

			$data['detail_omitted'] = 'expired';

			return $data;

		}

		// The third argument is false for the long-form field, and that is the
		// load-bearing rule of this file. get_field()'s default formatted mode
		// pipes a wysiwyg value through acf_the_content, which carries
		// do_shortcode at priority 11 and WP_Embed::autoembed at 8. With no post
		// context — which is exactly an ability callback — autoembed fetches the
		// remote URL and wp_insert_post()s an oembed_cache row, so a declared
		// read-only ability would write to the database and make outbound HTTP
		// requests. A bare URL on one line of a description is enough.
		$data['detail'] = [
			'short_description' => jpkcom_acf_references_plain_text( get_field( 'reference_short_description', $post_id, false ) ),
			'gallery'           => jpkcom_acf_references_normalise_gallery( get_field( 'reference_image_gallery', $post_id, false ) ),
		];

		return $data;

	}

}

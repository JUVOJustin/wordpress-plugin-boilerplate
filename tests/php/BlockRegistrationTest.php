<?php
/**
 * Ensures every block shipped in the build output is actually loaded by WordPress.
 *
 * Blocks are registered automatically from `build/blocks-manifest.php` via
 * Demo_Plugin::register_blocks() on the `init` hook. This generic guard discovers
 * every block in `build/Blocks` and asserts that:
 *   1. WordPress reports it through the editor-facing REST API (/wp/v2/block-types), and
 *   2. each asset file referenced in its block.json exists in the build output.
 *
 * It needs no edits when blocks are added or renamed, and is skipped when no blocks
 * are present (the fresh boilerplate state).
 *
 * @package Demo_Plugin
 */

/**
 * Verifies the automatic block registration pipeline end-to-end.
 */
class BlockRegistrationTest extends WP_UnitTestCase {

	/**
	 * Block.json fields that may reference script/style assets.
	 *
	 * @var string[]
	 */
	private const ASSET_FIELDS = array(
		'editorScript',
		'script',
		'viewScript',
		'editorStyle',
		'style',
		'viewStyle',
	);

	/**
	 * Every built block must be reported as registered by the REST API.
	 */
	public function test_built_blocks_are_registered(): void {
		$blocks = $this->discover_blocks();

		// The /wp/v2/block-types endpoint needs a running REST server and the
		// `edit_posts` capability, so set both up just for this test.
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();
		do_action( 'rest_api_init' );

		$response = rest_do_request( new \WP_REST_Request( 'GET', '/wp/v2/block-types' ) );
		$this->assertSame( 200, $response->get_status(), 'The /wp/v2/block-types endpoint should respond with HTTP 200.' );

		$registered = wp_list_pluck( $response->get_data(), 'name' );

		foreach ( $blocks as $block ) {
			$this->assertContains(
				$block['name'],
				$registered,
				sprintf(
					'Block "%s" exists in build/Blocks but the REST API does not report it as registered. Did `init`/register_blocks() wire it up? Try running `npm run build`.',
					$block['name']
				)
			);
		}
	}

	/**
	 * Every asset declared by a built block must exist on disk so it cannot 404.
	 *
	 * Missing assets are collected and asserted once so the test always makes an
	 * assertion (never "risky"), even for blocks that declare no file-based assets.
	 */
	public function test_built_block_assets_exist(): void {
		$missing = array();

		foreach ( $this->discover_blocks() as $block ) {
			foreach ( self::ASSET_FIELDS as $field ) {
				if ( ! isset( $block['metadata'][ $field ] ) ) {
					continue;
				}

				foreach ( (array) $block['metadata'][ $field ] as $ref ) {
					if ( ! is_string( $ref ) || ! str_starts_with( $ref, 'file:' ) ) {
						continue;
					}

					$path = $block['dir'] . '/' . ltrim( substr( $ref, strlen( 'file:' ) ), './' );
					if ( ! file_exists( $path ) ) {
						$missing[] = sprintf( '%s declares %s "%s"', $block['name'], $field, $ref );
					}
				}
			}
		}

		$this->assertSame(
			array(),
			$missing,
			"Some built blocks declare assets missing from the build output — they will 404 when enqueued. Run `npm run build`.\n" . implode( "\n", $missing )
		);
	}

	/**
	 * Discover every block in the build output, skipping the test when none exist.
	 *
	 * @return array<int, array{name: string, dir: string, metadata: array<string, mixed>}>
	 */
	private function discover_blocks(): array {
		$blocks_dir = DEMO_PLUGIN_PATH . 'build/Blocks';

		if ( ! is_dir( $blocks_dir ) ) {
			$this->markTestSkipped( 'No build/Blocks directory found. Add a block with `npm run create-block` and run `npm run build`.' );
		}

		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $blocks_dir, FilesystemIterator::SKIP_DOTS ) );

		$blocks = array();
		foreach ( $iterator as $file ) {
			if ( 'block.json' !== $file->getFilename() ) {
				continue;
			}

			$metadata = json_decode( (string) file_get_contents( $file->getPathname() ), true );

			$this->assertIsArray(
				$metadata,
				sprintf( 'block.json at "%s" is not valid JSON.', $file->getPathname() )
			);
			$this->assertNotEmpty(
				$metadata['name'] ?? null,
				sprintf( 'block.json at "%s" is missing a "name". WordPress cannot register an unnamed block.', $file->getPathname() )
			);

			$blocks[] = array(
				'name'     => $metadata['name'],
				'dir'      => $file->getPath(),
				'metadata' => $metadata,
			);
		}

		if ( empty( $blocks ) ) {
			$this->markTestSkipped( 'No block.json files found in build/Blocks. Run `npm run build` after adding a block.' );
		}

		return $blocks;
	}
}

<?php

namespace ElementorPro\Modules\CustomCode\ImportExportCustomization;

use Elementor\App\Modules\ImportExportCustomization\Runners\Export\Export_Runner_Base;
use ElementorPro\Modules\CustomCode\Module as Custom_Code_Module;
use ElementorPro\Modules\CustomCode\Custom_Code_Metabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Export_Runner extends Export_Runner_Base {
	public static function get_name(): string {
		return 'custom-code';
	}

	public function should_export( array $data ) {
		return (
			isset( $data['include'] ) &&
			in_array( 'settings', $data['include'], true )
		);
	}

	public function export( array $data ) {
		$code_snippets = $this->get_custom_code_snippets();
		$include_custom_code = $data['customization']['settings']['customCode'] ?? true;

		if ( empty( $code_snippets ) || ! $include_custom_code ) {
			return [
				'manifest' => [],
				'files' => [],
			];
		}

		$snippets_data = [];
		$manifest = [];

		foreach ( $code_snippets as $snippet ) {
			$data = $this->prepare_snippet_data( $snippet );
			$snippets_data[] = $data;
			$manifest[ $snippet['custom-code'] ][ $snippet->ID ] = $data;
		}

		return [
			'files' => [
				'path' => Import_Export_Customization::FILE_NAME,
				'data' => $snippets_data,
			],
			'manifest' => [
				$manifest,
			],
		];
	}

	private function get_custom_code_snippets() {
		return get_posts( [
			'post_type' => Custom_Code_Module::CPT,
			'posts_per_page' => -1,
			'post_status' => 'publish',
		] );
	}

	private function prepare_snippet_data( $snippet ) {
		$location = get_post_meta( $snippet->ID, '_elementor_' . Custom_Code_Metabox::FIELD_LOCATION, true );
		$priority = get_post_meta( $snippet->ID, '_elementor_' . Custom_Code_Metabox::FIELD_PRIORITY, true );
		$conditions = get_post_meta( $snippet->ID, '_elementor_conditions', true );

		return [
			'ID' => $snippet->ID,
			'post_title' => $snippet->post_title,
			'post_content' => $snippet->post_content,
			'post_status' => $snippet->post_status,
			'location' => $location,
			'priority' => $priority,
			'conditions' => $conditions,
		];
	}
}

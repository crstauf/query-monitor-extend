<?php declare(strict_types=1);

defined( 'WPINC' ) || die();

/**
 * Reusable sub-panel outputter that renders a collector's
 * concerned actions/filters as a "Hooks in Use" table.
 *
 * Mirrors the QM 3.x `output_concerns()` output so React's
 * PhpPanelFallback picks up a matching `qm-{id}-concerned_hooks-container`
 * div when the auto-added child menu is clicked.
 */
class QMX_Output_Html_Concerned_Hooks extends QM_Output_Html {

	public function name() {
		return __( 'Hooks in Use', 'query-monitor-extend' );
	}

	public function output() : void {
		$concerns = array(
			'concerned_actions' => __( 'Action', 'query-monitor-extend' ),
			'concerned_filters' => __( 'Filter', 'query-monitor-extend' ),
		);

		if (
			empty( $this->collector->concerned_actions )
			&& empty( $this->collector->concerned_filters )
		) {
			$this->before_non_tabular_output( $this->collector->id() . '-concerned_hooks', $this->name() );
			echo '<div class="qm-notice"><p>' . esc_html__( 'No concerned hooks recorded.', 'query-monitor-extend' ) . '</p></div>';
			$this->after_non_tabular_output();
			return;
		}

		$id = $this->collector->id() . '-concerned_hooks';

		printf(
			'<div class="qm qm-concerns" id="%1$s" role="tabpanel" aria-labelledby="%1$s-caption" tabindex="-1">',
			esc_attr( $id )
		);

		echo '<table>';

		printf(
			'<caption><h2 id="%1$s-caption">%2$s</h2></caption>',
			esc_attr( $id ),
			esc_html( $this->name() )
		);

		echo '<thead><tr>';
		echo '<th scope="col">' . esc_html__( 'Hook', 'query-monitor-extend' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Type', 'query-monitor-extend' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Priority', 'query-monitor-extend' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Callback', 'query-monitor-extend' ) . '</th>';
		echo '<th scope="col">' . esc_html__( 'Component', 'query-monitor-extend' ) . '</th>';
		echo '</tr></thead>';

		echo '<tbody>';

		foreach ( array_keys( $concerns ) as $key ) {
			if ( empty( $this->collector->$key ) ) {
				continue;
			}

			# QM 4.x `QM_Hook::process()` no longer populates the `parts`
			# array that `QM_Output_Html_Hooks::output_hook_table()` reads
			# for its `data-qm-name` attribute, and closure callbacks leave
			# `$cb->name` null which fatals in `output_filename()`'s
			# `strpos()`. Backfill both with the minimum the renderer needs.

			/**
			 * @var array<int, array{
			 *     name: string,
			 *     type: string,
			 *     parts: array<string>,
			 *     components: array<string, QM_Component>,
			 *     actions: list<array{
			 *         priority: int,
			 *         callback: QM_Data_Callback
			 *     }>
			 * }>
			 */
			$hooks = array_map( static function ( array $hook ) : array {
				$hook['parts'] ??= array();

				foreach ( $hook['actions'] ?? array() as $action ) {
					$cb = $action['callback'] ?? null;

					if ( ! is_object( $cb ) ) {
						continue;
					}

					/** @var QM_Data_Callback $cb */
					if ( null === $cb->name && 'closure' === $cb->callback_type ) {
						// `{closure}` triggers output_filename's built-in closure formatter.
						$cb->name = '{closure}';
					}
				}

				return $hook;
			}, $this->collector->$key );

			QM_Output_Html_Hooks::output_hook_table( $hooks, true );
		}

		echo '</tbody></table></div>';
	}
}

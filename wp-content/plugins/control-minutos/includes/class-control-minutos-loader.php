<?php
/**
 * Collects WordPress hooks for the plugin and registers them at runtime.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lightweight loader patterned after the WordPress Plugin Boilerplate implementation.
 */
class Control_Minutos_Loader {

    /**
     * Registered actions.
     *
     * @var array<int,array{hook:string,component:object,callback:string,priority:int,accepted_args:int}>
     */
    protected $actions = array();

    /**
     * Registered filters.
     *
     * @var array<int,array{hook:string,component:object,callback:string,priority:int,accepted_args:int}>
     */
    protected $filters = array();

    /**
     * Register an action with WordPress.
     *
     * @param string $hook          WordPress hook.
     * @param object $component     Component instance that contains the callback.
     * @param string $callback      Method name to execute.
     * @param int    $priority      Priority for the hook.
     * @param int    $accepted_args Accepted arguments for the callback.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions[] = $this->build_hook( $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Register a filter with WordPress.
     *
     * @param string $hook          WordPress filter.
     * @param object $component     Component instance that contains the callback.
     * @param string $callback      Method name to execute.
     * @param int    $priority      Priority for the hook.
     * @param int    $accepted_args Accepted arguments for the callback.
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters[] = $this->build_hook( $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Attach all of the stored actions and filters to WordPress.
     */
    public function run() {
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }

        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
        }
    }

    /**
     * Shape the hook arguments for storage.
     *
     * @param string $hook          Hook name.
     * @param object $component     Instance with the callback.
     * @param string $callback      Method name.
     * @param int    $priority      Hook priority.
     * @param int    $accepted_args Accepted args.
     *
     * @return array{hook:string,component:object,callback:string,priority:int,accepted_args:int}
     */
    protected function build_hook( $hook, $component, $callback, $priority, $accepted_args ) {
        return array(
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => (int) $priority,
            'accepted_args' => (int) $accepted_args,
        );
    }
}

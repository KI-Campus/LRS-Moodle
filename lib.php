<?php
function local_openlrs_extend_navigation(global_navigation $navigation) {
    if (has_capability('local/openlrs:view', context_system::instance())) {
        $node = navigation_node::create(
            'OpenLRS',
            new moodle_url('/local/openlrs/view.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'openlrs'
        );
        $navigation->add_node($node);
    }
}

?>
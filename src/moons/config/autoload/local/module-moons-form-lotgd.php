<?php

return [
    'forms' => [
        'Lotgd\Local\Form\Module\MoonsSettings' => include 'data/form/local/module/moons/settings_input.php',
    ],
    'input_filter_specs' => [
        'Lotgd\Local\Form\Filter\Module\MoonsSettings' => include 'data/form/local/module/moons/settings_filter.php',
    ],
];

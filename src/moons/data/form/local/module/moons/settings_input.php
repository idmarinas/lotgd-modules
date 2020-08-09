<?php

return [
    'attributes' => [
        'method' => 'post', //-- Core overwrite this.
        'action' => '', //-- Core overwrite this.
        'name' => 'settings-moons',
        'id' => 'settings-moons'
    ],
    'options' => [
        'label' => 'form.label',
        'translator_text_domain' => 'form-local-module-moons', // This is necesary for translate all labels of form
        'use_csrf_security' => true,
        'buttons' => [
            'submit' => true, // true|false - Default value is true
            'reset' => false, // true|false - Default value is false
        ],
    ],
    'fieldsets' => [
        [
            // First Moon
            'spec' => [
                'name' => 'moon1',
                'attributes' => [
                    'id' => 'moon1'
                ],
                'options' => [
                    'label' => 'moon1.title'
                ],
                'elements' => [
                    //-- Is the second moon active?
                    [
                        'spec' => [
                            'type' => 'checkbox',
                            'name' => 'moon1',
                            'attributes' => [
                                'id' => 'moon1',
                                'class' => 'lotgd toggle',
                                'value' => 1
                            ],
                            'options' => [
                                'label' => 'moon1.moon1'
                            ]
                        ]
                    ],
                    //-- Days in the second moons lunar cycle
                    [
                        'spec' => [
                            'type' => 'range',
                            'name' => 'moon1cycle',
                            'attributes' => [
                                'id' => 'moon1cycle',
                                'min' => 10,
                                'max' => 60,
                                'value' => 23
                            ],
                            'options' => [
                                'label' => 'moon1.moon1cycle'
                            ]
                        ]
                    ],
                    //-- Place in cycle?
                    [
                        'spec' => [
                            'type' => 'range',
                            'name' => 'moon1place',
                            'attributes' => [
                                'id' => 'moon1place',
                                'min' => 1,
                                'max' => 60,
                                'value' => 1
                            ],
                            'options' => [
                                'label' => 'moon1.moon1place'
                            ]
                        ]
                    ],
                ]
            ],
        ],
        [
            // Second Moon
            'spec' => [
                'name' => 'moon2',
                'attributes' => [
                    'id' => 'moon2'
                ],
                'options' => [
                    'label' => 'moon2.title'
                ],
                'elements' => [
                    //-- Is the second moon active?
                    [
                        'spec' => [
                            'type' => 'checkbox',
                            'name' => 'moon2',
                            'attributes' => [
                                'id' => 'moon2',
                                'class' => 'lotgd toggle',
                                'value' => 0
                            ],
                            'options' => [
                                'label' => 'moon2.moon2'
                            ]
                        ]
                    ],
                    //-- Days in the second moons lunar cycle
                    [
                        'spec' => [
                            'type' => 'range',
                            'name' => 'moon2cycle',
                            'attributes' => [
                                'id' => 'moon2cycle',
                                'min' => 10,
                                'max' => 60,
                                'value' => 43
                            ],
                            'options' => [
                                'label' => 'moon2.moon2cycle'
                            ]
                        ]
                    ],
                    //-- Place in cycle?
                    [
                        'spec' => [
                            'type' => 'range',
                            'name' => 'moon2place',
                            'attributes' => [
                                'id' => 'moon2place',
                                'min' => 1,
                                'max' => 60,
                                'value' => 1
                            ],
                            'options' => [
                                'label' => 'moon2.moon2place'
                            ]
                        ]
                    ],
                ]
            ],
        ],
        [
            // Third Moon
            'spec' => [
                'name' => 'moon3',
                'attributes' => [
                    'id' => 'moon3'
                ],
                'options' => [
                    'label' => 'moon3.title'
                ],
                'elements' => [
                    //-- Is the second moon active?
                    [
                        'spec' => [
                            'type' => 'checkbox',
                            'name' => 'moon3',
                            'attributes' => [
                                'id' => 'moon3',
                                'class' => 'lotgd toggle',
                                'value' => 0
                            ],
                            'options' => [
                                'label' => 'moon3.moon3'
                            ]
                        ]
                    ],
                    //-- Days in the second moons lunar cycle
                    [
                        'spec' => [
                            'type' => 'range',
                            'name' => 'moon3cycle',
                            'attributes' => [
                                'id' => 'moon3cycle',
                                'min' => 10,
                                'max' => 60,
                                'value' => 37
                            ],
                            'options' => [
                                'label' => 'moon3.moon3cycle'
                            ]
                        ]
                    ],
                    //-- Place in cycle?
                    [
                        'spec' => [
                            'type' => 'range',
                            'name' => 'moon3place',
                            'attributes' => [
                                'id' => 'moon3place',
                                'min' => 1,
                                'max' => 60,
                                'value' => 1
                            ],
                            'options' => [
                                'label' => 'moon3.moon3place'
                            ]
                        ]
                    ],
                ]
            ],
        ],
    ],
    // 'input_filter' => 'Lotgd\Local\Form\Filter\Module\MoonsSettings'
];

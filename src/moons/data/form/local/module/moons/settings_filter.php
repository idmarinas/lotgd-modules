<?php

use Laminas\Validator;

return [
    'moon1' =>  [
        'type' => \Laminas\InputFilter\InputFilter::class,
        [
            'name' => 'moon1',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
        [
            'name' => 'moon1cycle',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
        [
            'name' => 'moon1place',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
    ],
    'moon2' =>  [
        'type' => \Laminas\InputFilter\InputFilter::class,
        [
            'name' => 'moon2',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
        [
            'name' => 'moon2cycle',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
        [
            'name' => 'moon2place',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
    ],
    'moon3' =>  [
        'type' => \Laminas\InputFilter\InputFilter::class,
        [
            'name' => 'moon3',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
        [
            'name' => 'moon3cycle',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
        [
            'name' => 'moon3place',
            'required' => false,
            'filters' => [],
            'validators' => [
                ['name' => Validator\NotEmpty::class],
                ['name' => Validator\Digits::class]
            ]
        ],
    ],
];

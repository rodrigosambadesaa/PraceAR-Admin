<?php

return [
    'nave_endpoints' => [
        'get_info_nave_1' => [
            'conditions' => [
                ['between' => ['NC038', 'NC057']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_1b' => [
            'conditions' => [
                ['between' => ['NC058', 'NC077']],
            ],
            'order' => 'desc',
        ],
        'get_info_nave_2' => [
            'conditions' => [
                ['between' => ['NC120', 'NC135']],
                ['equals' => 'MC001'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_2b' => [
            'conditions' => [
                ['between' => ['NC136', 'NC151']],
                ['equals' => 'MC002'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_3' => [
            'conditions' => [
                ['between' => ['NC186', 'NC201']],
                ['equals' => 'MC002'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_3b' => [
            'conditions' => [
                ['between' => ['NC202', 'NC217']],
                ['equals' => 'MC003'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_4' => [
            'conditions' => [
                ['between' => ['NC252', 'NC271']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_4b' => [
            'conditions' => [
                ['between' => ['NC272', 'NC291']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_5' => [
            'conditions' => [
                ['between' => ['NC078', 'NC098']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_5b' => [
            'conditions' => [
                ['between' => ['NC099', 'NC119']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_6' => [
            'conditions' => [
                ['between' => ['NC152', 'NC168']],
                ['equals' => 'MC006'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_6b' => [
            'conditions' => [
                ['between' => ['NC169', 'NC185']],
                ['equals' => 'MC005'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_7' => [
            'conditions' => [
                ['between' => ['NC218', 'NC234']],
                ['equals' => 'MC005'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_7b' => [
            'conditions' => [
                ['between' => ['NC235', 'NC251']],
                ['equals' => 'MC004'],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_8' => [
            'conditions' => [
                ['between' => ['NC292', 'NC312']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_8b' => [
            'conditions' => [
                ['between' => ['NC313', 'NC333']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_9' => [
            'conditions' => [
                ['between' => ['CE001', 'CE018']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_10' => [
            'conditions' => [
                ['between' => ['CE019', 'CE037']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_11' => [
            'conditions' => [
                ['between' => ['NA334', 'NA351']],
            ],
            'order' => 'asc',
        ],
        'get_info_nave_12' => [
            'conditions' => [
                ['between' => ['NA352', 'NA370']],
            ],
            'order' => 'asc',
        ],
    ],
];

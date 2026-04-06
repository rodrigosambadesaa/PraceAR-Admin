<?php

return [
    'project_root' => dirname(__DIR__, 2),
    'results_per_page' => 50,
    'languages' => [
        'es' => 'Español',
        'en' => 'Inglés',
        'gl' => 'Gallego',
        'fr' => 'Francés',
    ],
    'sections' => [
        'ameas' => [
            ['range' => ['CE001', 'CE018'], 'title' => 'Amea1', 'indice' => 1],
            ['range' => ['CE019', 'CE037'], 'title' => 'Amea2', 'indice' => 2],
        ],
        'naves' => [
            ['range' => ['NC038', 'NC077'], 'title' => 'Nave1', 'indice' => 1],
            ['range' => ['NC120', 'NC151', 'MC001', 'MC002'], 'title' => 'Nave2', 'indice' => 2],
            ['range' => ['NC186', 'NC217', 'MC002', 'MC003'], 'title' => 'Nave3', 'indice' => 3],
            ['range' => ['NC252', 'NC291'], 'title' => 'Nave4', 'indice' => 4],
            ['range' => ['NC078', 'NC119'], 'title' => 'Nave5', 'indice' => 5],
            ['range' => ['NC152', 'NC185', 'MC005', 'MC006'], 'title' => 'Nave6', 'indice' => 6],
            ['range' => ['NC218', 'NC251', 'MC004', 'MC005'], 'title' => 'Nave7', 'indice' => 7],
            ['range' => ['NC292', 'NC333'], 'title' => 'Nave8', 'indice' => 8],
        ],
        'murallones' => [
            ['range' => ['NA334', 'NA351'], 'title' => 'Murallón1', 'indice' => 1],
            ['range' => ['NA352', 'NA370'], 'title' => 'Murallón2', 'indice' => 2],
        ],
    ],
    'unity_types' => [
        'default' => 'Por defecto: default',
        'handicraft' => 'Artesanía: handicraft',
        'butcher' => 'Carnicería: butcher',
        'delicatessen' => 'Chacinería: delicatessen',
        'fish-seafood' => 'Peixe e marisco: fish-seafood',
        'cod-frozen' => 'Bacallau e conxelados: cod-frozen',
        'poultry-eggs' => 'Aves e ovos: poultry-eggs',
        'vegetables-organics' => 'Froitas e verduras e ecolóxicos: vegetables-organics',
        'bread-sweets' => 'Pan e doces: bread-sweets',
        'flowers' => 'Flores: flowers',
        'restaurants' => 'Restauración e produtos elaborados: retaurants',
    ],
];

<?php

return [
    'branches' => [
        1 => [
            'id' => 1,
            'name' => 'Labheswar Branch 1',
            'code' => 'LB1',
            'connection' => 'labheswar_branch_1',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'active',
        ],
        2 => [
            'id' => 2,
            'name' => 'Labheswar Branch 2',
            'code' => 'LB2',
            'connection' => 'labheswar_branch_2',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'active',
        ],
    ],

    'default_branch' => 1,
    
    // Future branches (inactive for now)
    'future_branches' => [
        3 => [
            'id' => 3,
            'name' => 'Labheswar Branch 3',
            'code' => 'LB3',
            'connection' => 'labheswar_branch_3',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'planned',
        ],
        4 => [
            'id' => 4,
            'name' => 'Labheswar Branch 4',
            'code' => 'LB4',
            'connection' => 'labheswar_branch_4',
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
            'status' => 'planned',
        ],
    ],
];
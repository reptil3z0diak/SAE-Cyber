<?php
// includes/db_mock.php

// Ce fichier remplace temporairement ta table SQL 'cars'
$cars_db = [
    1 => [
        'id' => 1,
        'brand' => 'Peugeot',
        'model' => '208',
        'price' => 12000,
        'year' => 2020,
        'image' => 'https://via.placeholder.com/600x400?text=Peugeot+208',
        'description' => 'Une citadine polyvalente et économique. Parfait état, contrôle technique OK.',
        'features' => ['Climatisation', 'Bluetooth', 'Régulateur de vitesse']
    ],
    2 => [
        'id' => 2,
        'brand' => 'BMW',
        'model' => 'Série 3',
        'price' => 25000,
        'year' => 2018,
        'image' => 'https://via.placeholder.com/600x400?text=BMW+Serie+3',
        'description' => 'Berline sportive, toutes options. Carnet d\'entretien à jour.',
        'features' => ['Cuir', 'GPS', 'Toit ouvrant', 'Radars de recul']
    ],
    3 => [
        'id' => 3,
        'brand' => 'Renault',
        'model' => 'Clio 2',
        'price' => 1500,
        'year' => 2005,
        'image' => 'https://via.placeholder.com/600x400?text=Renault+Clio',
        'description' => 'Idéal jeune conducteur. Quelques rayures sur le pare-choc arrière.',
        'features' => ['Radio CD', 'Vitres électriques avant']
    ],
    4 => [
        'id' => 4,
        'brand' => 'Porsche',
        'model' => 'Cayenne',
        'price' => 45000,
        'year' => 2015,
        'image' => 'https://via.placeholder.com/600x400?text=Porsche+Cayenne',
        'description' => 'SUV de luxe. Moteur V8 puissant. Intérieur impeccable.',
        'features' => ['4x4', 'Sièges chauffants', 'Caméra 360', 'Son Bose']
    ]
];


// ... suite du fichier db_mock.php ...

// Simulation de la table 'forum_posts'
$messages_db = [
    [
        'author' => 'Jean-Michel',
        'date' => '2023-10-12 14:30',
        'content' => 'Bonjour, je cherche une voiture pas chère, que pensez-vous de la Clio ?'
    ],
    [
        'author' => 'Admin Supreme',
        'date' => '2023-10-12 15:00',
        'content' => 'La Clio est un excellent choix pour commencer. Nous en avons une en stock.'
    ],
    [
        'author' => 'HackerDu93',
        'date' => '2023-10-13 09:00',
        'content' => 'Moi je préfère les trottinettes...'
    ]
];

// ... suite du fichier db_mock.php ...

// Simulation de la table 'users'
$users_db = [
    1 => [
        'id' => 1,
        'username' => 'admin',
        'email' => 'admin@car-pentest.com',
        'phone' => '06 00 00 00 01', // Donnée sensible (IDOR)
        'role' => 'admin',
        'avatar' => 'default_admin.png'
    ],
    2 => [
        'id' => 2,
        'username' => 'toto',
        'email' => 'toto@gmail.com',
        'phone' => '06 99 88 77 66', // Donnée sensible (IDOR)
        'role' => 'user',
        'avatar' => 'default_user.png'
    ],
    3 => [
        'id' => 3,
        'username' => 'alice_cooper',
        'email' => 'alice@rock.com',
        'phone' => '07 66 66 66 66',
        'role' => 'user',
        'avatar' => 'default_user.png'
    ]
];


?>
<?php

$config = [
    'default-sp' => [
        'saml:SP',
        'entityID' => 'https://www.tsrmpstrpsalerno.it/simplesaml/module.php/saml/sp/metadata.php/default-sp',
        'idp' => null, // lasciato null per permettere selezione dinamica
        'discoURL' => null,
    ],

    'spid' => [
        'saml:SP',
        'entityID' => 'https://www.tsrmpstrpsalerno.it/simplesaml/module.php/saml/sp/metadata.php/spid',
        'idp' => 'https://identityprovider.spid.gov.it', // es. idp demo o da SPID
        'privatekey' => 'spid.key',
        'certificate' => 'spid.crt',
        'sign.authnrequest' => true,
        'redirect.sign' => true,
        'redirect.validate' => true,
        'assertion.encryption' => false,
        'NameIDPolicy' => null,
    ],

    'cie' => [
        'saml:SP',
        'entityID' => 'https://www.tsrmpstrpsalerno.it/simplesaml/module.php/saml/sp/metadata.php/cie',
        'idp' => 'https://idserver.servizicie.interno.gov.it/idp',
        'privatekey' => 'cie.key',
        'certificate' => 'cie.crt',
        'sign.authnrequest' => true,
        'redirect.sign' => true,
        'redirect.validate' => true,
        'assertion.encryption' => false,
        'NameIDPolicy' => null,
    ]
];

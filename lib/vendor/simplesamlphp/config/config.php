<?php

$config = [

    'baseurlpath' => '/wp-content/plugins/wp-spid-cie-italia/simplesamlphp/www/',

    'certdir' => 'cert/',

    'logging.level' => SimpleSAML\Logger::WARNING,
    'logging.handler' => 'file',
    'loggingdir' => __DIR__ . '/../log/',

    'secretsalt' => 'sostituiscimiConUnaStringaLungaECasuale',

    'technicalcontact_name' => 'Supporto tecnico',
    'technicalcontact_email' => 'supporto@tuodominio.it',

    'timezone' => 'Europe/Rome',

    'enable.saml20-sp' => true,

    'session.duration' => 3600,
    'session.datastore.timeout' => 4 * 3600,
    'session.state.timeout' => 60 * 60,

    'theme.use' => 'default',

];

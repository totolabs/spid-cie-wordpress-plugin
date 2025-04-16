<?php
function wpsci_generate_cie_metadata_xml() {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    $entityDescriptor = $doc->createElementNS('urn:oasis:names:tc:SAML:2.0:metadata', 'EntityDescriptor');
    $entityDescriptor->setAttribute('entityID', 'https://tsrmpstrpsalerno.it');
    $doc->appendChild($entityDescriptor);

    // Puoi aggiungere altri nodi XML qui...

    // Salva il file oppure restituiscilo
    $xml_output = $doc->saveXML();

    // Salvataggio su file
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/cie-metadata.xml';
    $doc->save($file_path);

    return $file_path;
}

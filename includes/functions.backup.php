<?php

function wpsci_generate_certificates() {
    $cert_dir = plugin_dir_path(__DIR__) . 'cert/';
    $key_path = $cert_dir . 'private.key';
    $crt_path = $cert_dir . 'public.crt';

    if (!file_exists($cert_dir)) {
        mkdir($cert_dir, 0755, true);
    }

    if (file_exists($key_path)) {
        copy($key_path, $cert_dir . 'private.key.bak.' . time());
    }
    if (file_exists($crt_path)) {
        copy($crt_path, $cert_dir . 'public.crt.bak.' . time());
    }

    $config = [
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    ];

    $privkey = openssl_pkey_new($config);

    $dn = [
        "countryName" => get_option('wpsci_country_name', 'IT'),
        "stateOrProvinceName" => get_option('wpsci_state_or_province_name', ''),
        "localityName" => get_option('wpsci_locality_name', ''),
        "organizationName" => get_option('wpsci_sp_org_name', ''),
        "commonName" => parse_url(get_option('wpsci_entity_id', ''), PHP_URL_HOST),
        "emailAddress" => get_option('wpsci_email_address', ''),
    ];

    $csr = openssl_csr_new($dn, $privkey, $config);
    $x509 = openssl_csr_sign($csr, null, $privkey, 365);

    openssl_pkey_export($privkey, $privkey_out);
    openssl_x509_export($x509, $x509_out);

    file_put_contents($key_path, $privkey_out);
    file_put_contents($crt_path, $x509_out);
}

function wpsci_generate_config_files() {
    $config_dir = plugin_dir_path(__DIR__) . 'cert/';
    if (!file_exists($config_dir)) {
        mkdir($config_dir, 0755, true);
    }

    $entity_id = get_option('wpsci_entity_id', '');
    $sp_config = [
        'entity_id' => $entity_id,
        'organization' => [
            'name' => get_option('wpsci_sp_org_name', ''),
            'display_name' => get_option('wpsci_sp_org_display_name', ''),
            'url' => $entity_id,
        ],
        'contacts' => [
            [
                'contact_type' => 'technical',
                'email_address' => get_option('wpsci_sp_contact_email', ''),
                'telephone_number' => get_option('wpsci_sp_contact_phone', ''),
                'fiscal_code' => get_option('wpsci_sp_contact_fiscal_code', ''),
                'ipa_code' => get_option('wpsci_sp_contact_ipa_code', ''),
            ],
        ],
        'certificate' => file_exists($config_dir . 'public.crt') ? file_get_contents($config_dir . 'public.crt') : '',
    ];

    file_put_contents($config_dir . 'sp_metadata.json', json_encode($sp_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function wpsci_generate_certificates_on_save($old_value, $value) {
    wpsci_generate_certificates();
    wpsci_generate_config_files();
}

/**
 * Genera il file XML dei metadati per SPID.
 */
function wpsci_generate_spid_metadata_xml() {
    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;

    // Creazione elemento radice
    $entityDescriptor = $doc->createElement('EntityDescriptor');
    $entityDescriptor->setAttribute('entityID', 'https://tsrmpstrpsalerno.it'); // Modifica con il tuo EntityID
    $entityDescriptor->setAttribute('xmlns', 'urn:oasis:names:tc:SAML:2.0:metadata');
    $doc->appendChild($entityDescriptor);

    // Aggiungi AssertionConsumerService
    $acs = $doc->createElement('AssertionConsumerService');
    $acs->setAttribute('Binding', 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST');
    $acs->setAttribute('Location', 'https://tsrmpstrpsalerno.it/acs.php');
    $acs->setAttribute('index', '0');
    $entityDescriptor->appendChild($acs);

    // Aggiungi eventuali altri nodi richiesti per SPID
    // Ad esempio, un KeyDescriptor (supponendo che il certificato sia già generato)
    $config_dir = plugin_dir_path(__DIR__) . 'cert/';
    $cert_path = $config_dir . 'public.crt';
    if (file_exists($cert_path)) {
        $keyDescriptor = $doc->createElement('KeyDescriptor');
        $keyDescriptor->setAttribute('use', 'signing');
        $entityDescriptor->appendChild($keyDescriptor);

        $keyInfo = $doc->createElement('KeyInfo');
        $keyDescriptor->appendChild($keyInfo);

        $x509Data = $doc->createElement('X509Data');
        $keyInfo->appendChild($x509Data);

        $certificate = file_get_contents($cert_path);
        // Rimuovi header e footer se necessario
        $certificate = str_replace(["-----BEGIN CERTIFICATE-----", "-----END CERTIFICATE-----", "\n", "\r"], '', $certificate);

        $x509Certificate = $doc->createElement('X509Certificate', $certificate);
        $x509Data->appendChild($x509Certificate);
    }

    // Salva il file XML in una cartella dedicata, ad es. nella cartella "cert"
    $metadata_file = plugin_dir_path(__DIR__) . 'cert/spid_metadata.xml';
    $doc->save($metadata_file);

    return $metadata_file;
}

//richiamo le librerie per la firma del file xml
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

/**
 * Genera il file XML dei metadati per CIE.
 */
function wpsci_generate_complete_cie_metadata_xml() {
    $options = get_option('wpsci_options');

    $country = $options['country_name'] ?? 'IT';
    $province = $options['state_or_province_name'] ?? 'NA';
    $locality = $options['locality_name'] ?? 'Napoli';
    $org_name = $options['sp_org_name'] ?? 'Organizzazione';
    $org_display_name = $options['sp_org_display_name'] ?? 'Org Abbrev';
    $entity_id = $options['sp_entityid'] ?? 'https://example.org/';
    $email = $options['email_address'] ?? 'info@example.org';
    $ipa_code = $options['sp_contact_ipa_code'] ?? 'ipa_code';
    $fiscal_code = $options['sp_contact_fiscal_code'] ?? 'codicefiscale';
    $contact_email = $options['sp_contact_email'] ?? 'tecnico@example.org';
    $contact_phone = $options['sp_contact_phone'] ?? '0123456789';

    // Costruzione XML base (completo da rifinire con altri blocchi secondo necessità)
    $xml_content = <<<XML
 <?xml version="1.0" encoding="UTF-8"?>
  <md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
                     xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                     xmlns:cie="https://www.cartaidentita.interno.gov.it/saml-extensions"
                     entityID="$entity_id">
    <ds:Signature> [...] </ds:Signature>
    <md:SPSSODescriptor AuthnRequestsSigned="true"
         WantAssertionsSigned="true"
         protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
   <md:KeyDescriptor use="signing">
     <ds:KeyInfo>
    <ds:X509Data>
      <ds:X509Certificate> ... </ds:X509Certificate>
    </ds:X509Data>
     </ds:KeyInfo>
   </md:KeyDescriptor>
   <md:KeyDescriptor use="encryption">
    <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
     <ds:X509Data>
      <ds:X509Certificate> [...] </ds:X509Certificate>
     </ds:X509Data>
    </ds:KeyInfo>
   </md:KeyDescriptor>
   <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect"
         Location="https://url_esempio_SLO_Redirect" />
   <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST" 
         Location="url_esempio_SLO_POST"/>
   <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP" 
         Location="url_esempio_SLO_SOAP"/>
   <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
   <md:AssertionConsumerService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST"
           Location="https://tsrmpstrpsalerno.it/modulistica/cancellazione-albo/"
           index="0"
           isDefault="true" />
   <md:AttributeConsumingService index="0">
     <md:ServiceName xml:lang="">urn:uuid:a83e1df8-0dd3-46c0-b4e3-f6c650177056</md:ServiceName>
     <md:ServiceDescription xml:lang="it">Cancellazione dall'albo</md:ServiceDescription>
     <md:RequestedAttribute Name="name" />
     <md:RequestedAttribute Name="familyName" />
     <md:RequestedAttribute Name="dateOfBirth" />
     <md:RequestedAttribute Name="fiscalNumber" />
   </md:AttributeConsumingService>
    </md:SPSSODescriptor>
    <md:Organization>
   <md:OrganizationName xml:lang="it">$org_name</md:OrganizationName>
   <md:OrganizationName xml:lang="en">$org_name</md:OrganizationName>
   <md:OrganizationDisplayName xml:lang="it">$org_display_name</md:OrganizationDisplayName>
   <md:OrganizationDisplayName xml:lang="en">$org_display_name</md:OrganizationDisplayName>
   <md:OrganizationURL xml:lang="it">$entity_id</md:OrganizationURL>
   <md:OrganizationURL xml:lang="en">$entity_id</md:OrganizationURL>
    </md:Organization>
    <md:ContactPerson contactType="administrative">
     <md:Extensions>
      <cie:Public/>
      <cie:IPACode>$ipa_code</cie:IPACode>
      <cie:FiscalCode>$fiscal_code</cie:FiscalCode>
      <cie:Municipality>$locality</cie:Municipality>
      <cie:Province>$province</cie:Province>
      <cie:Country>$country</cie:Country>                 
     </md:Extensions>
     <md:Company>$org_display_name</md:Company>
     <md:EmailAddress>$email</md:EmailAddress>
     <md:TelephoneNumber>$contact_phone</md:TelephoneNumber>
    </md:ContactPerson>
    <md:ContactPerson contactType="technical">
     <md:Extensions>
      <cie:Private/>
      <cie:VATNumber>IT09526051215</cie:VATNumber>
      <cie:FiscalCode>09526051215</cie:FiscalCode>
      <cie:Municipality>063049</cie:Municipality>
      <cie:Province>NA</cie:Province>   
      <cie:Country>IT</cie:Country>
     </md:Extensions>
     <md:Company>TOTOLABS S.R.L.</md:Company>
     <md:EmailAddress>amministrazione@totolabs.it</md:EmailAddress>
     <md:TelephoneNumber>+3908119176364</md:TelephoneNumber>
    </md:ContactPerson>
  </md:EntityDescriptor>
 XML;

    // Salva in /wp-content/uploads/cert/
    $upload_dir = wp_upload_dir();
    $cert_dir = $upload_dir['basedir'] . '/cert/';
    $cert_url = $upload_dir['baseurl'] . '/cert/';
    $file_path = $cert_dir . 'cie_metadata.xml';

    if (!file_exists($cert_dir)) {
        mkdir($cert_dir, 0755, true);
    }

    file_put_contents($file_path, $xml_content);

    // Restituisci l'URL pubblico del file
    return $cert_url . 'cie_metadata.xml';
}

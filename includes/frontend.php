<?php
if (!defined('ABSPATH')) exit;

/**
 * Aggiunge alla pagina di login la sezione con le modalità di accesso:
 * - Accedi con SPID
 * - Accedi con CIE
 * - Accedi con Credenziali Amministrative
 */
function wpsci_add_login_buttons($message) {
    ob_start();
    ?>
    <div class="custom-login-options">
        <!-- Accedi con SPID -->
        <div class="login-option">
            <h2>Accedi con SPID</h2>
            <div class="login-box">
                <a href="https://tsrmpstrpsalerno.it/acs.php?provider=spid" class="spid-btn">
                    <img src="<?php echo plugins_url('img/spid_button_image.png', __FILE__); ?>" alt="Login con SPID">
                    Login con SPID
                </a>
                <p class="descr">
                    SPID è il sistema di accesso che consente di utilizzare, con un'identità digitale unica, i servizi online della Pubblica Amministrazione e dei privati accreditati.<br>
                    Non hai SPID? • <a href="https://www.agid.gov.it/it/spid-come-ottenere-identit%C3%A0-digitale" target="_blank">Maggiori info</a>
                </p>
            </div>
        </div>

        <!-- Separatore -->
        <div class="separator">oppure</div>

        <!-- Accedi con CIE -->
        <div class="login-option">
            <h2>Accedi con CIE</h2>
            <div class="login-box">
                <a href="https://tsrmpstrpsalerno.it/acs.php?provider=cie" class="cie-btn">
                    <img src="<?php echo plugins_url('img/cie_button_image.png', __FILE__); ?>" alt="Login con CIE">
                    Login con CIE
                </a>
                <p class="descr">
                    La Carta d'Identità Elettronica consente l'accesso sicuro ai servizi online della Pubblica Amministrazione.<br>
                    Non hai la CIE? • <a href="https://www.cartaidentita.interno.gov.it/info-utili/" target="_blank">Maggiori info</a>
                </p>
            </div>
        </div>

        <!-- Separatore -->
        <div class="separator">oppure</div>

        <!-- Accedi con Credenziali Amministrative -->
        <div class="login-option">
            <h2>Accedi con Credenziali Amministrative</h2>
        </div>
    </div>
    <?php
    return $message . ob_get_clean();
}
add_filter('login_message', 'wpsci_add_login_buttons');

/**
 * Aggiunge gli stili personalizzati alla pagina di login.
 */
function wpsci_enqueue_login_styles() {
    ?>
    <style>
        /* Contenitore principale */
        .custom-login-options {
            max-width: 500px;
            margin: 0 auto 20px;
            text-align: center;
        }
        .login-option {
            margin-bottom: 20px;
        }
        .login-option h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        /* Box bianco per i pulsanti e il testo, con padding aggiornato */
        .login-box {
            background: #fff;
            border: 1px solid #ccc;
            padding: 26px 24px !important;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        /* Bottoni SPID e CIE con bordi non arrotondati e padding maggiore */
        .spid-btn, .cie-btn {
            display: inline-block;
            margin: 10px auto 20px;
            padding: 6px 15px !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            border-radius: 0;
            background-color: #2271b1;
            color: white;
            border: 2px solid #2271b1;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .spid-btn:hover, .cie-btn:hover {
            background-color: #135e96;
            border-color: #135e96;
            color: white !important;
        }
        /* Immagini all'interno dei bottoni */
        .spid-btn img, .cie-btn img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            vertical-align: middle;
        }
        /* Testo descrittivo sotto i pulsanti, con più spazio */
        .descr {
            font-size: 12px;
            color: #666;
            margin-top: 30px !important; /* Aumentato il margine superiore */
            line-height: 1.4;
        }
        .descr a { 
            color: #0056b3;
            text-decoration: none;
        }
        .descr a:hover {
            text-decoration: underline;
        }
        /* Separatore fra le sezioni */
        .separator {
            font-size: 16px;
            color: #666;
            font-weight: bold;
            margin: 20px 0;
        }
        /* Allarga il box del form di login standard */
        #login {
            width: 500px !important;
            padding: 20px;
        }
    </style>
    <script>
        // Rimuove il focus automatico sul campo di login per evitare lo scroll automatico
        window.addEventListener('load', function() {
            var userField = document.getElementById('user_login');
            if(userField){
                userField.blur();
            }
        });
    </script>
    <?php
}
add_action('login_enqueue_scripts', 'wpsci_enqueue_login_styles');

<?php
// Recupera as chaves do reCAPTCHA de forma segura
function get_recaptcha_keys() {
    return [
        'site_key' => sanitize_text_field(YOUR SITE KEY),
        'secret_key' => sanitize_text_field(YOUR SECRET KEY HERE)
    ];
}

// Adiciona o script do reCAPTCHA v3 no login
function login_recaptcha_script() {
    $keys = get_recaptcha_keys();
    if (!empty($keys['site_key'])) {
        wp_register_script("recaptcha_login", "https://www.google.com/recaptcha/api.js?render=" . esc_js($keys['site_key']));
        wp_enqueue_script("recaptcha_login");
    }
}
add_action("login_enqueue_scripts", "login_recaptcha_script");

// Exibe o reCAPTCHA v3 no formulário de login
function display_login_captcha() {
    $keys = get_recaptcha_keys();
    if (!empty($keys['site_key'])) { ?>
        <script>
            grecaptcha.ready(function () {
                grecaptcha.execute('<?php echo esc_js($keys['site_key']); ?>', { action: 'login' }).then(function (token) {
                    document.getElementById('recaptchaResponse').value = token;
                });
            });
        </script>
        <input type="hidden" name="recaptcha_response" id="recaptchaResponse">
    <?php }
}
add_action("login_form", "display_login_captcha");

// Verifica o reCAPTCHA v3 durante o login
function verify_login_captcha($user, $password) {
    if (!isset($_POST['recaptcha_response']) || empty($_POST['recaptcha_response'])) {
        return new WP_Error("recaptcha_missing", __("<strong>ERROR</strong>: Verificação de segurança não realizada. Por favor, tente novamente."));
    }

    // Armazena o resultado do reCAPTCHA em um cache temporário para evitar múltiplas verificações
    $cached_result = get_transient('recaptcha_result_' . $_SERVER['REMOTE_ADDR']);
    if ($cached_result) {
        return $cached_result ? $user : new WP_Error("recaptcha_invalid", __("<strong>ERROR</strong>: Verificação de segurança falhou. Por favor, tente novamente."));
    }

    $keys = get_recaptcha_keys();
    if (empty($keys['secret_key'])) {
        return new WP_Error("recaptcha_error", __("<strong>ERROR</strong>: Chave de verificação ausente."));
    }

    $response = wp_remote_post("https://www.google.com/recaptcha/api/siteverify", array(
        'body' => array(
            'secret' => $keys['secret_key'],
            'response' => sanitize_text_field($_POST['recaptcha_response']),
            'remoteip' => sanitize_text_field($_SERVER['REMOTE_ADDR'])
        )
    ));

    if (is_wp_error($response)) {
        return new WP_Error("recaptcha_error", __("<strong>ERROR</strong>: Ocorreu um erro na verificação do reCAPTCHA. Tente novamente."));
    }

    $response_body = wp_remote_retrieve_body($response);
    $result = json_decode($response_body);

    // Verifica o resultado e armazena em cache
    if ($result && $result->success && $result->score >= 0.5) {
        set_transient('recaptcha_result_' . $_SERVER['REMOTE_ADDR'], true, 5 * MINUTE_IN_SECONDS);
        return $user; // Login permitido
    } else {
        set_transient('recaptcha_result_' . $_SERVER['REMOTE_ADDR'], false, 5 * MINUTE_IN_SECONDS);
        return new WP_Error("recaptcha_invalid", __("<strong>ERROR</strong>: Verificação de segurança falhou. Por favor, tente novamente."));
    }
}
add_filter("wp_authenticate_user", "verify_login_captcha", 10, 2);

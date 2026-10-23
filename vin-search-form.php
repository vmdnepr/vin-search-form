<?php
/*
Plugin Name: VIN Search - Carvertical
Description: Форма поиска по VIN-номеру авто для Carvertical. Партнёрскую ссылку можно менять в настройках: домен, путь и параметры. Для вставки на сайт используйте: <strong>[vin_search]</strong>.
Version: 1.4
Author: site2seo
Update URI: https://github.com/vmdnepr/vin-search-form
*/

if (!defined('ABSPATH')) {
    exit;
}

define('VIN_SEARCH_GITHUB_REPO', 'vmdnepr/vin-search-form');

function vin_search_default_a() {
    return "666d545263\xD0\xB544";
}

function vin_search_default_b() {
    return "2\xD1\x81fd8\xD0\xB539";
}

function vin_search_default_voucher() {
    return 'vincodeonlineeu';
}

function vin_search_default_url_template() {
    return 'http://www.carvertical.com/ua/precheck?a={a}&b={b}&voucher={voucher}&vin={vin}';
}

function vin_search_sanitize_param($value) {
    $value = wp_check_invalid_utf8((string) $value);
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
    return trim($value);
}

function vin_search_sanitize_url_template($value) {
    $default = vin_search_default_url_template();
    $value = vin_search_sanitize_param($value);
    $value = preg_replace('/\s+/', '', $value);
    $value = str_replace('{{vin}}', '{vin}', $value);

    if ($value === '') {
        return $default;
    }

    if (!preg_match('#^https?://#i', $value)) {
        $value = 'https://' . ltrim($value, '/');
    }

    return $value;
}

function vin_search_sanitize_lang_segment($value) {
    $value = vin_search_sanitize_param($value);
    $value = trim($value, "/ \t");
    $clean = preg_replace('/[^\p{L}\p{N}_-]+/u', '', $value);
    if ($clean === null) {
        $clean = preg_replace('/[^A-Za-z0-9_-]+/', '', $value);
    }
    return $clean;
}

function vin_search_sanitize_lang_enabled($value) {
    return ((string) $value === '1') ? '1' : '0';
}

function vin_search_sanitize_lang_map($value) {
    if (!is_array($value)) {
        return array();
    }

    $clean = array();
    foreach ($value as $row) {
        if (!is_array($row)) {
            continue;
        }
        $site = vin_search_sanitize_lang_segment(isset($row['site']) ? $row['site'] : '');
        $partner = vin_search_sanitize_lang_segment(isset($row['partner']) ? $row['partner'] : '');
        if ($site === '' && $partner === '') {
            continue;
        }
        $clean[] = array(
            'site' => $site,
            'partner' => $partner,
        );
    }

    return $clean;
}

function vin_search_default_settings() {
    return array(
        'a' => vin_search_default_a(),
        'b' => vin_search_default_b(),
        'voucher' => vin_search_default_voucher(),
        'url_template' => vin_search_default_url_template(),
        'lang_enabled' => '0',
        'lang_map' => array(),
        'lang_default' => '',
        'style_mode' => 'custom',
        'font_family' => '',
        'custom_css' => '',
        'button_bg' => '#FFD220',
        'button_text' => '#333333',
        'button_hover' => '#ffe066',
        'field_bg' => '#ffffff',
        'field_text' => '#333333',
        'field_border' => '#e0e0e0',
    );
}

function vin_search_legacy_option_map() {
    return array(
        'a' => 'vin_search_a',
        'b' => 'vin_search_b',
        'voucher' => 'vin_search_voucher',
        'url_template' => 'vin_search_url_template',
        'lang_enabled' => 'vin_search_lang_enabled',
        'lang_map' => 'vin_search_lang_map',
        'lang_default' => 'vin_search_lang_default',
        'style_mode' => 'vin_search_style_mode',
        'font_family' => 'vin_search_font_family',
        'custom_css' => 'vin_search_custom_css',
        'button_bg' => 'vin_search_button_bg',
        'button_text' => 'vin_search_button_text',
        'button_hover' => 'vin_search_button_hover',
        'field_bg' => 'vin_search_field_bg',
        'field_text' => 'vin_search_field_text',
        'field_border' => 'vin_search_field_border',
    );
}

function vin_search_color_defaults() {
    $settings = vin_search_default_settings();
    return array(
        'button_bg' => $settings['button_bg'],
        'button_text' => $settings['button_text'],
        'button_hover' => $settings['button_hover'],
        'field_bg' => $settings['field_bg'],
        'field_text' => $settings['field_text'],
        'field_border' => $settings['field_border'],
    );
}

function vin_search_normalize_settings($settings) {
    $defaults = vin_search_default_settings();
    if (!is_array($settings)) {
        $settings = array();
    }

    $clean = $defaults;
    $clean['a'] = vin_search_sanitize_param(isset($settings['a']) ? $settings['a'] : $defaults['a']);
    $clean['b'] = vin_search_sanitize_param(isset($settings['b']) ? $settings['b'] : $defaults['b']);
    $clean['voucher'] = vin_search_sanitize_param(isset($settings['voucher']) ? $settings['voucher'] : $defaults['voucher']);
    $clean['url_template'] = vin_search_sanitize_url_template(isset($settings['url_template']) ? $settings['url_template'] : $defaults['url_template']);
    $clean['lang_enabled'] = (isset($settings['lang_enabled']) && (string) $settings['lang_enabled'] === '1') ? '1' : '0';
    $clean['lang_map'] = vin_search_sanitize_lang_map(isset($settings['lang_map']) ? $settings['lang_map'] : array());
    $clean['lang_default'] = vin_search_sanitize_lang_segment(isset($settings['lang_default']) ? $settings['lang_default'] : '');
    $clean['style_mode'] = vin_search_sanitize_style_mode(isset($settings['style_mode']) ? $settings['style_mode'] : 'custom');
    $clean['font_family'] = vin_search_sanitize_font_family(isset($settings['font_family']) ? $settings['font_family'] : '');
    $clean['custom_css'] = vin_search_sanitize_custom_css(isset($settings['custom_css']) ? $settings['custom_css'] : '');
    foreach (vin_search_color_defaults() as $key => $default_color) {
        $clean[$key] = vin_search_sanitize_color(isset($settings[$key]) ? $settings[$key] : $default_color, $key);
    }

    return $clean;
}

function vin_search_legacy_settings() {
    $settings = vin_search_default_settings();
    $found = false;
    foreach (vin_search_legacy_option_map() as $key => $option_name) {
        $value = get_option($option_name, null);
        if ($value === null || $value === false) {
            continue;
        }
        $found = true;
        $settings[$key] = $value;
    }

    return $found ? $settings : null;
}

function vin_search_delete_legacy_options() {
    foreach (vin_search_legacy_option_map() as $option_name) {
        delete_option($option_name);
    }
}

function vin_search_disable_autoload($option) {
    if ($option !== 'vin_search_settings') {
        return;
    }
    global $wpdb;
    $wpdb->update(
        $wpdb->options,
        array('autoload' => 'no'),
        array('option_name' => 'vin_search_settings'),
        array('%s'),
        array('%s')
    );
    wp_cache_delete('alloptions', 'options');
}
add_action('added_option', 'vin_search_disable_autoload');
add_action('updated_option', 'vin_search_disable_autoload');

function vin_search_get_settings() {
    static $cache = null;
    if (is_array($cache)) {
        return $cache;
    }

    $stored = get_option('vin_search_settings', null);
    if (!is_array($stored)) {
        $legacy = vin_search_legacy_settings();
        $stored = is_array($legacy) ? vin_search_normalize_settings($legacy) : vin_search_default_settings();
        add_option('vin_search_settings', $stored, '', 'no');
        $stored = get_option('vin_search_settings', null);
        if (!is_array($stored)) {
            $stored = vin_search_default_settings();
        } elseif (is_array($legacy)) {
            vin_search_delete_legacy_options();
        }
    }

    $cache = vin_search_normalize_settings($stored);
    return $cache;
}

function vin_search_sanitize_settings($input) {
    $stored = get_option('vin_search_settings', null);
    if (!is_array($stored)) {
        $legacy = vin_search_legacy_settings();
        $current = is_array($legacy) ? $legacy : vin_search_default_settings();
    } else {
        $current = array_merge(vin_search_default_settings(), $stored);
    }

    if (!is_array($input)) {
        return vin_search_normalize_settings($current);
    }

    $section = isset($input['section']) ? sanitize_key($input['section']) : '';
    if ($section === 'link') {
        $current['a'] = isset($input['a']) ? $input['a'] : '';
        $current['b'] = isset($input['b']) ? $input['b'] : '';
        $current['voucher'] = isset($input['voucher']) ? $input['voucher'] : '';
        $current['url_template'] = isset($input['url_template']) ? $input['url_template'] : '';
        $current['lang_enabled'] = isset($input['lang_enabled']) ? $input['lang_enabled'] : '0';
        $current['lang_map'] = isset($input['lang_map']) ? $input['lang_map'] : array();
        $current['lang_default'] = isset($input['lang_default']) ? $input['lang_default'] : '';
    } elseif ($section === 'design') {
        $current['style_mode'] = isset($input['style_mode']) ? $input['style_mode'] : 'custom';
        $current['font_family'] = isset($input['font_family']) ? $input['font_family'] : '';
        $current['custom_css'] = isset($input['custom_css']) ? $input['custom_css'] : '';
        foreach (vin_search_color_defaults() as $key => $default_color) {
            $current[$key] = isset($input[$key]) ? $input[$key] : $default_color;
        }
    }

    return vin_search_normalize_settings($current);
}

function vin_search_register_settings() {
    register_setting('vin_search_settings_group', 'vin_search_settings', 'vin_search_sanitize_settings');
}
add_action('admin_init', 'vin_search_register_settings');

function vin_search_lang_config() {
    $settings = vin_search_get_settings();
    $clean = array();
    foreach ($settings['lang_map'] as $row) {
        if (!is_array($row) || empty($row['site'])) {
            continue;
        }
        $clean[] = array(
            'site' => (string) $row['site'],
            'partner' => isset($row['partner']) ? (string) $row['partner'] : '',
        );
    }

    return array(
        'enabled' => $settings['lang_enabled'] === '1',
        'map' => $clean,
        'fallback' => $settings['lang_default'],
    );
}

function vin_search_sanitize_style_mode($value) {
    return $value === 'theme' ? 'theme' : 'custom';
}

function vin_search_sanitize_color($value, $option = '') {
    $defaults = vin_search_color_defaults();
    $default = isset($defaults[$option]) ? $defaults[$option] : '#000000';
    $color = sanitize_hex_color(is_string($value) ? $value : '');
    if (!is_string($color) || $color === '') {
        return $default;
    }
    if (preg_match('/^#([A-Fa-f0-9]{3})$/', $color, $matches)) {
        $chars = $matches[1];
        $color = '#' . $chars[0] . $chars[0] . $chars[1] . $chars[1] . $chars[2] . $chars[2];
    }
    return $color;
}

function vin_search_sanitize_font_family($value) {
    $value = vin_search_sanitize_param($value);
    $value = preg_replace('/[^A-Za-z0-9 ,._\'"-]/', '', $value);
    return trim($value, " ,");
}

function vin_search_sanitize_custom_css($value) {
    $value = wp_check_invalid_utf8((string) $value);
    $value = wp_strip_all_tags($value);
    $value = preg_replace('/<\/?\s*style/i', '', $value);
    $value = preg_replace('/@import\b/i', '', $value);
    $value = preg_replace('/expression\s*\(/i', '', $value);
    $value = preg_replace('/javascript\s*:/i', '', $value);
    $value = preg_replace('/-moz-binding\s*:/i', '', $value);
    $value = preg_replace('/behavior\s*:/i', '', $value);
    $value = preg_replace('/data\s*:/i', '', $value);
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
    if (!is_string($value)) {
        return '';
    }
    if (strlen($value) > 20000) {
        $value = substr($value, 0, 20000);
    }
    return trim($value);
}

function vin_search_design_vars() {
    $settings = vin_search_get_settings();
    if ($settings['style_mode'] === 'theme') {
        return array(
            'button_bg' => 'transparent',
            'button_text' => 'inherit',
            'button_hover' => 'rgba(0,0,0,0.06)',
            'button_border' => 'currentColor',
            'field_bg' => 'transparent',
            'field_text' => 'inherit',
            'field_border' => 'currentColor',
            'font' => 'inherit',
            'font_size' => 'inherit',
            'shadow' => 'none',
        );
    }

    $font = $settings['font_family'];

    return array(
        'button_bg' => $settings['button_bg'],
        'button_text' => $settings['button_text'],
        'button_hover' => $settings['button_hover'],
        'button_border' => 'transparent',
        'field_bg' => $settings['field_bg'],
        'field_text' => $settings['field_text'],
        'field_border' => $settings['field_border'],
        'font' => $font !== '' ? $font : 'inherit',
        'font_size' => '16px',
        'shadow' => '0 1px 3px rgba(0,0,0,0.1)',
    );
}

function vin_search_css_vars() {
    $vars = vin_search_design_vars();
    $rules = array(
        '--vin-button-bg:' . $vars['button_bg'],
        '--vin-button-text:' . $vars['button_text'],
        '--vin-button-hover:' . $vars['button_hover'],
        '--vin-button-border:' . $vars['button_border'],
        '--vin-field-bg:' . $vars['field_bg'],
        '--vin-field-text:' . $vars['field_text'],
        '--vin-field-border:' . $vars['field_border'],
        '--vin-font:' . $vars['font'],
        '--vin-font-size:' . $vars['font_size'],
        '--vin-shadow:' . $vars['shadow'],
    );

    return '#vin-search-form{' . implode(';', $rules) . ';}';
}

function vin_search_custom_css() {
    $settings = vin_search_get_settings();
    return $settings['custom_css'];
}

function vin_search_register_options_page() {
    add_options_page('VIN Search Settings', 'VIN Search', 'manage_options', 'vin_search', 'vin_search_options_page');
}
add_action('admin_menu', 'vin_search_register_options_page');

function vin_search_partner_url() {
    $settings = vin_search_get_settings();
    $template = $settings['url_template'];
    if ($template === '') {
        $template = vin_search_default_url_template();
    }

    $template = str_replace('{{vin}}', '{vin}', $template);

    return strtr($template, array(
        '{a}' => $settings['a'],
        '{b}' => $settings['b'],
        '{voucher}' => $settings['voucher'],
    ));
}

function vin_search_render_design_tab() {
    $settings = vin_search_get_settings();
    $mode = $settings['style_mode'];
    $colors = array(
        'field_bg' => 'Цвет поля',
        'field_text' => 'Цвет текста в поле',
        'field_border' => 'Цвет рамки поля',
        'button_bg' => 'Цвет кнопки',
        'button_text' => 'Цвет текста кнопки',
        'button_hover' => 'Цвет кнопки при наведении',
    );
    ?>
    <form method="post" action="options.php" id="vin-design-form">
        <?php settings_fields('vin_search_settings_group'); ?>
        <input type="hidden" name="vin_search_settings[section]" value="design">
        <fieldset>
            <legend class="screen-reader-text">Режим оформления</legend>
            <p>
                <label>
                    <input type="radio" name="vin_search_settings[style_mode]" id="vin_search_style_mode_theme" value="theme" <?php checked($mode, 'theme'); ?>>
                    Использовать шрифт и цвета сайта
                </label>
            </p>
            <p>
                <label>
                    <input type="radio" name="vin_search_settings[style_mode]" id="vin_search_style_mode_custom" value="custom" <?php checked($mode, 'custom'); ?>>
                    Задать свои цвета и шрифт
                </label>
            </p>
        </fieldset>

        <div id="vin-custom-colors" <?php echo $mode === 'theme' ? 'hidden' : ''; ?>>
            <table class="form-table" role="presentation">
                <?php foreach ($colors as $key => $label) : ?>
                    <tr>
                        <th scope="row"><label for="vin_search_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
                        <td><input type="color" id="vin_search_<?php echo esc_attr($key); ?>" name="vin_search_settings[<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($settings[$key]); ?>"></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th scope="row"><label for="vin_search_font_family">Шрифт</label></th>
                    <td>
                        <input type="text" class="regular-text" id="vin_search_font_family" name="vin_search_settings[font_family]" value="<?php echo esc_attr($settings['font_family']); ?>" placeholder="inherit" spellcheck="false">
                        <p class="description">Пустое поле оставляет шрифт сайта. Пример: <code>Georgia, serif</code></p>
                    </td>
                </tr>
            </table>
        </div>

        <h2>Предпросмотр</h2>
        <div class="vin-design-preview" id="vin-design-preview">
            <p class="vin-preview-context">Текст страницы рядом с формой</p>
            <div class="vin-preview-form">
                <input class="vin-preview-input" type="text" placeholder="ENTER VIN" disabled>
                <button type="button" class="vin-preview-button">DECODE VIN</button>
            </div>
        </div>

        <h2>Свой CSS</h2>
        <p class="description">Дополнительные стили сохраняются и выводятся после основных. Можно переопределить форму селекторами <code>#vin-search-form</code>, <code>#vin-input</code> и <code>#search-button</code>.</p>
        <textarea id="vin_search_custom_css" name="vin_search_settings[custom_css]" rows="12" class="large-text code" spellcheck="false"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
        <style id="vin-preview-custom-css"></style>
        <?php submit_button('Сохранить дизайн'); ?>
    </form>
    <style>
        .vin-design-preview {
            max-width: 640px;
            margin: 12px 0 24px;
            padding: 24px;
            background: #f6f7f7;
            border: 1px solid #dcdcde;
        }
        .vin-preview-context {
            margin: 0 0 12px;
        }
        .vin-preview-form {
            display: flex;
            align-items: center;
            width: 100%;
            border: 1px solid var(--vin-field-border, #e0e0e0);
            border-radius: 4px;
            background: var(--vin-field-bg, #fff);
            box-shadow: var(--vin-shadow, 0 1px 3px rgba(0, 0, 0, 0.1));
            overflow: hidden;
            font-family: var(--vin-font, inherit);
        }
        .vin-preview-input {
            flex: 1;
            width: 100%;
            border: 0;
            outline: none;
            padding: 12px 15px;
            color: var(--vin-field-text, #333);
            background: transparent;
            font-family: inherit;
            font-size: var(--vin-font-size, 16px);
        }
        .vin-preview-input::placeholder {
            color: var(--vin-field-text, #888);
            opacity: 0.55;
        }
        .vin-preview-button {
            border: 1px solid var(--vin-button-border, transparent);
            background: var(--vin-button-bg, #FFD220);
            color: var(--vin-button-text, #333);
            padding: 12px 25px;
            font-family: inherit;
            font-size: var(--vin-font-size, 16px);
            font-weight: bold;
            cursor: pointer;
            white-space: nowrap;
        }
        .vin-preview-button:hover {
            background: var(--vin-button-hover, #ffe066);
        }
    </style>
    <script>
        (function () {
            var form = document.getElementById('vin-design-form');
            var preview = document.getElementById('vin-design-preview');
            var customColors = document.getElementById('vin-custom-colors');
            var customStyle = document.getElementById('vin-preview-custom-css');

            function fieldValue(id) {
                var field = document.getElementById(id);
                return field ? field.value : '';
            }

            function themeMode() {
                var theme = document.getElementById('vin_search_style_mode_theme');
                return !!(theme && theme.checked);
            }

            function updateDesignPreview() {
                var theme = themeMode();
                customColors.hidden = theme;
                if (theme) {
                    preview.style.setProperty('--vin-button-bg', 'transparent');
                    preview.style.setProperty('--vin-button-text', 'inherit');
                    preview.style.setProperty('--vin-button-hover', 'rgba(0,0,0,0.06)');
                    preview.style.setProperty('--vin-button-border', 'currentColor');
                    preview.style.setProperty('--vin-field-bg', 'transparent');
                    preview.style.setProperty('--vin-field-text', 'inherit');
                    preview.style.setProperty('--vin-field-border', 'currentColor');
                    preview.style.setProperty('--vin-font', 'inherit');
                    preview.style.setProperty('--vin-font-size', 'inherit');
                    preview.style.setProperty('--vin-shadow', 'none');
                } else {
                    preview.style.setProperty('--vin-button-bg', fieldValue('vin_search_button_bg'));
                    preview.style.setProperty('--vin-button-text', fieldValue('vin_search_button_text'));
                    preview.style.setProperty('--vin-button-hover', fieldValue('vin_search_button_hover'));
                    preview.style.setProperty('--vin-button-border', 'transparent');
                    preview.style.setProperty('--vin-field-bg', fieldValue('vin_search_field_bg'));
                    preview.style.setProperty('--vin-field-text', fieldValue('vin_search_field_text'));
                    preview.style.setProperty('--vin-field-border', fieldValue('vin_search_field_border'));
                    var font = fieldValue('vin_search_font_family').trim();
                    preview.style.setProperty('--vin-font', font || 'inherit');
                    preview.style.setProperty('--vin-font-size', '16px');
                    preview.style.setProperty('--vin-shadow', '0 1px 3px rgba(0,0,0,0.1)');
                }
                customStyle.textContent = fieldValue('vin_search_custom_css')
                    .replace(/#vin-search-form/g, '.vin-preview-form')
                    .replace(/#search-button/g, '.vin-preview-button')
                    .replace(/#vin-input/g, '.vin-preview-input');
            }

            form.addEventListener('input', updateDesignPreview);
            form.addEventListener('change', updateDesignPreview);
            updateDesignPreview();
        })();
    </script>
    <?php
}

function vin_search_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $tab = (isset($_GET['tab']) && sanitize_key(wp_unslash($_GET['tab'])) === 'design') ? 'design' : 'link';

    $settings = vin_search_get_settings();
    $template = $settings['url_template'];
    $lang_enabled = $settings['lang_enabled'] === '1';
    $lang_map = $settings['lang_map'];
    $lang_default = $settings['lang_default'];
    ?>
    <div class="wrap vin-search-settings">
        <h1>VIN Search Settings</h1>
        <p>Форма поиска по VIN-номеру авто для Carvertical. Для вставки в любое место на сайте используйте: <strong>[vin_search]</strong></p>
        <p class="description">Автор: site2seo. Все настройки плагина хранятся одной записью в базе. Обновления подтягиваются из <a href="https://github.com/vmdnepr/vin-search-form">GitHub</a>.</p>
        <?php settings_errors(); ?>
        <nav class="nav-tab-wrapper" aria-label="Разделы VIN Search">
            <a href="<?php echo esc_url(admin_url('options-general.php?page=vin_search')); ?>" class="nav-tab <?php echo $tab === 'link' ? 'nav-tab-active' : ''; ?>">Ссылка</a>
            <a href="<?php echo esc_url(admin_url('options-general.php?page=vin_search&tab=design')); ?>" class="nav-tab <?php echo $tab === 'design' ? 'nav-tab-active' : ''; ?>">Дизайн</a>
        </nav>
        <?php if ($tab !== 'design') : ?>

        <form method="post" action="options.php">
            <?php settings_fields('vin_search_settings_group'); ?>
            <input type="hidden" name="vin_search_settings[section]" value="link">

            <table class="form-table" role="presentation">
                <tr valign="top">
                    <th scope="row"><label for="vin_search_a">Parameter A</label></th>
                    <td><input type="text" id="vin_search_a" name="vin_search_settings[a]" class="regular-text" value="<?php echo esc_attr($settings['a']); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="vin_search_b">Parameter B</label></th>
                    <td><input type="text" id="vin_search_b" name="vin_search_settings[b]" class="regular-text" value="<?php echo esc_attr($settings['b']); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="vin_search_voucher">Voucher</label></th>
                    <td><input type="text" id="vin_search_voucher" name="vin_search_settings[voucher]" class="regular-text" value="<?php echo esc_attr($settings['voucher']); ?>" /></td>
                </tr>
            </table>

            <h2>Партнёрская ссылка</h2>
            <p class="description">Домен, путь и параметры меняются здесь. Поля A, B и Voucher остаются отдельными и подставляются в метки <code>{a}</code>, <code>{b}</code> и <code>{voucher}</code>. Метка <code>{vin}</code> заменяется номером из формы на сайте. Метка <code>{lang}</code> — сегментом языка партнёра.</p>

            <p>
                <label for="vin-link-base"><strong>Домен и путь</strong></label><br>
                <input type="text" id="vin-link-base" class="vin-link-base" spellcheck="false" autocomplete="off" placeholder="http://www.carvertical.com/ua/precheck">
            </p>

            <p><strong>Параметры ссылки</strong></p>
            <table class="widefat vin-link-params" id="vin-link-params">
                <thead>
                    <tr>
                        <th style="width:28%">Параметр</th>
                        <th>Значение</th>
                        <th style="width:110px"></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <p>
                <button type="button" class="button" id="vin-add-param-btn">Добавить параметр</button>
                <button type="button" class="button" id="vin-reset-template" data-template="<?php echo esc_attr(vin_search_default_url_template()); ?>">Вернуть ссылку Carvertical</button>
            </p>
            <p class="description">Новый параметр можно добавить кнопкой или дописать в ссылку ниже. В значении можно оставить метку, например <code>{voucher}</code>, или написать постоянный текст.</p>

            <p>
                <label for="vin_search_url_template"><strong>Ссылка целиком</strong></label>
            </p>
            <textarea id="vin_search_url_template" name="vin_search_settings[url_template]" rows="3" class="large-text code vin-url-template" spellcheck="false"><?php echo esc_textarea($template); ?></textarea>

            <h2>Язык в ссылке</h2>
            <input type="hidden" name="vin_search_settings[lang_enabled]" value="0">
            <label for="vin_search_lang_enabled">
                <input type="checkbox" id="vin_search_lang_enabled" name="vin_search_settings[lang_enabled]" value="1" <?php checked($lang_enabled); ?>>
                Подставлять язык из адреса нашего сайта
            </label>
            <div id="vin-lang-settings" <?php echo $lang_enabled ? '' : 'hidden'; ?>>
                <p class="description">Берётся сегмент между слешами в адресе страницы, например <code>/ru/</code>, и подменяется сегментом партнёра. В пути партнёрской ссылки должна стоять метка <code>{lang}</code>: <code>https://www.carvertical.com/{lang}/precheck</code>.</p>
                <p>
                    <button type="button" class="button" id="vin-insert-lang">Вставить {lang} в путь</button>
                    <button type="button" class="button" id="vin-add-lang">Добавить язык</button>
                </p>
                <table class="widefat vin-lang-map" id="vin-lang-map">
                    <thead>
                        <tr>
                            <th>Сегмент на нашем сайте</th>
                            <th>Сегмент на сайте партнёра</th>
                            <th style="width:110px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lang_map as $index => $row) :
                            $site_lang = isset($row['site']) ? (string) $row['site'] : '';
                            $partner_lang = isset($row['partner']) ? (string) $row['partner'] : '';
                            ?>
                            <tr>
                                <td>
                                    <span class="vin-lang-affix">/</span>
                                    <input type="text" class="vin-lang-site" name="vin_search_settings[lang_map][<?php echo esc_attr($index); ?>][site]" value="<?php echo esc_attr($site_lang); ?>" placeholder="ru" spellcheck="false" autocomplete="off">
                                    <span class="vin-lang-affix">/</span>
                                </td>
                                <td>
                                    <span class="vin-lang-affix">/</span>
                                    <input type="text" class="vin-lang-partner" name="vin_search_settings[lang_map][<?php echo esc_attr($index); ?>][partner]" value="<?php echo esc_attr($partner_lang); ?>" placeholder="ru" spellcheck="false" autocomplete="off">
                                    <span class="vin-lang-affix">/</span>
                                </td>
                                <td><button type="button" class="button button-small vin-lang-remove">Удалить</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="vin_search_settings[lang_map][sentinel][site]" value="">
                <input type="hidden" name="vin_search_settings[lang_map][sentinel][partner]" value="">
                <p>
                    <label for="vin_search_lang_default">Если в адресе нет такого сегмента, подставить</label><br>
                    <span class="vin-lang-affix">/</span>
                    <input type="text" id="vin_search_lang_default" name="vin_search_settings[lang_default]" value="<?php echo esc_attr($lang_default); ?>" placeholder="ua" spellcheck="false" autocomplete="off">
                    <span class="vin-lang-affix">/</span>
                </p>
            </div>

            <p><strong>Итоговая ссылка</strong></p>
            <p id="vin-url-preview" class="vin-url-preview"></p>
            <p id="vin-url-warning" class="vin-url-warning"></p>
            <p id="vin-url-note" class="description"></p>
            <div id="vin-lang-examples" class="vin-url-preview" hidden></div>
            <p><a href="#" id="vin-url-test" target="_blank" rel="noopener noreferrer">Проверить ссылку с тестовым VIN</a></p>

            <?php submit_button(); ?>
        </form>
        <?php else : ?>
            <?php vin_search_render_design_tab(); ?>
        <?php endif; ?>
    </div>
    <?php if ($tab !== 'design') : ?>
    <style>
        .vin-search-settings .vin-link-base,
        .vin-search-settings .vin-url-template,
        .vin-search-settings .vin-url-preview {
            width: 100%;
            max-width: 920px;
            font-family: Consolas, Monaco, monospace;
        }
        .vin-search-settings .vin-link-params {
            max-width: 920px;
            margin-top: 8px;
        }
        .vin-search-settings .vin-link-params input {
            width: 100%;
            font-family: Consolas, Monaco, monospace;
        }
        .vin-search-settings .vin-url-preview {
            word-break: break-all;
            background: #f6f7f7;
            padding: 12px;
            border: 1px solid #dcdcde;
        }
        .vin-search-settings .vin-url-warning {
            color: #b32d2e;
            max-width: 920px;
        }
        .vin-search-settings .vin-lang-map {
            max-width: 920px;
            margin-top: 8px;
        }
        .vin-search-settings .vin-lang-map input,
        .vin-search-settings #vin_search_lang_default {
            width: 160px;
            font-family: Consolas, Monaco, monospace;
        }
        .vin-search-settings .vin-lang-affix {
            display: inline-block;
            margin: 0 4px;
            color: #646970;
            font-family: Consolas, Monaco, monospace;
        }
        .vin-search-settings #vin-lang-examples div + div {
            margin-top: 6px;
        }
    </style>
    <script>
        (function () {
            var hashSuffix = '';
            var suppressSync = false;
            var baseInput = document.getElementById('vin-link-base');
            var templateInput = document.getElementById('vin_search_url_template');
            var paramsTable = document.getElementById('vin-link-params');
            var paramsBody = paramsTable.querySelector('tbody');
            var preview = document.getElementById('vin-url-preview');
            var warning = document.getElementById('vin-url-warning');
            var note = document.getElementById('vin-url-note');
            var testLink = document.getElementById('vin-url-test');
            var langExamples = document.getElementById('vin-lang-examples');
            var langSettings = document.getElementById('vin-lang-settings');
            var langToggle = document.getElementById('vin_search_lang_enabled');
            var langMapTable = document.getElementById('vin-lang-map');
            var sampleVin = '1HGCM82633A123456';
            var langIndex = <?php echo (int) count($lang_map) + 1; ?>;

            function parseTemplate(template) {
                template = template.trim();
                var hash = '';
                var hashIndex = template.indexOf('#');
                if (hashIndex !== -1) {
                    hash = template.slice(hashIndex);
                    template = template.slice(0, hashIndex);
                }
                var queryIndex = template.indexOf('?');
                var base = queryIndex === -1 ? template : template.slice(0, queryIndex);
                var query = queryIndex === -1 ? '' : template.slice(queryIndex + 1);
                var params = [];
                if (query !== '') {
                    query.split('&').forEach(function (part) {
                        if (part === '') {
                            return;
                        }
                        var eq = part.indexOf('=');
                        if (eq === -1) {
                            params.push({ name: part, value: '' });
                        } else {
                            params.push({ name: part.slice(0, eq), value: part.slice(eq + 1) });
                        }
                    });
                }
                return { base: base, params: params, hash: hash };
            }

            function readParams() {
                var params = [];
                paramsBody.querySelectorAll('tr').forEach(function (row) {
                    if (row.classList.contains('vin-params-empty')) {
                        return;
                    }
                    var nameInput = row.querySelector('.vin-param-name');
                    var valueInput = row.querySelector('.vin-param-value');
                    if (!nameInput || !valueInput) {
                        return;
                    }
                    params.push({ name: nameInput.value.trim(), value: valueInput.value });
                });
                return params;
            }

            function buildTemplate(base, params, hash) {
                var pairs = [];
                params.forEach(function (param) {
                    if (param.name === '') {
                        return;
                    }
                    pairs.push(param.name + '=' + param.value);
                });
                var url = base;
                if (pairs.length) {
                    url += (url.indexOf('?') === -1 ? '?' : '&') + pairs.join('&');
                }
                return url + (hash || '');
            }

            function showEmptyParams() {
                var row = document.createElement('tr');
                row.className = 'vin-params-empty';
                var cell = document.createElement('td');
                cell.colSpan = 3;
                cell.textContent = 'Параметров пока нет. Добавьте их кнопкой ниже или оставьте {vin} прямо в пути.';
                row.appendChild(cell);
                paramsBody.appendChild(row);
            }

            function appendParamRow(param) {
                var empty = paramsBody.querySelector('.vin-params-empty');
                if (empty) {
                    empty.remove();
                }
                var row = document.createElement('tr');
                var nameCell = document.createElement('td');
                var valueCell = document.createElement('td');
                var actionCell = document.createElement('td');
                var nameInput = document.createElement('input');
                var valueInput = document.createElement('input');
                var removeButton = document.createElement('button');

                nameInput.type = 'text';
                nameInput.className = 'vin-param-name';
                nameInput.spellcheck = false;
                nameInput.placeholder = 'utm_source';
                nameInput.value = param.name || '';

                valueInput.type = 'text';
                valueInput.className = 'vin-param-value';
                valueInput.spellcheck = false;
                valueInput.placeholder = '{a} или своё значение';
                valueInput.value = param.value || '';

                removeButton.type = 'button';
                removeButton.className = 'button button-small vin-param-remove';
                removeButton.textContent = 'Удалить';

                nameCell.appendChild(nameInput);
                valueCell.appendChild(valueInput);
                actionCell.appendChild(removeButton);
                row.appendChild(nameCell);
                row.appendChild(valueCell);
                row.appendChild(actionCell);
                paramsBody.appendChild(row);
                return nameInput;
            }

            function renderParams(params) {
                suppressSync = true;
                paramsBody.textContent = '';
                if (!params.length) {
                    showEmptyParams();
                } else {
                    params.forEach(function (param) {
                        appendParamRow(param);
                    });
                }
                suppressSync = false;
            }

            function applyPlaceholders(template, vin) {
                var map = {
                    '{a}': document.getElementById('vin_search_a').value,
                    '{b}': document.getElementById('vin_search_b').value,
                    '{voucher}': document.getElementById('vin_search_voucher').value,
                    '{vin}': vin
                };
                return template.replace(/\{(a|b|voucher|vin)\}/g, function (token) {
                    return Object.prototype.hasOwnProperty.call(map, token) ? map[token] : token;
                });
            }

            function langSegment(value) {
                return String(value || '').replace(/\//g, '').trim();
            }

            function langIsEnabled() {
                return !!(langToggle && langToggle.checked);
            }

            function readLangRows() {
                var rows = [];
                langMapTable.querySelectorAll('tbody tr').forEach(function (row) {
                    var siteInput = row.querySelector('.vin-lang-site');
                    var partnerInput = row.querySelector('.vin-lang-partner');
                    if (!siteInput || !partnerInput) {
                        return;
                    }
                    rows.push({
                        site: langSegment(siteInput.value),
                        partner: langSegment(partnerInput.value)
                    });
                });
                return rows;
            }

            function langFallback() {
                var input = document.getElementById('vin_search_lang_default');
                return input ? langSegment(input.value) : '';
            }

            function applyLang(url, lang) {
                return url.split('{lang}').join(lang || '').replace(/([^:])\/{2,}/g, '$1/');
            }

            function addLangRow(site, partner) {
                var row = document.createElement('tr');
                var siteCell = document.createElement('td');
                var partnerCell = document.createElement('td');
                var actionCell = document.createElement('td');
                var index = langIndex++;

                function segmentField(className, field, value, placeholder) {
                    var prefix = document.createElement('span');
                    var input = document.createElement('input');
                    var suffix = document.createElement('span');
                    prefix.className = 'vin-lang-affix';
                    prefix.textContent = '/';
                    suffix.className = 'vin-lang-affix';
                    suffix.textContent = '/';
                    input.type = 'text';
                    input.className = className;
                    input.name = 'vin_search_settings[lang_map][' + index + '][' + field + ']';
                    input.placeholder = placeholder;
                    input.spellcheck = false;
                    input.autocomplete = 'off';
                    input.value = value || '';
                    var cellContent = document.createDocumentFragment();
                    cellContent.appendChild(prefix);
                    cellContent.appendChild(input);
                    cellContent.appendChild(suffix);
                    return { content: cellContent, input: input };
                }

                var siteField = segmentField('vin-lang-site', 'site', site, 'ru');
                var partnerField = segmentField('vin-lang-partner', 'partner', partner, 'ua');
                var removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.className = 'button button-small vin-lang-remove';
                removeButton.textContent = 'Удалить';
                siteCell.appendChild(siteField.content);
                partnerCell.appendChild(partnerField.content);
                actionCell.appendChild(removeButton);
                row.appendChild(siteCell);
                row.appendChild(partnerCell);
                row.appendChild(actionCell);
                langMapTable.querySelector('tbody').appendChild(row);
                return siteField.input;
            }

            function updatePreview() {
                var template = templateInput.value.trim();
                var previewUrl = applyPlaceholders(template, '{vin}');
                var testUrl = applyPlaceholders(template, sampleVin);
                preview.textContent = previewUrl || 'Ссылка пустая';

                var unused = [];
                if (template.indexOf('{a}') === -1) unused.push('{a}');
                if (template.indexOf('{b}') === -1) unused.push('{b}');
                if (template.indexOf('{voucher}') === -1) unused.push('{voucher}');

                var messages = [];
                if (template.indexOf('{vin}') === -1) {
                    messages.push('В ссылке нет {vin}. Номер из формы не попадёт в адрес. Добавьте параметр vin со значением {vin} или вставьте {vin} в путь.');
                }
                if (unused.length) {
                    messages.push('Сейчас не используются: ' + unused.join(', ') + '. Значения A, B и Voucher попадают в ссылку только через эти метки.');
                }
                if (langIsEnabled() && template.indexOf('{lang}') === -1) {
                    messages.push('Подстановка языка включена, но в пути нет {lang}. Нажмите «Вставить {lang} в путь» или добавьте метку вручную.');
                }
                if (!langIsEnabled() && template.indexOf('{lang}') !== -1) {
                    messages.push('В ссылке есть {lang}, а подстановка языка выключена. Метка останется в адресе.');
                }
                warning.textContent = messages.join(' ');
                note.textContent = template.indexOf('{vin}') === -1
                    ? ''
                    : '{vin} на сайте заменится на номер, который введёт посетитель.';

                langExamples.textContent = '';
                if (langIsEnabled() && template.indexOf('{lang}') !== -1) {
                    readLangRows().forEach(function (row) {
                        if (!row.site) {
                            return;
                        }
                        var line = document.createElement('div');
                        line.textContent = '/' + row.site + '/ → ' + applyLang(previewUrl, row.partner);
                        langExamples.appendChild(line);
                    });
                    var fallbackLine = document.createElement('div');
                    fallbackLine.textContent = 'Если сегмент не найден → ' + applyLang(previewUrl, langFallback());
                    langExamples.appendChild(fallbackLine);
                    langExamples.hidden = false;
                    var samplePartner = '';
                    readLangRows().some(function (row) {
                        if (row.partner) {
                            samplePartner = row.partner;
                            return true;
                        }
                        return false;
                    });
                    testUrl = applyLang(testUrl, samplePartner || langFallback());
                } else {
                    langExamples.hidden = true;
                }

                if (/^https?:\/\//i.test(testUrl) && testUrl.indexOf('{lang}') === -1) {
                    testLink.href = testUrl;
                } else {
                    testLink.href = '#';
                }
            }

            function syncFromTemplate() {
                var parsed = parseTemplate(templateInput.value);
                hashSuffix = parsed.hash;
                suppressSync = true;
                baseInput.value = parsed.base;
                suppressSync = false;
                renderParams(parsed.params);
                updatePreview();
            }

            function syncFromVisual() {
                var baseValue = baseInput.value.trim();
                if (baseValue.indexOf('?') !== -1 || baseValue.indexOf('#') !== -1) {
                    suppressSync = true;
                    templateInput.value = baseValue;
                    suppressSync = false;
                    syncFromTemplate();
                    return;
                }
                suppressSync = true;
                templateInput.value = buildTemplate(baseValue, readParams(), hashSuffix);
                suppressSync = false;
                updatePreview();
            }

            baseInput.addEventListener('input', function () {
                if (!suppressSync) {
                    syncFromVisual();
                }
            });
            templateInput.addEventListener('input', function () {
                if (!suppressSync) {
                    syncFromTemplate();
                }
            });
            paramsTable.addEventListener('input', function (event) {
                if (suppressSync) {
                    return;
                }
                if (event.target.classList.contains('vin-param-name') || event.target.classList.contains('vin-param-value')) {
                    syncFromVisual();
                }
            });
            paramsTable.addEventListener('click', function (event) {
                var button = event.target.closest('.vin-param-remove');
                if (!button) {
                    return;
                }
                var row = button.closest('tr');
                if (row) {
                    row.remove();
                }
                if (!paramsBody.querySelector('tr')) {
                    showEmptyParams();
                }
                syncFromVisual();
            });
            document.getElementById('vin-add-param-btn').addEventListener('click', function () {
                var nameInput = appendParamRow({ name: '', value: '' });
                nameInput.focus();
            });
            document.getElementById('vin-reset-template').addEventListener('click', function () {
                if (!window.confirm('Вернуть исходную ссылку Carvertical? Поля A, B и Voucher не изменятся. Сохранить нужно кнопкой внизу страницы.')) {
                    return;
                }
                templateInput.value = this.getAttribute('data-template') || '';
                syncFromTemplate();
            });
            ['vin_search_a', 'vin_search_b', 'vin_search_voucher'].forEach(function (id) {
                document.getElementById(id).addEventListener('input', updatePreview);
            });
            testLink.addEventListener('click', function (event) {
                var href = testLink.getAttribute('href') || '';
                if (!/^https?:\/\//i.test(href) || href.indexOf('{lang}') !== -1) {
                    event.preventDefault();
                    window.alert(href.indexOf('{lang}') !== -1
                        ? 'В ссылке осталась метка {lang}. Включите подстановку и заполните сопоставление или уберите метку.'
                        : 'Ссылка должна начинаться с http:// или https://');
                }
            });
            langToggle.addEventListener('change', function () {
                langSettings.hidden = !langToggle.checked;
                if (langToggle.checked && !langMapTable.querySelector('tbody tr')) {
                    addLangRow('', '');
                }
                updatePreview();
            });
            document.getElementById('vin-add-lang').addEventListener('click', function () {
                addLangRow('', '').focus();
                updatePreview();
            });
            document.getElementById('vin-insert-lang').addEventListener('click', function () {
                var base = baseInput.value.trim();
                if (base.indexOf('{lang}') === -1) {
                    var next = base.replace(/^(https?:\/\/[^\/]+)\/[^\/?#]+/, '$1/{lang}');
                    if (next === base) {
                        next = base.replace(/\/+$/, '') + '/{lang}';
                    }
                    baseInput.value = next;
                    syncFromVisual();
                    return;
                }
                updatePreview();
            });
            langMapTable.addEventListener('input', updatePreview);
            langMapTable.addEventListener('click', function (event) {
                var button = event.target.closest('.vin-lang-remove');
                if (!button) {
                    return;
                }
                var row = button.closest('tr');
                if (row) {
                    row.remove();
                }
                updatePreview();
            });
            document.getElementById('vin_search_lang_default').addEventListener('input', updatePreview);

            syncFromTemplate();
        })();
    </script>
    <?php endif; ?>
    <?php
}

function vin_search_form() {
    static $assets_printed = false;
    $partner_url = vin_search_partner_url();
    $print_assets = !$assets_printed;
    $assets_printed = true;

    ob_start();
    if ($print_assets) :
    ?>
    <style>
    <?php echo vin_search_css_vars(); ?>

    /* Styles for the form container */
    #vin-search-container {
        display: flex;
        flex-direction: column; /* To stack the form and the "Try a sample VIN" link */
        align-items: center;
        width: 100%;
        max-width: 600px; /* Adjust as needed */
    }

    #vin-search-form {
        display: flex;
        align-items: center;
        /* The image shows a very subtle or no border, relying on shadow */
        border: 1px solid var(--vin-field-border, #e0e0e0);
        border-radius: 4px; /* Slightly less rounded than original CSS */
        /* Padding is mainly handled by the input itself now */
        padding: 0;
        width: 90%;
        background-color: var(--vin-field-bg, #fff);
        font-family: var(--vin-font, inherit);
        box-shadow: var(--vin-shadow, 0 1px 3px rgba(0, 0, 0, 0.1));
        overflow: hidden; /* Ensures border-radius is applied to children */
    }

    .input-container {
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
    }

    #vin-input {
        width: 100%;
        border: none; /* Remove the dashed border */
        outline: none;
        font-size: var(--vin-font-size, 16px);
        padding: 12px 15px; /* Adjust padding to match the image's height */
        padding-right: 40px; /* Space for the (missing) icon if added later, or just more padding */
        color: var(--vin-field-text, #333);
        font-family: inherit;
        background-color: transparent;
        border-radius: 4px; /* Inherit from parent or set here if needed */
    }

    #vin-input::placeholder {
        color: var(--vin-field-text, #888);
        opacity: 0.55;
    }

    /* The image does not show a help icon, so this section is kept but its display might need adjustment */
    .help-icon {
        position: absolute;
        right: 10px;
        width: 20px;
        height: 20px;
        background-color: transparent; /* No background color, just a symbol if needed */
        border-radius: 50%;
        display: none; /* Hide if not needed, or change to flex if an icon is desired */
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #999;
        cursor: pointer;
    }

    /* Tooltip styling - keep if functionality is desired, but not visible in the image */
    .help-icon:hover .tooltip {
        visibility: visible;
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .tooltip {
        visibility: hidden;
        opacity: 0;
        width: auto;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 5px;
        padding: 5px;
        position: absolute;
        bottom: 35px;
        right: 0;
        font-size: 12px;
        line-height: 1.4;
        z-index: 1;
        white-space: nowrap;
    }

    #search-button {
        background-color: var(--vin-button-bg, #FFD220);
        color: var(--vin-button-text, #333);
        border: 1px solid var(--vin-button-border, transparent);
        padding: 12px 25px; /* Adjust padding to match the button size in the image */
        font-size: var(--vin-font-size, 16px);
        font-family: inherit;
        font-weight: bold; /* Bold text for the button */
        border-radius: 4px; /* Matches the form's border-radius */
        cursor: pointer;
        transition: background-color 0.2s ease;
        margin-left: 0; /* Remove margin-left from button to integrate it directly */
        white-space: nowrap; /* Prevents text from wrapping */
    }

    #search-button:hover {
        background-color: var(--vin-button-hover, #ffe066);
    }

    /* Style for the "Try a sample VIN" link */
    .sample-vin-link {
        margin-top: 15px; /* Space between form and link */
        font-size: 14px;
        color: #007bff; /* Standard link blue */
        text-decoration: underline;
        cursor: pointer;
    }

    .sample-vin-link:hover {
        color: #0056b3; /* Darker blue on hover */
    }

    <?php echo vin_search_custom_css(); ?>
</style>
    <?php endif; ?>

    <form id="vin-search-form">
        <div class="input-container">
            <input type="text" id="vin-input" placeholder="ENTER VIN" required>
            <div class="help-icon">?
                <div class="tooltip">VIN - це унікальний набір із 17 символів</div>
            </div>
        </div>
        <button type="button" id="search-button" onclick="redirectToCarVertical()">DECODE VIN</button>
    </form>

    <?php if ($print_assets) : ?>
    <script>
        function redirectToCarVertical() {
            var vinInput = document.getElementById('vin-input');
            var vin = vinInput ? vinInput.value.trim() : '';
            if (!vin) {
                alert("Please enter your VIN code");
                return;
            }
            var langConfig = <?php echo wp_json_encode(vin_search_lang_config(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            var url = <?php echo wp_json_encode($partner_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            if (langConfig && langConfig.enabled && url.indexOf('{lang}') !== -1) {
                var parts = window.location.pathname.split('/');
                var partnerLang = '';
                var matchedLang = false;
                var map = langConfig.map || [];
                for (var i = 0; i < parts.length && !matchedLang; i++) {
                    if (!parts[i]) {
                        continue;
                    }
                    var segment = parts[i].toLowerCase();
                    for (var j = 0; j < map.length; j++) {
                        if (String(map[j].site || '').toLowerCase() === segment) {
                            partnerLang = map[j].partner || '';
                            matchedLang = true;
                            break;
                        }
                    }
                }
                if (!partnerLang) {
                    partnerLang = langConfig.fallback || '';
                }
                url = url.split('{lang}').join(encodeURIComponent(partnerLang)).replace(/([^:])\/{2,}/g, '$1/');
            }
            url = url.replace(/\{vin\}/g, encodeURIComponent(vin));
            window.open(url, '_blank', 'noopener,noreferrer');
        }
    </script>
    <?php endif; ?>
    <?php
    return ob_get_clean();
}

add_shortcode('vin_search', 'vin_search_form');

function vin_search_github_repo() {
    $repo = defined('VIN_SEARCH_GITHUB_REPO') ? trim(VIN_SEARCH_GITHUB_REPO) : '';
    if (!preg_match('#^[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+$#', $repo)) {
        return '';
    }
    return $repo;
}

function vin_search_github_release() {
    $repo = vin_search_github_repo();
    if ($repo === '') {
        return array('ok' => false);
    }

    $cached = get_transient('vin_search_github_release');
    if (is_array($cached) && isset($cached['repo']) && $cached['repo'] === $repo) {
        return $cached;
    }

    $response = wp_remote_get('https://api.github.com/repos/' . $repo . '/releases/latest', array(
        'timeout' => 8,
        'headers' => array(
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'VIN-Search-WordPress',
        ),
    ));

    $failed = array('ok' => false, 'repo' => $repo);
    if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
        set_transient('vin_search_github_release', $failed, 10 * MINUTE_IN_SECONDS);
        return $failed;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data) || empty($data['tag_name']) || !is_string($data['tag_name'])) {
        set_transient('vin_search_github_release', $failed, 10 * MINUTE_IN_SECONDS);
        return $failed;
    }

    $tag = $data['tag_name'];
    $version = preg_replace('/^v/i', '', $tag);
    if (!is_string($version) || $version === '' || !preg_match('/^\d+\.\d+/', $version)) {
        set_transient('vin_search_github_release', $failed, 10 * MINUTE_IN_SECONDS);
        return $failed;
    }

    $release = array(
        'ok' => true,
        'repo' => $repo,
        'tag' => $tag,
        'version' => $version,
        'package' => 'https://github.com/' . $repo . '/archive/refs/tags/' . rawurlencode($tag) . '.zip',
        'url' => 'https://github.com/' . $repo . '/releases/tag/' . rawurlencode($tag),
        'notes' => isset($data['body']) && is_string($data['body']) ? $data['body'] : '',
    );
    set_transient('vin_search_github_release', $release, HOUR_IN_SECONDS);
    return $release;
}

function vin_search_inject_github_update($transient) {
    if (!is_object($transient) || empty($transient->checked) || !is_array($transient->checked)) {
        return $transient;
    }

    $plugin = plugin_basename(__FILE__);
    if (empty($transient->checked[$plugin])) {
        return $transient;
    }

    $release = vin_search_github_release();
    if (empty($release['ok'])) {
        return $transient;
    }
    if (version_compare($release['version'], $transient->checked[$plugin], '<=')) {
        return $transient;
    }

    $slug = dirname($plugin);
    if ($slug === '.' || $slug === '') {
        $slug = 'vin-search-form';
    }

    $transient->response[$plugin] = (object) array(
        'slug' => $slug,
        'plugin' => $plugin,
        'new_version' => $release['version'],
        'url' => $release['url'],
        'package' => $release['package'],
    );
    return $transient;
}
add_filter('pre_set_site_transient_update_plugins', 'vin_search_inject_github_update');

function vin_search_github_plugin_info($result, $action, $args) {
    if ($action !== 'plugin_information' || !is_object($args) || empty($args->slug)) {
        return $result;
    }

    $plugin = plugin_basename(__FILE__);
    $slug = dirname($plugin);
    if ($slug === '.' || $slug === '') {
        $slug = 'vin-search-form';
    }
    if ($args->slug !== $slug) {
        return $result;
    }

    $release = vin_search_github_release();
    if (empty($release['ok'])) {
        return $result;
    }

    $notes = $release['notes'] !== '' ? $release['notes'] : 'Обновление с GitHub.';
    return (object) array(
        'name' => 'VIN Search - Carvertical',
        'slug' => $slug,
        'version' => $release['version'],
        'author' => 'site2seo',
        'homepage' => 'https://github.com/' . $release['repo'],
        'download_link' => $release['package'],
        'trunk' => $release['package'],
        'sections' => array(
            'description' => 'Форма поиска по VIN для Carvertical.',
            'changelog' => wpautop(esc_html($notes)),
        ),
    );
}
add_filter('plugins_api', 'vin_search_github_plugin_info', 20, 3);

function vin_search_fix_github_folder($source, $remote_source, $upgrader, $hook_extra) {
    unset($upgrader);
    $plugin = plugin_basename(__FILE__);
    if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== $plugin) {
        return $source;
    }

    $slug = dirname($plugin);
    if ($slug === '.' || $slug === '') {
        return $source;
    }

    $from = untrailingslashit(wp_normalize_path($source));
    if (basename($from) === $slug) {
        return trailingslashit($from);
    }
    if (!is_dir($from)) {
        return $source;
    }

    $to = untrailingslashit(wp_normalize_path($remote_source)) . '/' . $slug;
    if (is_dir($to) || !@rename($from, $to)) {
        return $source;
    }
    return trailingslashit($to);
}
add_filter('upgrader_source_selection', 'vin_search_fix_github_folder', 10, 4);

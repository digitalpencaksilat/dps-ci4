<?php

/**
 * CI3 compatibility helpers to allow ported views to run with minimal changes.
 * Keep these small and focused on public landing needs.
 */

use CodeIgniter\Database\BaseConnection;

if (! function_exists('get_setting')) {
    /**
     * Port of MY_Controller::get_setting() (CI3).
     *
     * Reads `site_builder_settings.setting` first. If missing, returns null.
     * For file-type settings stored as JSON with is_array=1, returns ['data'].
     */
    function get_setting(string $key, string $configFile = 'pendaftaran/profil_kejuaraan')
    {
        try {
            /** @var BaseConnection $db */
            $db = db_connect();

            $row = $db->table('site_builder_settings')
                ->where('setting', $key)
                ->get()
                ->getRow();
        } catch (Throwable $e) {
            // During early migration the DB may not be configured yet.
            // Return null so the view can still render.
            return null;
        }

        if ($row && $row->value !== null && $row->value !== '') {
            if ((int) ($row->is_array ?? 0) === 1) {
                $val = json_decode($row->value, true);
                if (is_array($val) && array_key_exists('data', $val)) {
                    return $val['data'];
                }
                return $val;
            }
            return $row->value;
        }

        // Best-effort fallback: read old CI3 config PHP files if present under public/assets/config.
        // For landing pages, DB settings are expected to exist so this is rarely used.
        return null;
    }
}

if (! function_exists('phpb_theme_asset')) {
    /**
     * Minimal replacement for PHPageBuilder's phpb_theme_asset().
     * In this project theme assets live under public/assets/pendaftaran/themes/basic-landing-pages.
     */
    function phpb_theme_asset(string $path): string
    {
        $path = ltrim($path, '/');
        return base_url('assets/pendaftaran/themes/basic-landing-pages/' . $path);
    }
}

// Some legacy views call get_instance()->get_setting(...). Provide a tiny shim.
if (! function_exists('ci3_config_item')) {
    /**
     * Read a value from a migrated CI3 config file (app/Config/ci3/<file>.php).
     *
     * Example: ci3_config_item('event_location', 'pendaftaran/profil_kejuaraan')
     */
    function ci3_config_item(string $key, string $configFile): mixed
    {
        $path = APPPATH . 'Config/ci3/' . trim($configFile, '/');
        if (! str_ends_with($path, '.php')) {
            $path .= '.php';
        }
        if (! is_file($path)) {
            return null;
        }

        // CI3 config files guard on BASEPATH.
        if (! defined('BASEPATH')) {
            define('BASEPATH', APPPATH);
        }

        $config = [];
        require $path;

        return $config[$key] ?? null;
    }
}

// Some legacy views call get_instance()->get_setting(...). Provide a tiny shim.
if (! function_exists('get_instance')) {
    function get_instance()
    {
        return new class {
            public function get_setting(string $key, string $configFile = 'pendaftaran/profil_kejuaraan')
            {
                return get_setting($key, $configFile);
            }
        };
    }
}

if (! function_exists('get_all_sponsors')) {
    function get_all_sponsors()
    {
        // Legacy helper reads assets/config/sponsors.json under public.
        $path = FCPATH . 'assets/config/sponsors.json';
        if (! is_file($path)) {
            return null;
        }
        $data = json_decode(file_get_contents($path));
        return $data ?: null;
    }
}

if (! function_exists('get_sponsor')) {
    function get_sponsor(string $name)
    {
        $all = get_all_sponsors();
        if (! $all) {
            return null;
        }
        return $all->$name ?? null;
    }
}

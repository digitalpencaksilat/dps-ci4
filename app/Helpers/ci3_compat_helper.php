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

if (! function_exists('online_asset')) {
    /**
     * Centralized CDN URLs for third-party libraries.
     * Keep project-specific/custom files local. Only use this for public CDN-safe libraries.
     */
    function online_asset(string $key): string
    {
        $assets = [
            'bootstrap_5_css'              => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
            'bootstrap_5_bundle_js'        => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
            'jquery_3_js'                  => 'https://code.jquery.com/jquery-3.7.1.min.js',
            'datatables_bs5_css'           => 'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css',
            'datatables_jquery_js'         => 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
            'datatables_bs5_js'            => 'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js',
            'datatables_responsive_css'    => 'https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css',
            'datatables_responsive_js'     => 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js',
            'datatables_responsive_bs5_js' => 'https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js',
            'fontawesome_6_css'            => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
            'fontawesome_4_css'            => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css',
            'select2_css'                  => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            'select2_bs5_css'              => 'https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css',
            'select2_js'                   => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            'toastr_css'                   => 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css',
            'toastr_js'                    => 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js',
            'sweetalert2_js'               => 'https://cdn.jsdelivr.net/npm/sweetalert2@11',
        ];

        return $assets[$key] ?? '';
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

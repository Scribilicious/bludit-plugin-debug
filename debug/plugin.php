<?php

class pluginDebug extends Plugin {

    private $tabs = null;

    /**
     * Initialize
     */
    public function init()
    {
        $this->dbFields = [
            'file-error' => '/var/log/apache2/error.log',
            'file-access' => '/var/log/apache2/access.log',
            'display-errors' => 3,
        ];
    }

    public function beforeSiteLoad()
    {
        if (in_array($this->getValue('display-errors'), [1,3])) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
    }

    public function adminHead()
    {
        if (in_array($this->getValue('display-errors'), [2,3])) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }
    }

    /**
     * Check before saving
     */
    public function post()
    {
        // If needed clears the Error files
        if ($action = intval($_POST['clear-cache'])) {
            if (($action === 1 || $action === 3) && ($file = $this->getValue('file-error')) && file_exists($file)) {
                @file_put_contents($file, '');
            }
            if (($action === 2 || $action === 3) && ($file = $this->getValue('file-access')) && file_exists($file)) {
                @file_put_contents($file, '');
            }
        }

        // Writes in the DB
        $this->db['file-error'] = trim(filter_var($_POST['file-error'], FILTER_SANITIZE_URL));
        $this->db['file-access'] = trim(filter_var($_POST['file-access'], FILTER_SANITIZE_URL));
        $this->db['display-errors'] = intval($_POST['display-errors']);

        // Save the database
        return $this->save();
    }

    /**
     * Creates the config form
     */
    public function form()
    {
        global $L;

        $html = $this->payMe();

        $settings = '<br><div class="alert alert-primary" role="alert">';
        $settings .= $L->get('Debug Help');
        $settings .= '</div>';
        $settings .= '<div>';
        $settings .= '<label>' . $L->get('Debug Error log file') . '</label>';
        $settings .= '<input name="file-error" type="text" value="' . $this->getValue('file-error') . '">';
        $settings .= '</div>';
        $settings .= '<div>';
        $settings .= '<label>' . $L->get('Debug Access log file') . '</label>';
        $settings .= '<input name="file-access" type="text" value="' . $this->getValue('file-access') . '">';
        $settings .= '</div>';
        $settings .= '<div>';
        $settings .= '<label>' . $L->get('Debug Display errors') . '</label>';
        $settings .= '<select name="display-errors">';
        $settings .= '<option value="" ' . ($this->getValue('display-errors') === 0 ? 'selected' : '') . '>' . $L->get('Debug None') . '</option>';
        $settings .= '<option value="1" ' . ($this->getValue('display-errors') === 1 ? 'selected' : '') . '>' . $L->get('Debug Page') . '</option>';
        $settings .= '<option value="2" ' . ($this->getValue('display-errors') === 2 ? 'selected' : '') . '>' . $L->get('Debug Admin') . '</option>';
        $settings .= '<option value="3" ' . ($this->getValue('display-errors') === 3 ? 'selected' : '') . '>' . $L->get('Debug All') . '</option>';
        $settings .= '</select>';
        $settings .= '</div>';
        $settings .= '<div>';
        $settings .= '<label>' . $L->get('Debug Clear log files') . '</label>';
        $settings .= '<select name="clear-cache">';
        $settings .= '<option value="">-</option>';
        $settings .= '<option value="1">' . $L->get('Debug Errors log') . '</option>';
        $settings .= '<option value="2">' . $L->get('Debug Access log') . '</option>';
        $settings .= '<option value="3">' . $L->get('Debug Clear all') . '</option>';
        $settings .= '</select>';
        $settings .= '</div>';

        $this->addTab($L->get('Debug Settings'), $settings);
        $this->addTab($L->get('Debug Errors Log'), '<br>' . $this->loadLog($this->getValue('file-error')));
        $this->addTab($L->get('Debug Access Log'), '<br>' . $this->loadLog($this->getValue('file-access')));
        $html .= $this->outputTabs();

        $html .= $this->footer();

        return $html;
    }

    /**
     * Adds a Tab Html
     */
    private function addTab($sTitle, $sHtml) {
        $this->tabs[] = [
            'title' => $sTitle,
            'html' => $sHtml
        ];
    }

    /**
     * Creates the Tab Html
     */
    private function outputTabs() {
        $nav = '';
        $content = '';

        foreach ($this->tabs as $key => $tab) {
            $link = 'tab-' . str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $tab['title']));
            $nav .= '<li class="nav-item">';
            $nav .= '<a class="nav-link' . ($key == 0 ? ' active' : '') . '" id="pages-tab" data-toggle="tab" href="#' . $link .'" role="tab" aria-selected="true">' . $tab['title'] . '</a>';
            $nav .= '</li>';
            $content .= '<div class="tab-pane' . ($key == 0 ? ' active' : '') . '" id="' . $link . '" role="tabpanel">' . $tab['html'] . '</div>';
        }

        return '<ul class="nav nav-tabs" role="tablist">'
            . $nav
            . '</ul>'
            . '<div class="tab-content">'
            . $content
            . '</div>';
    }

    /**
     * Creates the Support Me Button...
     */
    private function loadLog($fileName) {
        global $L;
        $html = '';

        if (!file_exists($fileName)) {
            return $html . '<p>' . $L->get('Debug Log file "' . $fileName . '" doesn not exist.') . '</p>';
        }

        $logContent = file_get_contents($fileName);

        $logs = explode(PHP_EOL, trim($logContent));

        if (empty($logs)) {
            return $html . '<p>' . $L->get('Debug Log file is empty.') . '</p>';
        }

        $html .= '<div style="font-family: SFMono-Regular,Menlo,Monaco,Consolas,\"Liberation Mono\",\"Courier New\">';
        $html .= implode('<br><br>', array_map('trim', array_reverse($logs)));
        $html .= '</div>';

        return $html;
    }

    /**
     * Creates the Support Me Button...
     */
    private function payMe() {
        global $L;

        $icons = ['💸', '🥹', '☕️', '🍻', '👾', '🍕'];
        shuffle($icons);
        $html = '<div class="bg-light text-center border mt-3 p-3">';
        $html .= '<p class="mb-2">' . $L->get('Please support Mr.Bot') . '</p>';
        $html .= '<a style="background: #ffd11b;box-shadow: 2px 2px 5px #ccc;padding: 0 10px;border-radius: 50%;width: 60px;display: block;text-align: center;margin: auto;height: 60px; font-size: 40px; line-height: 60px;" href="https://www.buymeacoffee.com/iambot" target="_blank" title="Buy me a coffee...">' . $icons[0] . '</a>';
        $html .= '</div><br>';

        return $html;
    }

    /**
     * Creates the Footer
     */
    private function footer() {
        $html = '<div class="text-center mt-3 p-3" style="opacity: 0.6;">';
        $html .= '<p class="mb-2">© ' . date('Y') . ' by <a href="https://github.com/Scribilicious" target="_blank" title="Visit GitHub page...">Mr.Bot</a>, Licensed under <a href="https://raw.githubusercontent.com/Scribilicious/MIT/main/LICENSE" target="_blank" title="view license...">MIT</a>.</p>';
        $html .= '</div><br>';

        return $html;
    }
}

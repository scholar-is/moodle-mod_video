<?php

namespace videosource_vimeo;

use videosource_vimeo\videosource\vimeo;

require_once("$CFG->libdir/adminlib.php");

class admin_setting_authorize extends \admin_setting {

    public function __construct($name, $visiblename, $description) {
        parent::__construct($name, $visiblename, $description, '');
    }

    public function output_html($data, $query = '') {
        global $OUTPUT;

        $vimeo = new vimeo();
        $content = $OUTPUT->render_from_template('videosource_vimeo/authorize_button', [
            'configured' => $vimeo->is_configured(),
            'authorizationurl' => $vimeo->get_authorization_url(),
            'hasclientinfo' => get_config('videosource_vimeo', 'clientid') &&
                get_config('videosource_vimeo', 'clientsecret'),
        ]);

        return format_admin_setting(
            $this,
            $this->visiblename,
            $content,
            $this->description,
            true,
            '',
            $this->defaultsetting,
            $query
        );
    }

    public function get_setting() {
    }

    public function write_setting($data) {
    }
}

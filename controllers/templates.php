<?php

require_once 'app/controllers/plugin_controller.php';

class TemplatesController extends PluginController
{

    function before_filter(&$action, &$args)
    {
        $this->utf8decode_xhr = true;
        parent::before_filter($action, $args);
        PageLayout::addScript($this->plugin->getPluginURL() . "/assets/serienbriefe.js");
        Navigation::activateItem("/messaging/serienbriefe");
        PageLayout::setTitle(_("Serienbriefe-Templates"));
    }

    public function overview_action() {
        $this->templates = SerienbriefeTemplate::findBySQL("1=1");
    }

    public function edit_action($template_id = null)
    {
        PageLayout::setTitle(_("Template bearbeiten"));
        $this->template = new SerienbriefeTemplate($template_id);

        if (Request::get("title")) {
            $this->template['title'] = Request::get("title");
        }
        if (Request::get("subject")) {
            $this->template['subject'] = Request::get("subject");
        }
        if (Request::get("message")) {
            $this->template['message'] = Request::get("message");
        }
        if (Request::isPost() && Request::submitted("speichern")) {
            $this->template['user_id'] = $GLOBALS['user']->id;
            $this->template->store();
            PageLayout::postMessage(MessageBox::success(_("Template wurde gespeichert")));
        }
    }
}
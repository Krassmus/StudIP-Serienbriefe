<?php

require_once 'app/controllers/plugin_controller.php';

class WriteController extends PluginController
{

    function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::addScript($this->plugin->getPluginURL() . "/assets/serienbriefe.js");
        Navigation::activateItem("/messaging/serienbriefe");
    }

    public function overview_action()
    {
        $GLOBALS['SERIENBRIEF_CSV'] = Serienbriefe::getSerienbriefeData();
        if (Request::get("reset")) {
            Serienbriefe::resetSerienbriefeData();
            if (is_array($_SESSION['SERIENBRIEFE_ATTACHMENTS'])) {
                foreach ($_SESSION['SERIENBRIEFE_ATTACHMENTS'] as $file_id) {
                    FileRef::find($file_id)->delete();
                }
            }
            unset($_SESSION['SERIENBRIEFE_ATTACHMENTS']);
        }
        $db = DBManager::get();
        $this->datafields = $db->query("SELECT * FROM datafields WHERE object_type = 'user' ")->fetchAll(PDO::FETCH_ASSOC);
        if (count(Request::getArray("delete_attachment"))) {
            foreach (Request::getArray("delete_attachment") as $file_id => $value) {
                $attachment = FileRef::find($file_id);
                if ($attachment) {
                    $attachment->delete();
                }
                foreach ($_SESSION['SERIENBRIEFE_ATTACHMENTS'] as $index => $attachment_id) {
                    if ($file_id == $attachment_id) {
                        unset($_SESSION['SERIENBRIEFE_ATTACHMENTS'][$index]);
                    }
                }
            }
        }

        if (Request::submitted("delete_template")) {
            $template = new SerienbriefeTemplate(Request::get("delete_template"));
            $template->delete();
            PageLayout::postMessage(MessageBox::success(_("Template wurde gelöscht.")));
        }
        if ($_FILES['csv_file']['tmp_name']) {
            $GLOBALS['SERIENBRIEF_CSV'] = array('header' => array(), 'content' => array());
            $content = CSVImportProcessor_serienbriefe::getCSVDataFromFile($_FILES["csv_file"]['tmp_name']);
            @unlink($_FILES["csv_file"]['tmp_name']);
            $GLOBALS['SERIENBRIEF_CSV']['header'] = array_shift($content);
            foreach ($GLOBALS['SERIENBRIEF_CSV']['header'] as $key => $header_name) {
                if (!$header_name) {
                    unset($GLOBALS['SERIENBRIEF_CSV']['header'][$key]);
                }
            }
            foreach ($content as $line) {
                $data = new stdClass();
                foreach ($GLOBALS['SERIENBRIEF_CSV']['header'] as $key => $header_name) {
                    if (isset($line[$key])) {
                        $data->$header_name = $line[$key];
                    }
                }
                $data = (array) $this->getUserdata($data);
                $GLOBALS['SERIENBRIEF_CSV']['content'][] = $data;
            }
        }
        if ($_FILES['add_attachment']['tmp_name']) {
            $file = $_FILES['add_attachment'];
            $output = array(
                'name' => $file['name'],
                'size' => $file['size']
            );
            $output['message_id'] = Request::option("message_id");

            $serienbriefefolder = Folder::findOneBySQL("user_id = ? AND folder_type = 'SerienbriefeFolder'", array($GLOBALS['user']->id));

            if (!$serienbriefefolder) {
                $top_folder = Folder::findTopFolder($GLOBALS['user']->id, 'user');
                $top_folder = $top_folder->getTypedFolder();

                $serienbriefefolder = new Folder();
                $serienbriefefolder->name = _("Serienbriefe");
                $serienbriefefolder->user_id = $GLOBALS['user']->id;
                $serienbriefefolder->parent_id = $top_folder->getId();
                $serienbriefefolder->range_id = $GLOBALS['user']->id;
                $serienbriefefolder->range_type  = $top_folder->range_type;
                $serienbriefefolder->folder_type = "SerienbriefeFolder";
                $serienbriefefolder->store();
            }

            $serienbriefefolder = $serienbriefefolder->getTypedFolder();
            $error_message = _('Die hochgeladene Datei kann nicht verarbeitet werden!');

            $uploaded = FileManager::handleFileUpload(
                [
                    'tmp_name' => [$file['tmp_name']],
                    'name' => [$file['name']],
                    'size' => [$file['size']],
                    'type' => [$file['type']],
                    'error' => [$file['error']]
                ],
                $serienbriefefolder,
                $GLOBALS['user']->id
            );

            if ($uploaded['error']) {
                Pagelayout::postError($error_message, $uploaded['error']);
            }

            if (!$uploaded['files'][0] instanceof FileType) {
                PageLayout::postError($error_message);
            } else {
                $output['document_id'] = $uploaded['files'][0]->getId();
                $_SESSION['SERIENBRIEFE_ATTACHMENTS'][] = $uploaded['files'][0]->getId();
            }
        }

        if (Config::get()->SERIENBRIEFE_ATTRIBUTE_TABLE) {
            $attribute_table = Config::get()->SERIENBRIEFE_ATTRIBUTE_TABLE;
            if (strpos($attribute_table, ":") !== false) {
                list($attribute_table, $this->user_id_column) = explode(":", $attribute_table);
            } else {
                $this->user_id_column = "user_id";
            }
            $statement = DBManager::get()->prepare("
                SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_NAME = :table AND
                    TABLE_SCHEMA = :db;
            ");
            $statement->execute(array(
                'table' => $attribute_table,
                'db' => $GLOBALS['DB_STUDIP_DATABASE']
            ));
            $this->attributetable_attributes = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        } else {
            $this->attributetable_attributes = array();
        }

        $this->templates = SerienbriefeTemplate::findBySQL("1=1");
        if ($GLOBALS['SERIENBRIEF_CSV']) {
            Serienbriefe::setSerienbriefeData($GLOBALS['SERIENBRIEF_CSV']);
        }
    }

    protected function getUserdata($data)
    {
        $data = (object) $data;
        $db = DBManager::get();
        if ($data->username) {
            $data->user_id = get_userid($data->username);
        } elseif ($data->email) {
            $data->user_id = $db->query("SELECT user_id FROM auth_user_md5 WHERE Email = ".$db->quote($data->email)." ")->fetch(PDO::FETCH_COLUMN, 0);
        } else {
            foreach (DataField::getDataFields('user') as $datafield) {
                $name = $datafield['name'];
                if (isset($data->$name) && $data->$name) {
                    $entries = DatafieldEntryModel::findBySQL("`datafield_id` = :datafield_id AND `content` = :content", [
                        'datafield_id' => $datafield->getId(),
                        'content' => $data->$name
                    ]);
                    if (count($entries) === 1) { //nur wenn es genau eine Person gibt mit dieser Angabe
                        $data->user_id = $entries[0]['range_id'];
                        break;
                    }
                }
            }
        }
        $user = $db->query(
            "SELECT * " .
            "FROM auth_user_md5 " .
            "INNER JOIN user_info ON (auth_user_md5.user_id = user_info.user_id) " .
            "WHERE auth_user_md5.user_id = ".$db->quote($data->user_id)." " .
            "")->fetch(PDO::FETCH_ASSOC);
        if ($data->user_id && !$data->name) {
            $data->name = $user['Vorname'] . ' ' . $user['Nachname'];
            $data->anrede = ($user['geschlecht'] == 2 ? "Frau " : ($user['geschlecht'] == 1 ? "Herr " : "")). $user['Nachname'];
            $data->sehrgeehrte = ($user['geschlecht'] == 2 ? "Sehr geehrte Frau " : ($user['geschlecht'] == 1 ? "Sehr geehrter Herr " : "Sehr geehrte/r ")). $user['Nachname'];
        }
        if ($data->user_id && !$data->email) {
            $data->email = $user['Email'];
        }
        if ($data->user_id) {
            $df_entries = DataFieldEntry::getDataFieldEntries($data->user_id, "user");
            $this->datafields = $db->query("SELECT * FROM datafields WHERE object_type = 'user' ")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($this->datafields as $datafield) {
                if (!$data->{$datafield['name']}) {
                    $data->{$datafield['name']} = isset($df_entries[$datafield['datafield_id']]) ? $df_entries[$datafield['datafield_id']]->getValue() : '';
                }
            }
        }

        //Noch diese ganzen zusätzlichen Datenfelder wie studiengruppe oder studienort,
        //die Standortspezifisch sein können.
        NotificationCenter::postNotification("serienbriefe_get_user_data", $data);

        if (Config::get()->SERIENBRIEFE_ATTRIBUTE_TABLE) {
            $attribute_table = Config::get()->SERIENBRIEFE_ATTRIBUTE_TABLE;
            if (strpos($attribute_table, ":") !== false) {
                list($attribute_table, $user_id_column) = explode(":", $attribute_table);
            } else {
                $user_id_column = "user_id";
            }
            $statement = DBManager::get()->prepare("
                SELECT *
                FROM `".$attribute_table."`
                WHERE `".$user_id_column."` = ?
            ");
            $statement->execute(array($data->user_id));
            $attributes = $statement->fetch(PDO::FETCH_ASSOC);
            if ($attributes) {
                $data = array_merge((array)$data, $attributes);
            }
        }

        return (array) $data;
    }

    public function preview_action()
    {
        PageLayout::setTitle(_("Serienbriefe: Vorschau"));

        $GLOBALS['SERIENBRIEF_CSV'] = Serienbriefe::getSerienbriefeData();
        $this->subject = Request::get("subject");
        $this->message = Request::get("message");

        foreach ((array) $GLOBALS['SERIENBRIEF_CSV']['content'] as $line_id => $l) {
            if ($line_id == Request::option('line_id')) {
                $line = $l;
                $this->user_id = $l['user_id'];
                break;
            }
        }

        $data = new stdClass();
        foreach ((array) $GLOBALS['SERIENBRIEF_CSV']['header'] as $key => $header_name) {
            if (isset($line[$header_name])) {
                $data->$header_name = $line[$header_name];
            }
        }

        $data = (array) $this->getUserdata($data);
        $this->user_subject = $this->subject;
        $this->user_message = $this->message;

        foreach ($data as $field => $d) {
            $this->user_subject = str_replace("{{".$field."}}", $d, $this->user_subject);
            $this->user_message = str_replace("{{".$field."}}", $d, $this->user_message);
        }

    }

    public function send_action()
    {
        if (Request::isPost() && Request::get("message") && Request::get("subject")) {
            //send the message
            $count = 0;
            $_SESSION['not_delivered_users'] = array();
            $GLOBALS['MESSAGING_FORWARD_AS_EMAIL'] = !Request::int('do_not_send_as_email');
            $GLOBALS['SERIENBRIEF_CSV'] = Serienbriefe::getSerienbriefeData();
            if (is_array($GLOBALS['SERIENBRIEF_CSV']['content'])) {
                $text_original = Request::get("message");
                $subject_original = Request::get("subject");
                $tags = preg_split("/\n/", Request::get("tags"), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($GLOBALS['SERIENBRIEF_CSV']['content'] as $user_data) {
                    $user_data = $this->getUserdata($user_data);
                    if ($user_data['user_id']) {
                        $text = $text_original;
                        $subject = $subject_original;
                        foreach ($user_data as $key => $value) {
                            $subject = str_replace("{{".$key."}}", $value, $subject);
                            $text = str_replace("{{".$key."}}", $value, $text);
                        }
                        $messaging = new messaging();
                        if (count($_SESSION['SERIENBRIEFE_ATTACHMENTS'])) {
                            $range_id = md5(uniqid());
                            $messaging->provisonal_attachment_id = $range_id;
                            $message_top_folder = MessageFolder::findTopFolder($range_id) ?: MessageFolder::createTopFolder($range_id);

                            foreach ($_SESSION['SERIENBRIEFE_ATTACHMENTS'] as $file_id) {
                                $document = new StandardFile(new FileRef($file_id));
                                $new_file_ref = FileManager::copyFile(
                                    $document,
                                    $message_top_folder,
                                    User::findCurrent()
                                );
                            }
                        }

                        $success = $messaging->insert_message(
                            $text,
                            get_username($user_data['user_id']),
                            $GLOBALS['user']->id,
                            '',
                            $range_id,
                            '',
                            '',
                            $subject,
                            1,
                            'normal',
                            $tags,
                            false
                        );
                        if ($success) {
                            $count++;
                        } else {
                            PageLayout::postMessage(MessageBox::error(sprintf("Nachricht konnte nicht an %s versendet werden.", $user_data['email'])));
                        }
                    } else {
                        $_SESSION['not_delivered_users'][] = $user_data;
                    }
                }
            }
            if (is_array($_SESSION['SERIENBRIEFE_ATTACHMENTS'])) {
                foreach ($_SESSION['SERIENBRIEFE_ATTACHMENTS'] as $file_id) {
                    FileRef::find($file_id)->delete();
                }
                //remove folder if it is empty:
                $serienbriefefolder = Folder::findOneBySQL("user_id = ? AND folder_type = 'SerienbriefeFolder'", array($GLOBALS['user']->id));
                $serienbriefefolder = $serienbriefefolder->getTypedFolder();
                if (!count($serienbriefefolder->getFiles())) {
                    $serienbriefefolder->delete();
                }
            }
            unset($_SESSION['SERIENBRIEFE_ATTACHMENTS']);
            if ($count > 0) {
                PageLayout::postMessage(MessageBox::success(sprintf("Nachricht wurde an %s Personen versendet.", $count)));
            }
            if (count($_SESSION['not_delivered_users']) > 0) {
                PageLayout::postMessage(MessageBox::info(sprintf("An %s Personen wurde die Nachricht nicht versendet. %sBericht dazu%s.", count($_SESSION['not_delivered_users']), '<a href="'.PluginEngine::getLink($this->plugin, array(), 'users_not_delivered_csv').'">', '</a>')));
            }
        }
        $this->response->add_header("X-Location", PluginEngine::getURL($this->plugin, array(), "write/overview"));
        $this->render_nothing();
    }
}

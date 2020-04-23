
<? if (is_array(Serienbriefe::getSerienbriefeData()) && $GLOBALS['SERIENBRIEF_CSV']['header'] && count($GLOBALS['SERIENBRIEF_CSV']['header']) && !in_array("username", $GLOBALS['SERIENBRIEF_CSV']['header']) && !in_array("email", $GLOBALS['SERIENBRIEF_CSV']['header']) && !in_array("user_id", $GLOBALS['SERIENBRIEF_CSV']['header'])) : ?>
    <?= MessageBox::error("Die hochgeladenen Empfägerdaten enthalten nicht das Feld <i>username</i> oder <i>email</i>.") ?>
<? endif ?>

<form name="message" action="<?= URLHelper::getLink("?", array('reset' => 0)) ?>" method="post" enctype="multipart/form-data">

    <h1><?= _("Serienbrief schreiben") ?></h1>
    <div style="float: right; width: 23%;" id="replacement_div" class="sb_box">
        <h4><?= _("Mögliche Ersetzungen") ?></h4>
        <ul style="list-style-type: none; padding: 0px; max-height: 60vh; overflow: auto;" id="replacements">
            <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{name}}</a></li>
            <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{anrede}}</a></li>
            <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{sehrgeehrte}}</a></li>
            <!--<li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{studiengruppe}}</a></li>
            <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{studienort}}</a></li>-->
            <? foreach ($datafields as $datafield) : ?>
                <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{<?= htmlReady($datafield['name']) ?>}}</a></li>
            <? endforeach ?>
            <? if (is_array($GLOBALS['SERIENBRIEF_CSV']['header'])) foreach ($GLOBALS['SERIENBRIEF_CSV']['header'] as $header_name) : ?>
                <li>
                    <a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{<?= htmlReady($header_name) ?>}}</a>
                </li>
            <? endforeach ?>
            <? foreach ($attributetable_attributes as $attribute) : ?>
                <? if ($user_id_column !== $attribute) : ?>
                <li>
                    <a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{<?= htmlReady($attribute) ?>}}</a>
                </li>
                <? endif ?>
            <? endforeach ?>
        </ul>
    </div>

    <?
    $text = "";
    $subject = "";
    if (Request::get("load_template")) {
        foreach ($templates as $template) {
            if (Request::get("load_template") === $template->getId()) {
                $text = $template['message'];
                $subject = $template['subject'];
            }
        }
    } else {
        if (Request::get("message")) {
            $text = Request::get("message");
        }
        if (Request::get("subject")) {
            $subject = Request::get("subject");
        }
    }
    ?>
    <input type="text" style="width: 68%;" name="subject" id="subject" value="<?= htmlReady($subject) ?>" placeholder="<?= _("Betreff") ?>">
    <textarea style="width: 68%; height: 60vh;" id="message" name="message" placeholder="<?= _("Nachrichtenkörper") ?>"><?= htmlReady($text) ?></textarea>

    <div style="margin: 20px; text-align: center; clear: both;">
        <label style="cursor: pointer;">
            <input type="file" name="add_attachment" style="display: none;" onChange="jQuery(this).closest('form').submit();">
            <?= Icon::create("staple", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
            <?= _("Datei für alle anhängen") ?>
        </label>

        <? if (is_array($_SESSION['SERIENBRIEFE_ATTACHMENTS'])) : ?>
            <ul class="clean attachments" style=" margin-top: 13px;">
                <? foreach ($_SESSION['SERIENBRIEFE_ATTACHMENTS'] as $file_id) : ?>
                    <li>
                        <? $document = new FileRef($file_id) ?>
                        <input type="checkbox" name="delete_attachment[<?= htmlReady($file_id) ?>]" value="1" id="delete_<?= htmlReady($file_id) ?>" style="display: none;">
                        <span>
                            <?= Icon::create("staple", "info")->asImg(20, array('class' => "text-bottom")) ?>
                            <?= htmlReady($document['name']) ?>
                        </span>
                        <label for="delete_<?= htmlReady($file_id) ?>" style="cursor: pointer;">
                            <?= Icon::create("trash", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
                        </label>
                    </li>
                <? endforeach ?>
            </ul>
        <? endif ?>
    </div>

    <div>
        <?= _("Schlagworte") ?>:
        <ul class="clean tags">
            <? foreach ((array) $_SESSION['SERIENBRIEFE_TAGS'] as $tag) : ?>
            <li>
                <span><?= htmlReady($tag) ?></span>
                <input type="hidden" name="tag[]" value="<?= htmlReady($tag) ?>">
                <a href="" class="deletetag"></a>
            </li>
            <? endforeach ?>
        </ul>
        <input type="text" id="new_tag">
        <a href="" class="add_tag">
            <?= Icon::create("add", "clickable")->asImg(16, ['class' => "text-bottom"]) ?>
        </a>
    </div>

    <div style="margin: 20px; text-align: center; clear: both;">
        <label style="cursor: pointer; display: block;">
            <input type="file" name="csv_file" style="display: none;" onChange="jQuery(this).closest('form').submit();" id="csv_file">
        </label>

        <div style="text-align: center;">
            <?= \Studip\LinkButton::create(_("Vorschau"), "#", array("onclick" => "STUDIP.serienbriefe.syncValues(); jQuery('#preview_button').trigger('click'); return false;")) ?>
            <?= \Studip\LinkButton::create(_("Zurücksetzen"), URLHelper::getURL("?", array('reset' => 1))) ?>
        </div>
    </div>

    <? if (is_array($GLOBALS['SERIENBRIEF_CSV']['content']) && count($GLOBALS['SERIENBRIEF_CSV']['content'])) : ?>
        <div>
            <h2><?= _("Teilnehmer und Teilnehmerdaten") ?></h2>
            <table id="datatable" class="default">
                <thead>
                <tr>
                    <th width="20px"></th>
                    <? foreach ($GLOBALS['SERIENBRIEF_CSV']['header'] as $header_name) : ?>
                        <th>{{<?= htmlReady($header_name) ?>}}</th>
                    <? endforeach ?>
                </tr>
                </thead>
                <tbody>
                <? $some_users_correct = false ?>
                <? foreach ($GLOBALS['SERIENBRIEF_CSV']['content'] as $line) : ?>
                    <? !$line['user_id'] || $some_users_correct = true ?>
                    <tr
                        id="user_<?= $line['user_id'] ?>"
                        class="<?= ($line['user_id'] ? " correct" : " unfinished") ?>"
                    >
                        <td>
                            <?= !$line['user_id']
                                ? Icon::create("decline","status-red")->asImg(20, array('title' => _("Nutzer konnte nicht anhand von Username oder Email identifiziert werden."), 'class' => "text-top"))
                                : "" ?>
                        </td>
                        <? foreach ($GLOBALS['SERIENBRIEF_CSV']['header'] as $header_name) : ?>
                            <td data="<?= htmlReady($header_name) ?>">
                                <?= htmlReady($line[$header_name]) ?>
                            </td>
                        <? endforeach ?>
                        <? $line_utf8 = array();
                        foreach ($line as $key => $value) {
                            unset($line_utf8[$key]);
                            $line_utf8[$key] = $value;
                        }
                        ?>
                        <td style="display: none;" class="user_data"><?= htmlReady(json_encode($line_utf8)) ?></td>
                    </tr>
                <? endforeach ?>
                </tbody>
            </table>
        </div>

    <? endif ?>

</form>

<form action="<?= PluginEngine::getLink($plugin, array(), "write/preview") ?>"
      data-dialog
      style="display: none;"
      method="post">
    <input type="text" id="preview_subject" name="subject">
    <textarea id="preview_message" name="message"></textarea>
    <textarea id="tags" name="tags"><?= htmlReady(implode("\n", (array) $_SESSION['SERIENBRIEFE_TAGS'])) ?></textarea>
    <button id="preview_button"></button>
    <button id="save_template_button" formaction="<?= PluginEngine::getLink($plugin, array(), "templates/edit") ?>"></button>
</form>

<?

if (Request::get("edit_template")) : ?>
    <script>
        jQuery(STUDIP.serienbriefe.adminTemplatesDialog);
    </script>
<? endif;

$actions = new ActionsWidget();
if ($some_users_correct) {
    $actions->addLink(
        _("Vorschau"),
        PluginEngine::getURL($plugin, array(), "write/preview"),
        Icon::create("play", "clickable"),
        array("onclick" => "jQuery('#preview_message').val(jQuery('#message').val()); jQuery('#preview_subject').val(jQuery('#subject').val()); jQuery('#preview_button').trigger('click'); return false;")
    );
}
$actions->addLink(
    _("CSV-Datei auswählen"),
    "#2",
    Icon::create("file-excel", "clickable"),
    array("onclick" => "jQuery('#csv_file').trigger('click'); return false;")
);
Sidebar::Get()->addWidget($actions);


Helpbar::Get()->addPlainText(
    _("Markup"),
    _("Links im Textfeld können Sie den Brief eingeben. Wörter in {{geschweiften}} Klammern werden als spezielle Variablen betrachtet, die Stud.IP durch die gewünschte Information ersetzt."),
    Icon::create("info", "info_alt")
);
Helpbar::Get()->addPlainText(
    _("CSV-Daten"),
    _("Laden Sie eine CSV-Datei hoch, in der mindestens das Feld \"username\" oder \"email\" vorkommt, damit Stud.IP die Briefe auch versenden kann."),
    Icon::create("info", "info_alt")
);

$templates_select = '<select id="template_action" onChange="STUDIP.serienbriefe.showTemplates();" style="max-width: 100%;">';
$templates_select .= '<option value="">Template ...</option>';
if ($templates) {
    foreach ($templates as $template) {
        $templates_select .=
            '<option value="'.htmlReady($template->getId()).'" title="'.htmlReady($template['subject']).'">'
            .'&nbsp;-&nbsp;&nbsp;'.htmlReady($template['title']).'</option>';
    }
}
$templates_select .= '<option value="admin" onClick="STUDIP.serienbriefe.showTemplates();">'._("Templateverwaltung").'</option>';
$templates_select .= '<option value="save" onClick="STUDIP.serienbriefe.showTemplates();">'._("Dies als Template speichern").'</option></select>';

$widget = new SidebarWidget();
$widget->setTitle(_("Templates"));
$widget->addElement(new WidgetElement($templates_select));
Sidebar::Get()->addWidget($widget);

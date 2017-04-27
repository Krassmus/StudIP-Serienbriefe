<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
?>

    <style>
        a {
            cursor: pointer;
        }
        ul#fehler_protokoll {
            margin: 0px;
            padding: 2px;
            list-style-type: none;
            margin-bottom: 20px;
        }
        ul#fehler_protokoll > li {
            background-color: #aa0022;
            color: white;
            margin: 5px;
            padding: 3px;
            font-size: 1.2em;
        }
        .sb_box {
            margin: 12px;
            margin-top: 7px;
            margin-bottom: 7px;
            padding: 4px;
            border: 3px solid white;
            background-color: #eaeaea;
            border-radius: 10px;
            box-shadow: 0px 0px 4px #c0c0c0;
            font-size: 1.2em;

        }
        #replacements {
            text-indent: -12px;
        }
        #replacements li {
            padding-left: 12px;
        }
        #submit_button {
            margin: 20px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        #preview_text {

        }
        input:-moz-placeholder {
            color: rgba(0,0,0,0.4);
        }
        input::-webkit-input-placeholder {
            color: rgba(0,0,0,0.4);
        }
        textarea:-moz-placeholder {
            color: rgba(0,0,0,0.4);
        }
        textarea::-webkit-input-placeholder {
            color: rgba(0,0,0,0.4);
        }

        .attachments input[type=checkbox]:checked + span {
            text-decoration: line-through;
        }

    </style>


<? if (is_array($GLOBALS['SERIENBRIEF_CSV']) && count($GLOBALS['SERIENBRIEF_CSV']['header']) && !in_array("username", $GLOBALS['SERIENBRIEF_CSV']['header']) && !in_array("email", $GLOBALS['SERIENBRIEF_CSV']['header']) && !in_array("user_id", $GLOBALS['SERIENBRIEF_CSV']['header'])) : ?>
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
    if (Request::submitted("notenbekanntgabe")) {
        $notenbekanntgabe = Request::int("notenbekanntgabe");
    } elseif(Request::submitted("notenbekanntgabe_template")) {
        $notenbekanntgabe = Request::int("notenbekanntgabe_template");
    } else {
        $notenbekanntgabe = true;
    }
    if (Request::get("load_template")) {
        foreach ($templates as $template) {
            if (Request::get("load_template") === $template->getId()) {
                $text = $template['message'];
                $subject = $template['subject'];
                $notenbekanntgabe = $template['notenbekanntgabe'];
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
            <?= Icon::create("add", "clickable")->asImg(20, array('class' => "text-bottom")) ?>
            <?= _("Datei für alle anhängen") ?>
        </label>

        <? if (is_array($_SESSION['SERIENBRIEFE_ATTACHMENTS'])) : ?>
            <ul class="clean attachments" style=" margin-top: 13px;">
                <? foreach ($_SESSION['SERIENBRIEFE_ATTACHMENTS'] as $file_id) : ?>
                    <li>
                        <? $document = new StudipDocument($file_id) ?>
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

    <div style="margin: 20px; text-align: center; clear: both;">
        <label style="cursor: pointer; display: block;">
            <input type="file" name="csv_file" style="display: none;" onChange="jQuery(this).closest('form').submit();" id="csv_file">
        </label>
        <input type="hidden" name="notenbekanntgabe" id="notenbekanntgabe_hidden" value="<?= $notenbekanntgabe ? 1 : 0 ?>">

        <div style="text-align: center;">
            <?= \Studip\LinkButton::create(_("Vorschau"), "#", array("onclick" => "jQuery('#preview_message').val(jQuery('#message').val()); jQuery('#preview_subject').val(jQuery('#subject').val()); jQuery('#preview_button').trigger('click'); return false;")) ?>
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
                    <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                        <th></th>
                    <? endif ?>
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
                        class="<?= ($line['user_id'] ? " correct" : " unfinished").((!get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD") || $line[get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")]) ? " allowed" : " denied") ?>"
                    >
                        <td>
                            <?= !$line['user_id']
                                ? Assets::img("icons/16/red/decline.png", array('title' => _("Nutzer konnte nicht anhand von Username oder Email identifiziert werden."), 'class' => "text-top"))
                                : "" ?>
                        </td>
                        <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                            <td>
                                <?= !$line[Config::get()->SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD]
                                        ? Assets::img("icons/16/red/decline.png", array('title' => _("Nutzer ist nicht einverstanden, seine Noten per Mail zu bekommen."), 'class' => "text-top"))
                                        : "" ?>
                            </td>
                        <? endif ?>
                        <? foreach ($GLOBALS['SERIENBRIEF_CSV']['header'] as $header_name) : ?>
                            <td data="<?= htmlReady($header_name) ?>">
                                <?= htmlReady($line[$header_name]) ?>
                            </td>
                        <? endforeach ?>
                        <? $line_utf8 = array();
                        foreach ($line as $key => $value) {
                            unset($line_utf8[$key]);
                            $line_utf8[studip_utf8encode($key)] = studip_utf8encode($value);
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

<form action="<?= PluginEngine::getLink($plugin, array(), "write/preview") ?>" data-dialog style="display: none;">
    <input type="text" id="preview_subject" name="subject">
    <textarea id="preview_message" name="message"></textarea>
    <button id="preview_button"></button>
    <button id="save_template_button" formaction="<?= PluginEngine::getLink($plugin, array(), "templates/edit") ?>"></button>
</form>

<?

if (Request::get("edit_template")) : ?>
    <script>
        jQuery(STUDIP.serienbriefe.adminTemplatesDialog);
    </script>
<? endif;

Sidebar::Get()->setImage("sidebar/mail-sidebar.png");

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
    "icons/16/white/info"
);
Helpbar::Get()->addPlainText(
    _("CSV-Daten"),
    _("Laden Sie eine CSV-Datei hoch, in der mindestens das Feld \"username\" oder \"email\" vorkommt, damit Stud.IP die Briefe auch versenden kann."),
    "icons/16/white/info"
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

/*
if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) {
    $infobox['content'][1]['eintrag'][] = array(
        'icon' => "icons/16/black/doctoral_cap.png",
        'text' =>
            '<label title="'._("Notenbekanntgaben werden nur an Nutzer verschickt, die diesem Verbreitungsweg zugestimmt haben.").'">'
            ._("Serienbrief ist Notenbekanntgabe")
            .'<input type="checkbox" value="1" id="notenbekanntgabe" name="notenbekanntgabe" class="text-bottom"'.($notenbekanntgabe ? " checked" : "").' onCHange="jQuery('."'#notenbekanntgabe_hidden, #notenbekanntgabe_delivery'".').val(this.checked ? 1 : 0);">'
            .'</label>'
    );
}

*/

if (count($infobox['content'][1]['eintrag']) === 0) {
    unset($infobox['content'][1]);
}
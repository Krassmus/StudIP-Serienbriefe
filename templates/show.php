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
    table.active_table {
        border-collapse: collapse;
        margin: 10px;
    }
    table.active_table > thead > tr > th {
        padding: 5px;
        border: 1px solid lightgrey;
        border-bottom: 1px solid grey;
        background-image: none;
    }
    table.active_table > tbody > tr > td {
        padding: 5px;
        border: 1px solid lightgrey;
    }
    table.active_table > tbody > tr:hover > td {
        background-color: #eeeeee;
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

</style>


<? foreach ($msg as $message) {
    $type = $message[0];
    $message = $message[1];
    echo MessageBox::$type($message);
} ?>

<? if (is_array($_SESSION['SERIENBRIEF_CSV']) && count($_SESSION['SERIENBRIEF_CSV']['header']) && !in_array("username", $_SESSION['SERIENBRIEF_CSV']['header']) && !in_array("email", $_SESSION['SERIENBRIEF_CSV']['header'])) : ?>
    <?= MessageBox::error("Die hochgeladenen Empfägerdaten enthalten nicht das Feld <i>username</i> oder <i>email</i>.") ?>
<? endif ?>

<form name="message" action="<?= URLHelper::getLink("?", array('reset' => 0)) ?>" method="post" enctype="multipart/form-data">
    
<h2><?= _("Serienbrief erstellen") ?></h2>
<div style="float: right; width: 23%;" id="replacement_div" class="sb_box">
    <h4><?= _("Mögliche Ersetzungen") ?></h4>
    <ul style="list-style-type: none; padding: 0px;" id="replacements">
        <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{name}}</a></li>
        <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{anrede}}</a></li>
        <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{sehrgeehrte}}</a></li>
        <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{studiengruppe}}</a></li>
        <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{studienort}}</a></li>
        <? foreach ($datafields as $datafield) : ?>
        <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{<?= htmlReady($datafield['name']) ?>}}</a></li>
        <? endforeach ?>
        <? if (is_array($_SESSION['SERIENBRIEF_CSV']['header'])) foreach ($_SESSION['SERIENBRIEF_CSV']['header'] as $header_name) : ?>
        <li><a onClick="STUDIP.serienbriefe.insertAtCursor(jQuery(this).text()); return false;">{{<?= htmlReady($header_name) ?>}}</a></li>
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
<textarea style="width: 68%; height: 300px;" id="message" name="message" placeholder="<?= _("Nachrichtenkörper") ?>"><?= htmlReady($text) ?></textarea>

<div style="margin: 20px; text-align: center; clear: both;">
        <input type="file" name="csv_file" class="text-bottom">
        <?= makebutton("absenden", "input") ?>
        <input type="hidden" name="notenbekanntgabe" id="notenbekanntgabe_hidden" value="<?= $notenbekanntgabe ? 1 : 0 ?>">
        <a href="?reset=1"><?= makebutton("zuruecksetzen") ?></a>
</div>

<? if (is_array($_SESSION['SERIENBRIEF_CSV']['content']) && count($_SESSION['SERIENBRIEF_CSV']['content'])) : ?>
<div>
    <h2><?= _("Teilnehmer und Teilnehmerdaten") ?></h2>
    <table id="datatable" class="active_table">
        <thead>
            <tr>
                <th></th>
                <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                <th></th>
                <? endif ?>
                <? foreach ($_SESSION['SERIENBRIEF_CSV']['header'] as $header_name) : ?>
                <th>{{<?= htmlReady($header_name) ?>}}</th>
                <? endforeach ?>
            </tr>
        </thead>
        <tbody>
            <? $some_users_correct = false ?>
            <? foreach ($_SESSION['SERIENBRIEF_CSV']['content'] as $line) : ?>
            <? !$line['user_id'] || $some_users_correct = true ?>
            <tr 
                    id="user_<?= $line['user_id'] ?>" 
                    class="<?= ($line['user_id'] ? " correct" : " unfinished").((!get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD") || $line[get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")]) ? " allowed" : " denied") ?>"
                    >
                <td><?= !$line['user_id'] ? Assets::img("icons/16/red/decline.png", array('title' => _("Nutzer konnte nicht anhand von Username oder Email identifiziert werden."), 'class' => "text-top")) : "" ?></td>
                <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                <td><?= !$line[get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")] ? Assets::img("icons/16/red/decline.png", array('title' => _("Nutzer ist nicht einverstanden, seine Noten per Mail zu bekommen."), 'class' => "text-top")) : "" ?></td>
                <? endif ?>
                <? foreach ($_SESSION['SERIENBRIEF_CSV']['header'] as $header_name) : ?>
                <td data="<?= htmlReady($header_name) ?>"><?= htmlReady($line[$header_name]) ?></td>
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

<? if ($some_users_correct) : ?>
<div style="text-align: center;">
    <a onClick="STUDIP.serienbriefe.preview(true)"><?= makebutton("vorschau") ?></a>
</div>
<? endif ?>

<? endif ?>

</form>

<div id="preview_window" style="display: none;">
    <form action="?" method="post">
        <ul id="fehler_protokoll"></ul>
        <div style="text-align: center;">
            <label><?= _("Vorschau für ") ?>
                <select id="preview_user" onChange="STUDIP.serienbriefe.preview(false);">
                    <? foreach ($_SESSION['SERIENBRIEF_CSV']['content'] as $user_data) :
                        if ($user_data['user_id']) : ?>
                    <option value="<?= htmlReady($user_data['user_id']) ?>"><?= htmlReady($user_data['name']) ?></option>
                        <? endif;
                    endforeach ?>
                </select>
            </label>
        </div>
        <h3><?= _("Betreff") ?>: <span id="preview_subject"></span></h3>
        <div id="preview_text" class="sb_box"></div>
        <div id="submit_button">
            <input type="hidden" name="subject_delivery" id="subject_delivery">
            <input type="hidden" name="notenbekanntgabe" id="notenbekanntgabe_delivery" value="<?= $notenbekanntgabe ? 1 : 0 ?>">
            <textarea style="display: none" name="message_delivery" id="message_delivery"></textarea>
            <?= makebutton("abschicken", "input") ?>
            <a href="" onClick="jQuery('#preview_window').dialog('close'); return false;"><?= makebutton("abbrechen", "img") ?></a>
        </div>
    </form>
</div>

<div id="templates_window" style="display: none;">
    <div id="add_new_template" class="sb_box">
        <form action="?" method="post">
            <? $is_note = get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD") ?>
            <? if (Request::option("edit_template")) { 
                   $edit_template = new serienbriefe_templates(Request::get("edit_template")); 
               } ?>
            <div style="display: inline-block; vertical-align: middle; width: <?= $is_note ? "20" : "25" ?>%;">
                <input type="hidden" name="template_id" value="<?= Request::get("edit_template") ? $edit_template->getId() : "new" ?>">
                <label>
                    <?= _("Name des Templates") ?>
                    <br><input title="<?= _("Geben Sie einen Namen ein") ?>" type="text" required="required" name="title" style="width: 95%;" value="<?= Request::get("edit_template") ? htmlReady($edit_template['title']) : "" ?>">
                </label>
            </div>
            <div style="display: inline-block; vertical-align: middle; width: <?= $is_note ? "20" : "25" ?>%;">
                <label>
                    <?= _("Betreff") ?>
                    <br><input type="text" name="subject" style="width: 95%;" value="<?= Request::get("edit_template") ? htmlReady($edit_template['subject']) : "" ?>">
                </label>
            </div>
            <? if ($is_note) : ?>
            <div style="display: inline-block; vertical-align: middle; width: 15%;">
                <label>
                    <?= _("Notenbekanntgabe") ?>
                    <br><input type="checkbox" name="notenbekanntgabe_template" value="1"<?= (Request::get("edit_template") && $edit_template['notenbekanntgabe']) ? " checked" : "" ?>>
                </label>
            </div>
            <? else : ?>
            <input type="hidden" name="notenbekanntgabe_template" value="<?= Request::get("edit_template") && $edit_template['notenbekanntgabe'] ? "1" : "0" ?>">
            <? endif ?>
            <div style="display: inline-block; vertical-align: middle; width: <?= $is_note ? "42" : "48" ?>%;">
                <textarea name="message" style="width: 95%; height: 200px; font-size: 0.7em;"><?= Request::get("edit_template") ? htmlReady($edit_template['message']) : "" ?></textarea>
            </div>
            <div style="text-align: center;">
                <?= makebutton("speichern", "input") ?>
            </div>
        </form>
    </div>
    
    <div id="all_templates">
        <? if ($templates && count($templates) > 0) : ?>
        <table class="active_table" style="width: 97%;">
            <thead>
                <tr>
                    <th><?= _("Template") ?></th>
                    <th><?= _("Betreff") ?></th>
                    <th><?= _("Nachrichtenkörper") ?></th>
                    <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                    <th><?= _("Notenbekanntgabe") ?></th>
                    <? endif ?>
                    <th><?= _("Autor") ?></th>
                    <th><?= _("Letzte Änderung") ?></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($templates as $template) : ?>
                <tr>
                    <td><?= htmlReady($template['title']) ?></td>
                    <td><?= htmlReady($template['subject']) ?></td>
                    <td title="<?= htmlReady($template['message']) ?>"><?= htmlReady(mila($template['message']), 250) ?></td>
                    <td><?= $template['notenbekanntgabe'] ? Assets::img("icons/16/grey/accept", array('title' => _("Serienbrief ist Notenbekanntgabe"))) : Assets::img("icons/16/grey/decline", array('title' => _("Serienbrief ist keine Notenbekanntgabe"))) ?></td>
                    <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                    <td><?= htmlReady(get_fullname($template['user_id'])) ?></td>
                    <? endif ?>
                    <td><?= date("j.n.Y", $template['chdate']) ?></td>
                    <td><a title="<?= _("Template verwenden") ?>" onClick="STUDIP.serienbriefe.loadTemplate('<?= $template->getId() ?>')"><?= Assets::img("icons/16/blue/arr_1right.png") ?></a></td>
                    <td><a title="<?= _("Template bearbeiten") ?>" onClick="STUDIP.serienbriefe.editTemplate('<?= $template->getId() ?>')"><?= Assets::img("icons/16/blue/edit.png") ?></a></td>
                    <td><a title="<?= _("Template löschen") ?>" onClick="STUDIP.serienbriefe.deleteTemplate('<?= $template->getId() ?>')"><?= Assets::img("icons/16/blue/trash.png") ?></a></td>
                </tr>
                <? endforeach ?>
            </tbody>
        </table>
        <? endif ?>
    </div>
</div>


<? 

if (Request::get("edit_template")) : ?>
<script>
    jQuery(STUDIP.serienbriefe.adminTemplatesDialog);
</script>
<? endif;

$infobox = array(
    'picture' => $GLOBALS['ABSOLUTE_URI_STUDIP'].$plugin->getPluginPath()."/assets/letterbox_bw.jpg",
    'content' => array(
        array(
            'kategorie' => _("Information"),
            'eintrag' => array(
                array(
                    'icon' => "icons/16/black/info", 
                    'text' => _("Links im Textfeld können Sie den Brief eingeben. Wörter in {{geschweiften}} Klammern werden als spezielle Variablen betrachtet, die Stud.IP durch die gewünschte Information ersetzt.")
                ),
                array(
                    'icon' => "icons/16/black/info", 
                    'text' => _("Laden Sie eine CSV-Datei hoch, in der mindestens das Feld \"username\" oder \"email\" vorkommt, damit Stud.IP die Briefe auch versenden kann.")
                )
            )
        ),
        array(
            'kategorie' => _("Aktionen"),
            'eintrag' => array()
        )
    )
);

if ($some_users_correct) {
    $infobox['content'][1]['eintrag'][] = array(
        'icon' => "icons/16/black/play", 
        'text' => '<a onClick="STUDIP.serienbriefe.preview(true)">'._("Vorschau").'</a>'
    );
}

if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) {
    $infobox['content'][1]['eintrag'][] = array(
        'icon' => "icons/16/black/doctoral_cap", 
        'text' => 
            '<label title="'._("Notenbekanntgaben werden nur an Nutzer verschickt, die diesem Verbreitungsweg zugestimmt haben.").'">'
                ._("Serienbrief ist Notenbekanntgabe")
                .'<input type="checkbox" value="1" id="notenbekanntgabe" name="notenbekanntgabe" class="text-bottom"'.($notenbekanntgabe ? " checked" : "").' onCHange="jQuery('."'#notenbekanntgabe_hidden, #notenbekanntgabe_delivery'".').val(this.checked ? 1 : 0);">'
            .'</label>'
    );
}

$templates_select = '<select id="template_action" onChange="STUDIP.serienbriefe.showTemplates();">';
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
$infobox['content'][1]['eintrag'][] = array(
    'icon' => "icons/16/black/staple", 
    'text' => $templates_select
);

if (count($infobox['content'][1]['eintrag']) === 0) {
    unset($infobox['content'][1]);
}
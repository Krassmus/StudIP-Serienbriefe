<form action="<?= PluginEngine::getLink($plugin, array(), "write/preview") ?>" method="post" data-dialog>
    <ul id="fehler_protokoll"></ul>
    <div style="text-align: center;">
        <label><?= _("Vorschau für ") ?>
            <select name="user_id" onChange="jQuery(this).closest('form').submit();">
                <? foreach ($GLOBALS['SERIENBRIEF_CSV']['content'] as $user_data) :
                    if ($user_data['user_id']) : ?>
                        <option value="<?= htmlReady($user_data['user_id']) ?>"<?= $user_data['user_id'] === Request::option("user_id") ? " selected" : "" ?>>
                            <?= htmlReady($user_data['name'] ?: get_fullname($user_data['user_id'])) ?>
                        </option>
                    <? endif;
                endforeach ?>
            </select>
        </label>
    </div>

    <h3><?= _("Betreff") ?>: <span id="preview_subject"><?= htmlReady($user_subject) ?></span></h3>
    <div id="preview_text" class="sb_box"><?= nl2br(htmlReady($user_message)) ?></div>

    <div id="submit_button">

        <input type="hidden" name="subject" id="subject_delivery" value="<?= htmlReady($subject) ?>">
        <textarea style="display: none" name="message" id="message_delivery"><?= htmlReady($message) ?></textarea>

        <label>
            <input type="checkbox" name="do_not_send_as_email" value="1"<?= Request::get("do_not_send_as_email") ? " checked" : "" ?>>
            <?=_("Nachricht NICHT per E-mail weiterleiten")?>
        </label>

        <div data-dialog-button>
            <?= \Studip\Button::create(_("Abschicken"), "abschicken", array('formaction' => PluginEngine::getURL($plugin, array(), "write/send"))) ?>
        </div>
    </div>
</form>
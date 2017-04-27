<form action="<?= PluginEngine::getLink($plugin, array(), "templates/edit/".$template->getId()) ?>"
      method="post"
      class="default"
      data-dialog>
    <? $is_note = get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD") ?>

    <label>
        <?= _("Name des Templates") ?>
        <input title="<?= _("Geben Sie einen Namen ein") ?>" type="text" required="required" name="title" value="<?= htmlReady($template['title']) ?>">
    </label>

    <label>
        <?= _("Betreff") ?>
        <br><input type="text" name="subject" value="<?= htmlReady($template['subject']) ?>">
    </label>
    <? if ($is_note) : ?>
        <label>
            <input type="checkbox" name="notenbekanntgabe_template" value="1"<?= $template['notenbekanntgabe'] ? " checked" : "" ?>>
            <?= _("Notenbekanntgabe") ?>
        </label>
    <? else : ?>
        <input type="hidden" name="notenbekanntgabe_template" value="<?= $template['notenbekanntgabe'] ? "1" : "0" ?>">
    <? endif ?>

    <label>
        <?= _("Nachricht") ?>
        <textarea name="message" style="height: 200px;"><?= htmlReady($template['message']) ?></textarea>
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), "speichern") ?>
        <?= \Studip\LinkButton::create(_("Laden"), "#", array('onclick' => "STUDIP.serienbriefe.loadTemplate('". $template->getId()."'); return false;")) ?>
    </div>
</form>
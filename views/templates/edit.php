<form action="<?= PluginEngine::getLink($plugin, array(), "templates/edit/".$template->getId()) ?>"
      method="post"
      class="default"
      data-dialog>

    <label>
        <?= _("Name des Templates") ?>
        <input title="<?= _("Geben Sie einen Namen ein") ?>" type="text" required="required" name="title" value="<?= htmlReady($template['title']) ?>">
    </label>

    <label>
        <?= _("Betreff") ?>
        <br><input type="text" name="subject" value="<?= htmlReady($template['subject']) ?>">
    </label>

    <label>
        <?= _("Nachricht") ?>
        <textarea name="message" style="height: 200px;"><?= htmlReady($template['message']) ?></textarea>
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_("Speichern"), "speichern") ?>
        <?= \Studip\LinkButton::create(_("Laden"), "#", array('onclick' => "STUDIP.serienbriefe.loadTemplate('". $template->getId()."'); return false;")) ?>
    </div>
</form>
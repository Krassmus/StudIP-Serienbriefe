<? if ($templates && count($templates) > 0) : ?>
    <table class="default">
        <thead>
        <tr>
            <th><?= _("Template") ?></th>
            <th><?= _("Betreff") ?></th>
            <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                <th><?= _("Notenbekanntgabe") ?></th>
            <? endif ?>
            <th><?= _("Autor") ?></th>
            <th><?= _("Letzte Änderung") ?></th>
            <th class="actions"></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($templates as $template) : ?>
            <tr>
                <? $author = User::find($template['user_id']) ?>
                <td><?= htmlReady($template['title']) ?></td>
                <td><?= htmlReady($template['subject']) ?></td>
                <td><?= htmlReady($author ? $author->getFullName() : _("unbekannt")) ?></td>
                <? if (get_config("SERIENBRIEFE_NOTENBEKANNTGABE_DATENFELD")) : ?>
                    <td><?= $template['notenbekanntgabe'] ? Assets::img("icons/16/grey/accept.png", array('title' => _("Serienbrief ist Notenbekanntgabe"))) : Assets::img("icons/16/grey/decline.png", array('title' => _("Serienbrief ist keine Notenbekanntgabe"))) ?></td>
                <? endif ?>
                <td><?= date("j.n.Y", $template['chdate']) ?></td>
                <td class="actions">
                    <a title="<?= _("Template verwenden") ?>" onClick="STUDIP.serienbriefe.loadTemplate('<?= $template->getId() ?>');">
                        <?= Assets::img("icons/20/blue/play") ?>
                    </a>
                    <a href="<?= PluginEngine::getLink($plugin, array(), "templates/edit/".$template->getId()) ?>" title="<?= _("Template bearbeiten") ?>" data-dialog>
                        <?= Assets::img("icons/20/blue/edit") ?>
                    </a>
                    <a title="<?= _("Template löschen") ?>" onClick="STUDIP.serienbriefe.deleteTemplate('<?= $template->getId() ?>')">
                        <?= Assets::img("icons/20/blue/trash") ?>
                    </a>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
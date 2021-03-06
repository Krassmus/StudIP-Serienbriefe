<? if ($templates && count($templates) > 0) : ?>
    <table class="default">
        <thead>
        <tr>
            <th><?= _("Template") ?></th>
            <th><?= _("Betreff") ?></th>
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
                <td><?= date("j.n.Y", $template['chdate']) ?></td>
                <td class="actions">
                    <a title="<?= _("Template verwenden") ?>" onClick="STUDIP.serienbriefe.loadTemplate('<?= $template->getId() ?>');">
                        <?= Icon::create("play", "clickable")->asImg(20) ?>
                    </a>
                    <a href="<?= PluginEngine::getLink($plugin, array(), "templates/edit/".$template->getId()) ?>" title="<?= _("Template bearbeiten") ?>" data-dialog>
                        <?= Icon::create("edit", "clickable")->asImg(20) ?>
                    </a>
                    <a title="<?= _("Template löschen") ?>" onClick="STUDIP.serienbriefe.deleteTemplate('<?= $template->getId() ?>')">
                        <?= Icon::create("trash", "clickable")->asImg(20) ?>
                    </a>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    </table>
<? endif ?>
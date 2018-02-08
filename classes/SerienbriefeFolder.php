<?php

class SerienbriefeFolder extends StandardFolder
{
    public static function getTypeName()
    {
        return _('Temporäre Anhänge der Serienbriefe');
    }

    public function getIcon($role)
    {
        return Icon::create(
            $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/data-quest/Serienbriefe/assets/".(count($this->getFiles())
                ? 'folder-serienbriefe-full.svg'
                : 'folder-serienbriefe-empty.svg'),
            $role
        );
    }

    public static function availableInRange($range_id_or_object, $user_id)
    {
        return false;
    }

    public function isVisible($user_id)
    {
        return $this->user
            && $user_id === $this->user->id;
    }

    public function isReadable($user_id)
    {
        return $this->user
            && $user_id === $this->user->id;
    }

    public function isWritable($user_id)
    {
        return false;
    }

    public function isEditable($user_id)
    {
        return false;
    }

    public function isSubfolderAllowed($user_id)
    {
        //this folder type does not allow subfolders!
        return false;
    }
}
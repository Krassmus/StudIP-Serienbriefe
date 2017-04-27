STUDIP.serienbriefe = {
    activeElement: null,
    insertAtCursor: function (myValue) {
        if (!STUDIP.serienbriefe.activeElement) {
            STUDIP.serienbriefe.activeElement = window.document.message.message;
        }
        //IE support
        if (document.selection) {
            STUDIP.serienbriefe.activeElement.focus();
            sel = document.selection.createRange();
            sel.text = myValue;
        }
        //MOZILLA und Konsorten
        else if (STUDIP.serienbriefe.activeElement.selectionStart || STUDIP.serienbriefe.activeElement.selectionStart == '0') {
            var startPos = STUDIP.serienbriefe.activeElement.selectionStart;
            var endPos = STUDIP.serienbriefe.activeElement.selectionEnd;
            STUDIP.serienbriefe.activeElement.value = STUDIP.serienbriefe.activeElement.value.substring(0, startPos)
            + myValue
            + STUDIP.serienbriefe.activeElement.value.substring(endPos, STUDIP.serienbriefe.activeElement.value.length);
        } else {
            STUDIP.serienbriefe.activeElement.value += myValue;
        }
    },
    showTemplates: function () {
        var choice = jQuery("#template_action").val();
        if (choice === "save") {
            jQuery("#preview_subject").val(jQuery("#subject").val());
            jQuery("#preview_message").val(jQuery("#message").val());
            jQuery("#save_template_button").trigger("click");
            return;
        }
        if (choice === "admin") {
            jQuery("#add_new_template").hide();
            STUDIP.serienbriefe.adminTemplatesDialog();
            return;
        }
        if (choice !== "") {
            //laden eines Templates
            STUDIP.serienbriefe.loadTemplate(choice);
        }
    },
    adminTemplatesDialog: function () {
        STUDIP.Dialog.fromURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/serienbriefe/templates/overview");
    },
    loadTemplate: function (template_id) {
        if (window.confirm("Wirklich Template laden?")) {
            window.location.href = STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/serienbriefe/write/overview", {
                'load_template' : template_id
            });
        }
    },
    deleteTemplate: function (template_id) {
        if (window.confirm("Soll das Template wirklich gelöscht werden?")) {
            window.location.href = STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/serienbriefe/write/overview", {
                'delete_template' : template_id
            });
        }
    }
};
jQuery(function () {
    jQuery("input, textarea").focus(function () {
        STUDIP.serienbriefe.activeElement = this;
    });
});
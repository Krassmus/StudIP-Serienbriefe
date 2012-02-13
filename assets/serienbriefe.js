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
    preview: function () {
        var subject = jQuery("#subject").val();
        var text = jQuery("#message").val();
        jQuery("#message_delivery").val(text);
        jQuery("#subject_delivery").val(jQuery("#subject").val());
        
        var user_data = jQuery("#datatable > tbody > tr.correct")
            .filter("#user_" + jQuery("#preview_user").val())
            .find("td.user_data")
            .text();
        user_data = jQuery.parseJSON(user_data);
        jQuery.each(user_data, function (index, value) {
            subject = subject.replace("{{" + index + "}}", value ? value : "");
            text = text.replace("{{" + index + "}}", value ? value : "");
        });
        jQuery.ajax({
            url: STUDIP.URLHelper.getURL("plugins.php/serienbriefe/parse_text"),
            data: {
                'subject': subject,
                'message': text
            },
            dataType: 'json',
            success: function (output) {
                subject = output.subject;
                text = output.message;
                jQuery("#preview_subject").html(subject);
                jQuery("#preview_text").html(text);
                STUDIP.serienbriefe.previewCheck();
                
                jQuery('#preview_window').dialog({
                    title: "Vorschau",
                    modal: false,
                    height: jQuery(window).height() * 0.9,
                    width: "90%",
                    show: "fade",
                    hide: "fade"
                });
            }
        });
        /*text = text.replace(/&/g,"&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/\n/g, "<br>");*/
    },
    previewCheck: function () {
        jQuery("#fehler_protokoll").children().remove();
        var text = jQuery.trim(jQuery("#message").val());
        var subject = jQuery.trim(jQuery("#subject").val());
        if (!text) {
            jQuery("#fehler_protokoll").append(jQuery("<li>Text ist leer.</li>"));
        }
        var replacements = text.match(/{{.+?}}/gi);
        if (replacements === null) {
            replacements = [];
        }
        var replacements2 = subject.match(/{{.+?}}/gi);
        if (replacements2 !== null) {
            replacements.concat(replacements2);
        }
        if (replacements && (replacements.length > 0)) {
            jQuery.each(replacements, function (index, replacement) {
                var exists = false;
                jQuery("#replacements > li").each(function () {
                    if (jQuery(this).text().toLowerCase() === replacement.toLowerCase()) {
                        exists = true;
                    }
                });
                if (!exists) {
                    jQuery("#fehler_protokoll").append(jQuery("<li>" + replacement + " ist eine unbekannte Variable und wird nicht ersetzt werden.</li>"));
                }
            });
            var missing_info = 0;
            var missing_user = null;
            var missing_parameter = [];
            jQuery('#datatable > tbody > tr.correct').each(function () {
                var user_data = jQuery.parseJSON(jQuery(this).find(".user_data").text());
                if (user_data.user_id) {
                    var check = true;
                    jQuery.each(replacements, function (index, replacement) {
                        replacement = replacement.replace(/[{}]/g, "");
                        if (!user_data[replacement]) {
                            check = false;
                            if (!missing_user) {
                                missing_parameter.push("{{" + replacement + "}}");
                            }
                        }
                    });
                    if (!check) {
                        missing_info++;
                        if (!missing_user) {
                            missing_user = user_data;
                        }
                    }
                }
            });
            if (missing_info > 0) {
                jQuery("#fehler_protokoll").append(jQuery("<li>Es gibt " + missing_info + " Personen mit fehlerhaften bzw. unvollständigen Daten.</li>"));
                jQuery("#fehler_protokoll").append(jQuery("<li>Zum Beispiel " + missing_user.name + " fehlen die Daten: " + missing_parameter.join(", ") + "</li>"));
            }
        }
    },
    
    showTemplates: function () {
        var choice = jQuery("#template_action").val();
        if (choice === "save") {
            jQuery("#add_new_template input[name=template_id]").val("new");
            jQuery("#add_new_template input[name=subject]").val(jQuery("#subject").val());
            jQuery("#add_new_template textarea").val(jQuery("#message").val());
            jQuery("#add_new_template").show();
            STUDIP.serienbriefe.adminTemplatesDialog();
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
        jQuery('#templates_window').dialog({
            title: "Template-Verwaltung",
            modal: false,
            height: jQuery(window).height() * 0.9,
            width: "90%",
            show: "fade",
            hide: "fade"
        });
    },
    loadTemplate: function (template_id) {
        if (window.confirm("Wirklich Template laden?")) {
            window.location.href = STUDIP.URLHelper.getURL("?", {'load_template' : template_id});
        }
    },
    editTemplate: function (template_id) {
        //if (window.confirm("Wirklich Template laden?")) {
        window.location.href = STUDIP.URLHelper.getURL("?", {'edit_template' : template_id});
        //}
    },
    deleteTemplate: function (template_id) {
        if (window.confirm("Soll das Template wirklich gelöscht werden?")) {
            window.location.href = STUDIP.URLHelper.getURL("?", {'delete_template' : template_id});
        }
    }
};
jQuery(function () {
    jQuery("input, textarea").focus(function () {
        STUDIP.serienbriefe.activeElement = this;
    });
});
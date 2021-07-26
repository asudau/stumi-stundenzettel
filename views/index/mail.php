<form name="write_message" action="<?=$this->controller->link_for('index/send_form/' . $mail)?>" method="post" style="margin-left: auto; margin-right: auto;" >
       
    <h1>eMail an <?= $empfaenger ?></h1>
    
    <div>
        <label>
            <?= _("Betreff") ?>:
        </label>
        <input type="text" name="message_subject" style="width: 100%" required value="<?= htmlReady($default_message['subject']) ?>">
    </div>
    <div>
        <label>
            <?= _("Nachricht") ?>:
        </label>
        <textarea style="width: 100%; height: 100px; color: black" name="message_body" class="wysiwyg"></textarea> 
    </div>

    <div style="text-align: center;" data-dialog-button>
        <?= \Studip\Button::create(_('Abschicken'), null) ?>
    </div>

</form>



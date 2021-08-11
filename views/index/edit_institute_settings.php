<?
use Studip\Button, Studip\LinkButton;
?>

<form class='default' method="post" action="<?= $controller->link_for('index/save_institute_settings/' . $inst_id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    
    <h2></h2>
    
    <label>
        <h2>
        <?= _('Hilfskraft-Statusgruppen:') ?>
        <?= Icon::create('info-circle', Icon::ROLE_CLICKABLE,  
           ['title' => _('Wählen Sie die Statusgruppe(n) der Einrichtung aus, welchen Hilfskräfte zugeordnet sind. Die zugeordneten Personen erscheinen dann automatisch in Ihrer Vertrags-Verwaltungsübersicht.') ])?>
       </h2>
    </label>
        
    <? foreach ($groups as $group): ?>
        <input type='checkbox' name='statusgruppen[]' <?= (in_array($group->id, explode(',', $stumi_group_ids) )) ? 'checked' : '' ?> value="<?= htmlready($group->id)?>"><?= htmlready($group->name) ?></input><br>
    <? endforeach ?>
        
    <label>
       <h2>
        <?= _('Mailadresse der Verwaltung:') ?>
        <?= Icon::create('info-circle', Icon::ROLE_CLICKABLE,  
           ['title' => _('Emailadresse der verantwortlichen Stelle innerhalb der Einrichtung (z.B. Sekretariat). Diese wird beispielsweise als Absender von automatischen Erinnerungsmails angegeben.') ])?>
       </h2>
    </label> 
        <input type='text' required name="email" value='<?= $inst_mail?>'>
       

    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>
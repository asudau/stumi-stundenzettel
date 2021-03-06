
<?
use Studip\Button, Studip\LinkButton;
?>

<form class='default' method="post" action="<?= $controller->link_for('index/save/' . $inst_id . '/' . $stumi->user_id . '/' . $contract->id ) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($following_contract) : ?>
        <input type='hidden' name ='following_contract' value='true' >
    <? endif ?>
    
    <h2><?= _('Name: ') ?> <?=htmlready($stumi->vorname)?> <?=htmlready($stumi->nachname)?></h2>

    <label>
        <?= _('Vertragsbeginn') ?>
    
        <input type='date' required class='size-l' 
           <? if ($following_contract) : ?>
            min="<?= date('Y-m-d', strtotime('+1 day', $contract->contract_end)) ?>" 
            name="begin" value='<?= ($contract) ? date('Y-m-d', strtotime('+1 day', $contract->contract_end)) : ''?>' >
           <? else : ?>
            name="begin" value='<?= ($contract) ? date('Y-m-d', $contract->contract_begin) : ''?>' >
           <? endif ?>
    </label>

    <label>
        <?= _('Vertragsende') ?>
        <input type='date' required class='size-l'
           name="end" value='<?= ($contract && !$following_contract) ? date('Y-m-d', $contract->contract_end) : ''?>' >
    </label>

    <label>
        <?= _('Stundenumfang') ?>
        <input type='text' required name="hours" placeholder='00:00' 
               value='<?= ($contract) ? htmlready($contract->contract_hours) : ''?>'>
    </label>

    <label>
        <?= _('Verantwortliche/r Mitarbeiter/in') ?>
        <?= $search ?>
    </label>
              
    
    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>





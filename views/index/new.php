
<?
use Studip\Button, Studip\LinkButton;
?>

<html>

<form class='default' method="post" action="<?= $controller->url_for('index/save/' . htmlready($inst_id) . '/' . htmlready($stumi->user_id) . '/' . htmlready($contract->id) ) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($following_contract) : ?>
        <input type='hidden' name ='following_contract' value='true' >
    <? endif ?>
    
    <section>
        <label>
            <h2><?= _('Name: ') ?> <?=htmlready($stumi->vorname)?> <?=htmlready($stumi->nachname)?></h2>
        </label>
    </section>
    
    <section>
        <label>
            <?= _('Vertragsbeginn') ?>
        </label>
        <input type='date' class='size-l' 
               <? if ($following_contract) : ?>
                min="<?= date('Y-m-d', strtotime('+1 day', $contract->contract_end)) ?>" 
                name="begin" value='<?= ($contract) ? date('Y-m-d', strtotime('+1 day', $contract->contract_end)) : ''?>' >
               <? else : ?>
                name="begin" value='<?= ($contract) ? date('Y-m-d', $contract->contract_begin) : ''?>' >
               <? endif ?>
               </input>
    </section>
    
    <section>
        <label>
            <?= _('Vertragsende') ?>
        </label>
        <input type='date' class='size-l'
               name="end" value='<?= ($contract && !$following_contract) ? date('Y-m-d', $contract->contract_end) : ''?>' >
    </section>
    
    <section>
        <label>
            <?= _('Stundenumfang') ?>
        </label>
        <input type='text' name="hours" placeholder='00:00' value='<?= ($contract) ? htmlready($contract->contract_hours) : ''?>'>
    </section>
    
    <section>
        <label>
            <?= _('Verantwortliche/r Mitarbeiter/in') ?>
        </label>
        <?= $search ?>
    </section>                
    
    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>





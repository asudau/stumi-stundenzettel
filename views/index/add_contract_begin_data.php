
<?
use Studip\Button, Studip\LinkButton;
?>

<html>

<form class='default' method="post" action="<?= $controller->url_for('index/save_contract_begin_data/' . htmlready($contract->id)) ?>">
    <?= CSRFProtection::tokenTag() ?>
    
    <label>
        <h2><?= _('Name: ') ?> <?= htmlready($stumi->vorname)?> <?= htmlready($stumi->nachname)?></h2>
    </label>
    
    <label>
        <?= _('Beginn der digitalen Stundenerfassung ab: Monat und Jahr') ?>
    </label>
    <select name ='begin_month' required>
        <? foreach ($plugin->getMonths() as $entry_value): ?>
            <option <?= ($contract->begin_digital_recording_month == $entry_value) ? 'selected' : '' ?> value="<?= htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
        <? endforeach ?>
    </select>
    <select  name ='begin_year' required>
        <? foreach ($plugin->getYears() as $entry_value): ?>
            <option <?= ($contract->begin_digital_recording_year == $entry_value) ? 'selected' : '' ?> value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
        <? endforeach ?>
    </select>
    
    <label>
        <?= _('Bereits beanspruchter Urlaub im laufenden Jahr:') ?>
    </label>
    <input type='text' pattern="<?= $balance_pattern ?>" required name="vacation_claimed" placeholder='hh:mm' value='<?= ($contract->begin_vacation_claimed) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->begin_vacation_claimed)) : ''?>'>

    
    <label>
        <?= _('Stundenkonto zu Beginn der digitalen Aufzeichnung:') ?>
    </label>
    <input type='text' pattern="<?= $balance_pattern ?>" required name="balance" placeholder='(-)hh:mm' value='<?= ($contract->begin_balance) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->begin_balance)) : ''?>'>
        

    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>






<?
use Studip\Button, Studip\LinkButton;
?>

<form class='default' method="post" action="<?= $controller->link_for('index/save_contract_begin_data/' . $contract->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    
    <h2><?= _('Name: ') ?> <?= htmlready($stumi->vorname)?> <?= htmlready($stumi->nachname)?></h2>
    
    <label>
        <?= _('Beginn der digitalen Stundenerfassung ab: Monat und Jahr') ?>

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
    </label>
    
    <label>
        <?= _('Bereits beanspruchter Urlaub im laufenden Jahr:') ?>
        <input type='text' pattern="<?= $balance_pattern ?>" required name="vacation_claimed" placeholder='hh:mm' value='<?= ($contract->begin_vacation_claimed) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->begin_vacation_claimed)) : '00:00'?>'>
    </label>
    
    <label>
        <?= _('Verbleibender Resturlaub aus dem vorigen Jahr zum 01.01. diesen Jahres:') ?>
        <input type='text' pattern="<?= $balance_pattern ?>" required name="begin_last_year_vacation_remainig" placeholder='hh:mm' value='<?= ($contract->last_year_vacation_remaining) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->last_year_vacation_remaining)) : '00:00'?>'>
    </label>
    
    <label>
        <?= _('Stundenkonto zu Beginn der digitalen Aufzeichnung:') ?>
        <input type='text' pattern="<?= $balance_pattern ?>" required name="balance" placeholder='(-)hh:mm' value='<?= ($contract->begin_balance) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->begin_balance)) : '00:00'?>'>
    </label>    

    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>





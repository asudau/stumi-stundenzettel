
<?
use Studip\Button, Studip\LinkButton;
?>

<html>

<form class='default' method="post" action="<?= $controller->url_for('index/save_contract_begin_data/' . htmlready($contract->id)) ?>">
    <?= CSRFProtection::tokenTag() ?>
    
    <section>
        <label>
            <h2><?= _('Name: ') ?> <?= htmlready($stumi->vorname)?> <?= htmlready($stumi->nachname)?></h2>
        </label>
    </section>
    
    <section>
        <label>
            <?= _('Beginn der digitalen Stundenerfassung ab: Monat und Jahr') ?>
        </label>
        <select name ='begin_month' required>
            <?php foreach ($plugin->getMonths() as $entry_value): ?>
                <option <?= ($contract->begin_digital_recording_month == $entry_value) ? 'selected' : '' ?> value="<?= htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
            <?php endforeach ?>
        </select>
        <select  name ='begin_year' required>
            <?php foreach ($plugin->getYears() as $entry_value): ?>
                <option <?= ($contract->begin_digital_recording_year == $entry_value) ? 'selected' : '' ?> value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
            <?php endforeach ?>
        </select>
    </section>
    
    <section>
        <label>
            <?= _('Bereits beanspruchter Urlaub im laufenden Jahr:') ?>
        </label>
        <input type='text' pattern="<?= $balance_pattern ?>" required name="vacation_claimed" placeholder='hh:mm' value='<?= ($contract->begin_vacation_claimed) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->begin_vacation_claimed)) : ''?>' ></input>
    </section>
    
    <section>
        <label>
            <?= _('Stundenkonto zu Beginn der digitalen Aufzeichnung:') ?>
        </label>
        <input type='text' pattern="<?= $balance_pattern ?>" required name="balance" placeholder='(-)hh:mm' value='<?= ($contract->begin_balance) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->begin_balance)) : ''?>'></input>
    </section>

    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>





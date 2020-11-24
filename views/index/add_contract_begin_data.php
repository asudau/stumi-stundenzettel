
<?
use Studip\Button, Studip\LinkButton;
?>

<html>

<form class='default' method="post" action="<?= $controller->url_for('index/save_contract_begin_data/' . $contract->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    
    <section>
        <label>
            <h2><?= _('Name: ') ?> <?=$stumi->vorname?> <?=$stumi->nachname?></h2>
        </label>
    </section>
    
    <section>
        <label>
            <?= _('Beginn der digitalen Stundenerfassung ab: Monat und Jahr') ?>
        </label>
        <select name ='begin_month' required>
            <?php foreach ($plugin->getMonths() as $entry_value): ?>
                <option <?= ($contract_data->begin_digital_recording_month == $entry_value) ? 'selected' : '' ?> value="<?=$entry_value?>"><?= $entry_value ?></option>
            <?php endforeach ?>
        </select>
        <select  name ='begin_year' required>
            <?php foreach ($plugin->getYears() as $entry_value): ?>
                <option <?= ($contract_data->begin_digital_recording_year == $entry_value) ? 'selected' : '' ?> value="<?=$entry_value?>"><?= $entry_value ?></option>
            <?php endforeach ?>
        </select>
    </section>
    
    <section>
        <label>
            <?= _('Bereits beanspruchter Urlaub im laufenden Jahr:') ?>
        </label>
        <input type='text' required name="vacation_claimed" placeholder='00:00' value='<?= ($contract_data) ? $contract_data->vacation_claimed : ''?>' ></input>
    </section>
    
    <section>
        <label>
            <?= _('Stundenkonto zu Beginn der digitalen Aufzeichnung:') ?>
        </label>
        <input type='text' required name="balance" placeholder='(-)00:00' value='<?= ($contract_data) ? $contract_data->balance : ''?>'></input>
    </section>

    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>





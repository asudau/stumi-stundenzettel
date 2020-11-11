
<?
use Studip\Button, Studip\LinkButton;
?>

<html>

<form class='default' method="post" action="<?= $controller->url_for('index/save/' . $inst_id . '/' . $stumi->id . '/' . $contract->id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    
    <section>
        <label>
            <h2><?= _('Name: ') ?> <?=$stumi->vorname?> <?=$stumi->nachname?></h2>
        </label>
    </section>
    
    <section>
        <label>
            <?= _('Vertragsbeginn') ?>
        </label>
        <input type='date' name="begin" value='<?= ($contract) ? date('Y-m-d', $contract->contract_begin) : ''?>' ></input>
    </section>
    
    <section>
        <label>
            <?= _('Vertragsende') ?>
        </label>
        <input type='date' name="end" value='<?= ($contract) ? date('Y-m-d', $contract->contract_end) : ''?>' ></input>
    </section>
    
    <section>
        <label>
            <?= _('Stundenumfang') ?>
        </label>
        <input type='text' name="hours" placeholder='00:00' value='<?= ($contract) ? $contract->contract_hours : ''?>'></input>
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





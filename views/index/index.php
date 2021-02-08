<html>

<body>
<div>
    
<?php if ($adminrole || $supervisorrole) : ?>    
    <h> <?= htmlready(Institute::find($inst_id[0])->name) ?>: <?= sizeof($stumis) ?> Studentische MitarbeiterInnen </h1>

    <table id='stumi-contract-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Nachname, Vorname</th>
                <th data-sort="false" style='width:10%'>Vertragsbeginn</th>
                <th data-sort="false" style='width:10%'>Vertragsende</th>
                <th data-sort="digit" style='width:10%'>Laufzeit</br>in Monaten</th>
                <th data-sort="digit" style='width:10%'>Monatsstunden</br>lt. Vertrag</th>
                <th data-sort="digit" style='width:10%'>Tagessatz</th>
                <th data-sort="digit" style='width:10%'>Stundenkonto</br>(exkl. <?= strftime('%B', time()) ?>)</th>
                <th data-sort="false" style='width:10%'>Urlaub in Anspruch genommen <?= date('Y', time()) ?></th>
                <th data-sort="digit" style='width:10%'>Resturlaub</br><?= date('Y', time()) ?></th>
                <th data-sort="false" style='width:10%'>Verantwortliche/r MA</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($stumis as $stumi): ?>
            <?php if ($stumi_contracts[$stumi->user_id]) : ?>
                <?php foreach ($stumi_contracts[$stumi->user_id] as $contract): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/index/' . htmlready($contract->id)) ?>' title='Stundenzettel einsehen'><?= htmlready($stumi->nachname) ?>, <?= htmlready($stumi->vorname) ?></a>
                    </td>
                    <td><?= date('d.m.Y', $contract->contract_begin) ?>
                        <? if ($contract->begin_digital_recording_month && $contract->begin_digital_recording_year) : ?>
                            <?= Icon::create('info-circle', Icon::ROLE_CLICKABLE,  
                                    ['title' => 'Beginn der elektronischen Erfassung: ' . htmlready($contract->begin_digital_recording_month) . '/' . htmlready($contract->begin_digital_recording_year) ]);?>
                        <? endif ?>
                    </td>
                    <td><?= date('d.m.Y', $contract->contract_end) ?></td>
                    <td><?= htmlready($contract->getContractDuration()) ?></td>
                    <td><?= htmlready($contract->contract_hours) ?></td>
                    <td><?= htmlready($contract->default_workday_time) ?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getWorktimeBalance())) ?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getClaimedVacation(date('Y', time()))))?> von <?= htmlready($contract->getVacationEntitlement(date('Y', time())))?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getRemainingVacation(date('Y', time()))))?></td>
                    <td><?= htmlready(User::findOneByUser_Id($contract->supervisor)->username) ?></td>
                    <td>
                        <? if ($adminrole) : ?>
                            <a onclick="return confirm('Eintrag löschen?')" href='<?=$this->controller->url_for('index/delete/' . htmlready($contract->id)) ?>' title='Eintrag löschen' ><?=Icon::create('trash', Icon::ROLE_CLICKABLE)?></a>
                            <a  href='<?=$this->controller->url_for('index/edit/' . htmlready($contract->id)) ?>' title='Vertragsdaten bearbeiten' data-dialog='size=auto'><?=Icon::create('edit', Icon::ROLE_CLICKABLE)?></a>
                            <a  href='<?=$this->controller->url_for('index/add_contract_begin_data/' . htmlready($contract->id)) ?>' title='Zeitpunkt für Beginn digitaler Erfassung defnieren' data-dialog='size=auto'><?=Icon::create('date', Icon::ROLE_CLICKABLE)?></a>
                            <a  href='<?=$this->controller->url_for('index/edit/'. htmlready($contract->id) . '/1') ?>' title='Folgevertrag anlegen' data-dialog='size=auto'><?=Icon::create('add', Icon::ROLE_CLICKABLE)?></a>
                            <? endif ?>
                    </td>

                </tr>
                <?php endforeach ?>

           <?php else : ?>
                <tr> 
                    <td><?= htmlready($stumi->nachname) ?>, <?= htmlready($stumi->vorname) ?>
                    </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td>
                       <a  href='<?=$this->controller->url_for('index/new/'. htmlready($inst_id[0]) . '/' . htmlready($stumi->user_id)) ?>' title='Vertrag hinzufügen' data-dialog='size=auto'><?=Icon::create('add', Icon::ROLE_CLICKABLE)?></a>
                    </td>
                    </tr>

            <?php endif ?>
        <?php endforeach ?>

        </tbody>
    </table>
    
<?php elseif ($stumirole) : ?>
    <h> Meine Verträge </h1>
    <table id='stumi-contract-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Institut/Organisationseinheit</th>
                <th data-sort="false" style='width:10%'>Vertragsbeginn</th>
                <th data-sort="false" style='width:10%'>Vertragsende</th>
                <th data-sort="false" style='width:10%'>Stunden lt. Vertrag</th>
                <th data-sort="false" style='width:10%'>Stundenkonto (exkl. <?= date('M', time()) ?>)</th>
                <th data-sort="false" style='width:10%'>Resturlaub/Urlaubsanspruch <?= date('Y', time()) ?></th>                
                <th data-sort="false" style='width:10%'>Verantwortliche/r MA</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stumi_contracts as $contract): ?>
                <tr>
                    <td><?= htmlready(Institute::find($contract->inst_id)->name) ?></td>
                    <td><?= date('d.m.Y', $contract->contract_begin) ?></td>
                    <td><?= date('d.m.Y', $contract->contract_end) ?></td>
                    <td><?= htmlready($contract->contract_hours) ?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getWorktimeBalance())) ?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getRemainingVacation(date('Y', time())))) ?>/<?= htmlready($contract->getVacationEntitlement(date('Y', time()))) ?></td>
                    <td><?= htmlready(User::findOneByUser_Id($contract->supervisor)->username) ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

<?php endif ?>    
    
</div>

</body>

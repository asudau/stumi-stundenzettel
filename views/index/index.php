<html>

<body>
<div>
    
<?php if ($adminrole) : ?>    
    <h> <?= Institute::find($inst_id)->name ?>: <?= sizeof($stumis) ?> Studentische MitarbeiterInnen </h1>

    <table id='stumi-contract-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Nachame, Vorname</th>
                <th data-sort="false" style='width:10%'>Vertragsbeginn</th>
                <th data-sort="false" style='width:10%'>Vertragsende</th>
                <th data-sort="false" style='width:10%'>Stunden</th>
                <th data-sort="false" style='width:10%'>Verantwortlicher/r MA</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($stumis as $stumi): ?>
            <?php if ($stumi_contracts[$stumi->user_id]) : ?>
                <?php foreach ($stumi_contracts[$stumi->user_id] as $contract): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/index/' . $contract->id) ?>' title='Stundenzette einsehen'><?= $stumi->nachname ?>, <?= $stumi->vorname ?></a>
                    </td>
                    <td><?= date('d.m.Y', $contract->contract_begin) ?></td>
                    <td><?= date('d.m.Y', $contract->contract_end) ?></td>
                    <td><?= $contract->contract_hours ?></td>
                    <td><?= User::findOneByUser_Id($contract->supervisor)->username ?></td>
                    <td>
                       <a onclick="return confirm('Eintrag löschen?')" href='<?=$this->controller->url_for('index/delete/' . $entry['id']) ?>' title='Eintrag löschen' ><?=Icon::create('trash')?></a>
                    </td>

                </tr>
                <?php endforeach ?>

           <?php else : ?>
                <tr> 
                    <td><?= $stumi->nachname ?>, <?= $stumi->vorname ?>
                    </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td> -- </td>
                    <td>
                       <a onclick="return confirm('Eintrag löschen?')" href='<?=$this->controller->url_for('index/delete/' . $entry['id']) ?>' title='Eintrag löschen' ><?=Icon::create('trash')?></a>
                    </td>
                    </tr>

            <?php endif ?>
        <?php endforeach ?>

        </tbody>
    </table>
    
<?php elseif ($stumirole) : ?>
    <h> <?= Institute::find($inst_id)->name ?>: Meine Verträge </h1>
    <table id='stumi-contract-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Nachame, Vorname</th>
                <th data-sort="false" style='width:10%'>Vertragsbeginn</th>
                <th data-sort="false" style='width:10%'>Vertragsende</th>
                <th data-sort="false" style='width:10%'>Stunden</th>
                <th data-sort="false" style='width:10%'>Verantwortlicher/r MA</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stumi_contracts as $contract): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/index/' . $contract->id) ?>' title='Stundenzette einsehen'><?= $stumi->nachname ?>, <?= $stumi->vorname ?></a>
                    </td>
                    <td><?= date('d.m.Y', $contract->contract_begin) ?></td>
                    <td><?= date('d.m.Y', $contract->contract_end) ?></td>
                    <td><?= $contract->contract_hours ?></td>
                    <td><?= User::findOneByUser_Id($contract->supervisor)->username ?></td>
                    <td>
                    </td>

                </tr>
            <?php endforeach ?>
        </tbody>
    </table>

<?php endif ?>    
    
</div>

</body>

<html>

<body>
<div>
    
<?php if ($adminrole) : ?>    
    <h1> <?= $stumi->nachname ?>, <?= $stumi->vorname ?> </h1>
    Vertragslaufzeit: <?= date('d.m.Y', $contract->contract_begin) ?> bis <?= date('d.m.Y', $contract->contract_end) ?>

    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Monat/Jahr</th>
                <th data-sort="false" style='width:10%'>Stunden</th>
                <th data-sort="false" style='width:10%'>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>  
                <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . $contract->id) ?>' title='Stundenzettel anlegen'>Stundenzettel anlegen</a>
                </td>
                <td></td>
                <td></td>
            </tr>
            <?php if ($timesheets) : ?>
                <?php foreach ($timesheets as $timesheet): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . $timesheet->id) ?>' title='Stundenzettel editieren'><?= $timesheet->month ?>/<?= $timesheet->year ?></a>
                    </td>
                    <td><?= $timesheet->sum ?></td>
                    <td></td>
                </tr>
                <?php endforeach ?>
            <? endif ?>
        </tbody>
    </table>
    
<?php elseif ($stumirole) : ?>
    <h1> Vertragslaufzeit: <?= date('d.m.Y', $contract->contract_begin) ?> bis <?= date('d.m.Y', $contract->contract_end) ?> </h1>

    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Monat/Jahr</th>
                <th data-sort="false" style='width:10%'>Stunden</th>
                <th data-sort="false" style='width:10%'>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>  
                <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . $contract->id) ?>' title='Stundenzettel anlegen'>Stundenzettel anlegen</a>
                </td>
                <td></td>
                <td></td>
            </tr>
            <?php if ($timesheets) : ?>
                <?php foreach ($timesheets as $timesheet): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . $timesheet->id) ?>' title='Stundenzettel editieren'><?= $timesheet->month ?>/<?= $timesheet->year ?></a>
                    </td>
                    <td><?= $timesheet->sum ?></td>
                    <td></td>
                </tr>
                <?php endforeach ?>
            <? endif ?>
        </tbody>
    </table>

<?php endif ?>    
    
</div>

</body>

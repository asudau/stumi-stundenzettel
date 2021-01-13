<html>

<body>
<div>
<?php if (!$contract->id) : ?>   
    <h1> Diese Ansicht fehlt noch, Für die Übersicht über Stundenzettel einer Person, einfach die Person anklicken. </h1>
    
<?php elseif ($adminrole || $supervisorrole) : ?>    
    <? $role = ($adminrole) ? 'admin' : 'supervisor' ?>
    <h1> <?= $stumi->nachname ?>, <?= $stumi->vorname ?> </h1>
    Vertragslaufzeit: <?= date('d.m.Y', $contract->contract_begin) ?> bis <?= date('d.m.Y', $contract->contract_end) ?>

    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Monat/Jahr</th>
                <th data-sort="false" style='width:10%'>Stunden</th>
                <th data-sort="false" style='width:10%'>Überstunden</th>
                <th data-sort="false" style='width:10%'>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($timesheets) : ?>
                <?php foreach ($timesheets as $timesheet): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . $timesheet->id) ?>' title='Stundenzettel editieren'><?= $timesheet->month ?>/<?= $timesheet->year ?></a>
                    </td>
                    <td><?= ($timesheet->sum) ? : '0:00' ?> / <?= $timesheet->contract->contract_hours ?></td>
                    <td><?= ($timesheet->month_completed) ? $timesheet->timesheet_balance : '(laufend)' ?></td>
                    <td>  
                        <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet->getCurrentState('finished', $role) . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet->getCurrentState('finished', $role) . '_tooltip']] )?>
                        <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet->getCurrentState('approved', $role) . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet->getCurrentState('approved', $role) . '_tooltip']] )?>
                        <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet->getCurrentState('received', $role) . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet->getCurrentState('received', $role) . '_tooltip']] )?>
                        <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet->getCurrentState('complete', $role) . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet->getCurrentState('complete', $role) . '_tooltip']] )?>
                    </td>
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
                <th data-sort="false" style='width:10%'>Überstunden</th>
                <th data-sort="false" style='width:10%'>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($timesheets) : ?>
                <?php foreach ($timesheets as $timesheet): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . $timesheet->id) ?>' title='Stundenzettel editieren'><?= $timesheet->month ?>/<?= $timesheet->year ?></a>
                    </td>
                    <td><?= ($timesheet->sum) ? : '0:00' ?> / <?= $timesheet->contract->contract_hours ?></td>
                    <td><?= ($timesheet->month_completed) ? $timesheet->timesheet_balance : '(laufend)' ?></td>
                    <td>
                        <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet->getCurrentState('finished', 'stumi') . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet->getCurrentState('finished', 'stumi') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet->getCurrentState('approved', 'stumi') . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet->getCurrentState('approved', 'stumi') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet->getCurrentState('received', 'stumi') . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet->getCurrentState('received', 'stumi') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet->getCurrentState('complete', 'stumi') . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet->getCurrentState('complete', 'stumi') . '_tooltip']] )?>
                    </td>
                </tr>
                <?php endforeach ?>
            <? endif ?>
        </tbody>
    </table>

<?php endif ?>    
    
</div>

</body>

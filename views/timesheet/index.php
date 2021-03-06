<div>
<? if (!$contract->id) : ?>   
    <h2> Diese Ansicht fehlt noch, Für die Übersicht über Stundenzettel einer Person, einfach die Person anklicken. </h2>
    
<? elseif ($adminrole || $supervisorrole) : ?>    
    <? $role = ($adminrole) ? 'admin' : 'supervisor' ?>
    <h2> <?= htmlready($stumi->nachname) ?>, <?= htmlready($stumi->vorname) ?> </h2>
    Vertragslaufzeit: <?= date('d.m.Y', $contract->contract_begin) ?> bis <?= date('d.m.Y', $contract->contract_end) ?>

    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Monat/Jahr</th>
                <th data-sort="false" style='width:10%'>Stunden</th>
                <th data-sort="false" style='width:10%'>davon Urlaub</th>
                <th data-sort="false" style='width:10%'>Überstunden</th>
                <th data-sort="false" style='width:10%'>Status</th>
            </tr>
        </thead>
        <tbody>
            <? if ($timesheets) : ?>
                <? foreach ($timesheets as $timesheet): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . htmlready($timesheet->id)) ?>' title='Stundenzettel editieren'><?= htmlready($timesheet->month) ?>/<?= htmlready($timesheet->year) ?></a>
                    </td>
                    <td><?= ($timesheet->sum) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->sum)) : '0:00' ?> / <?= htmlready($timesheet->contract->contract_hours) ?></td>
                    <td><?= ($timesheet->vacation) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->vacation)) : '0:00' ?></td>
                    <td><?= ($timesheet->month_completed) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->timesheet_balance)) : '(ausstehend)' ?></td>
                    <td>  
                        <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet->getCurrentState('finished', $role) . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet->getCurrentState('finished', $role) . '_tooltip']] )?>
                        <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet->getCurrentState('approved', $role) . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet->getCurrentState('approved', $role) . '_tooltip']] )?>
                        <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet->getCurrentState('received', $role) . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet->getCurrentState('received', $role) . '_tooltip']] )?>
                        <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet->getCurrentState('complete', $role) . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet->getCurrentState('complete', $role) . '_tooltip']] )?>
                    </td>
                </tr>
                <? endforeach ?>
            <? endif ?>
        </tbody>
    </table>
    
<? elseif ($stumirole) : ?>
    <h2> Vertragslaufzeit: <?= date('d.m.Y', $contract->contract_begin) ?> bis <?= date('d.m.Y', $contract->contract_end) ?> </h2>

    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Monat/Jahr</th>
                <th data-sort="false" style='width:10%'>Stunden</th>
                <th data-sort="false" style='width:10%'>davon Urlaub</th>
                <th data-sort="false" style='width:10%'>Überstunden</th>
                <th data-sort="false" style='width:10%'>Status</th>
                <th style='width:10%'>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <? if ($timesheets) : ?>
                <? foreach ($timesheets as $timesheet): ?>
                <tr>  
                    <td><a href='<?=$this->controller->url_for('timesheet/timesheet/' . htmlready($timesheet->id)) ?>' title='Stundenzettel editieren'><?= htmlready($timesheet->month) ?>/<?= htmlready($timesheet->year) ?></a>
                    </td>
                    <td><?= ($timesheet->sum) ? StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->sum) : '0:00' ?> / <?= htmlready($timesheet->contract->contract_hours) ?></td>
                    <td><?= ($timesheet->vacation) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->vacation)) : '0:00' ?></td>
                    <td><?= ($timesheet->month_completed) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->timesheet_balance)) : '(ausstehend)' ?></td>
                    <td>
                        <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet->getCurrentState('finished', 'stumi') . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet->getCurrentState('finished', 'stumi') . '_tooltip']] )?>
                        <!--
                        <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet->getCurrentState('approved', 'stumi') . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet->getCurrentState('approved', 'stumi') . '_tooltip']] )?>
                        -->
                        <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet->getCurrentState('received', 'stumi') . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet->getCurrentState('received', 'stumi') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet->getCurrentState('complete', 'stumi') . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet->getCurrentState('complete', 'stumi') . '_tooltip']] )?>
                    </td>
                    <td>
                        <? if ($timesheet->finished) : ?>
                        <a href="<?= PluginEngine::getLink($plugin, [], 'timesheet/pdf/' . $timesheet->id ) ?>">
                            <?= Icon::create('file-pdf', ['title' =>  'PDF zum Ausdruck generieren'] )?>
                        </a>
                        <? endif ?>
                    </td>
                </tr>
                <? endforeach ?>
            <? endif ?>
        </tbody>
    </table>

<? endif ?>    
    
</div>

</body>

<div>

<? if ($adminrole || $supervisorrole) : ?>    
    <h2>Status aktuelle Stundenzettel  </h2>


    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Nachname, Vorname</th>
                <th data-sort="false" style='width:10%'>Stundenkonto</th>
                <th data-sort="htmldata" style='width:10%'>Status</br>Stundenzettel</br><?= htmlready($last_month)?> </th>
                <th data-sort="htmldata" style='width:10%'>Status</br>Stundenzettel</br><?= htmlready($next_month)?></th>
                <th data-sort="false" style='width:10%'>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <? if ($contracts) : ?>
                <? foreach ($contracts as $contract): ?>
                <tr>  
                    <? $timesheet_last_month = $timesheets[$contract->id]['last_month']; ?>
                    <? $timesheet_next_month = $timesheets[$contract->id]['next_month']; ?>
                    <td>
                        <a href='<?=$this->controller->url_for('timesheet/index/' . htmlready($contract->id)) ?>' title='Stundenzettel einsehen'><?= htmlready($contract->stumi->nachname) ?>, <?= htmlready($contract->stumi->vorname) ?></a>
                    </td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getWorktimeBalance())) ?></td>
                    <td data-sort-value= <?= htmlready($timesheet_last_month->int_status) ?> >  
                        <? if ($timesheet_last_month) : ?>
                        <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet_last_month->getCurrentState('finished', 'admin') . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet_last_month->getCurrentState('finished', 'admin') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet_last_month->getCurrentState('approved', 'admin') . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet_last_month->getCurrentState('approved', 'admin') . '_tooltip']] )?>
                        <a href='<?=$this->controller->url_for('timesheet/received/' . htmlready($timesheet_last_month->id)) ?>' title='Vorliegen bestätigen'>
                            <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet_last_month->getCurrentState('received', 'admin') . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet_last_month->getCurrentState('received', 'admin') . '_tooltip']] )?>
                        </a>
                        <a href='<?=$this->controller->url_for('timesheet/complete/' . htmlready($timesheet_last_month->id)) ?>' title='Vorgang abschließen'>
                            <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet_last_month->getCurrentState('complete', 'admin') . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet_last_month->getCurrentState('complete', 'admin') . '_tooltip']] )?>
                        </a>
                            <? endif ?>
                    </td>
                    <td data-sort-value= <?= htmlready($timesheet_next_month->int_status) ?>>  
                        <? if ($timesheet_next_month->int_status) : ?>
                            <? if ($timesheet_next_month) : ?>
                            <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet_next_month->getCurrentState('finished', 'admin') . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet_next_month->getCurrentState('finished', 'admin') . '_tooltip']] )?>      
                            <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet_next_month->getCurrentState('approved', 'admin') . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet_next_month->getCurrentState('approved', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet_next_month->getCurrentState('received', 'admin') . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet_next_month->getCurrentState('received', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet_next_month->getCurrentState('complete', 'admin') . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet_next_month->getCurrentState('complete', 'admin') . '_tooltip']] )?>
                            <? endif ?>
                        <? elseif ($timesheet_next_month): ?>
                            <?= Icon::create($status_infos['finished']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['finished'][$timesheet_next_month->getCurrentState('finished', 'admin') . '_tooltip']] )?>      
                            <?= Icon::create($status_infos['approved']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['approved'][$timesheet_next_month->getCurrentState('approved', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['received']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['received'][$timesheet_next_month->getCurrentState('received', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['complete']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['complete'][$timesheet_next_month->getCurrentState('complete', 'admin') . '_tooltip']] )?>
                        
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

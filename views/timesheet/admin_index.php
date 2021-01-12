<html>

<body>
<div>

<?php if ($adminrole || $supervisorrole) : ?>    
    <h1>Status aktuelle Stundenzettel  </h1>


    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Nachname, Vorname</th>
                <th data-sort="false" style='width:10%'>Stundenkonto</th>
                <th data-sort="htmldata" style='width:10%'>Status</br>Stundenzettel</br><?= $last_month?> </th>
                <th data-sort="htmldata" style='width:10%'>Status</br>Stundenzettel</br><?= $next_month?></th>
                <th data-sort="false" style='width:10%'>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($contracts) : ?>
                <?php foreach ($contracts as $contract): ?>
                <tr>  
                    <? $timesheet_last_month = $timesheets[$contract->id]['last_month']; ?>
                    <? $timesheet_next_month = $timesheets[$contract->id]['next_month']; ?>
                    <td><?= $contract->stumi->nachname ?>, <?= $contract->stumi->vorname ?>
                        <a href='<?=$this->controller->url_for('timesheet/timesheet/' . $timesheet->id) ?>' title='Stundenzettel einsehen'><?= $timesheet->month ?><?= $timesheet->year ?></a>
                    </td>
                    <td><?= $contract->getWorktimeBalance() ?></td>
                    <td data-sort-value= <?= $timesheet_last_month->int_status ?> >  
                        <? if ($timesheet_last_month) : ?>
                        <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet_last_month->getCurrentState('finished', 'admin') . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet_last_month->getCurrentState('finished', 'admin') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet_last_month->getCurrentState('approved', 'admin') . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet_last_month->getCurrentState('approved', 'admin') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet_last_month->getCurrentState('received', 'admin') . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet_last_month->getCurrentState('received', 'admin') . '_tooltip']] )?>
                        <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet_last_month->getCurrentState('complete', 'admin') . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet_last_month->getCurrentState('complete', 'admin') . '_tooltip']] )?>
                        <? endif ?>
                    </td>
                    <td data-sort-value= <?= $timesheet_next_month->int_status ?>>  
                        <? if ($timesheet_next_month->int_status) : ?>
                            <? if ($timesheet_next_month) : ?>
                            <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet_next_month->getCurrentState('finished', 'admin') . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet_last_month->getCurrentState('finished', 'admin') . '_tooltip']] )?>      
                            <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet_next_month->getCurrentState('approved', 'admin') . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet_last_month->getCurrentState('approved', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet_next_month->getCurrentState('received', 'admin') . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet_last_month->getCurrentState('received', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet_next_month->getCurrentState('complete', 'admin') . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet_last_month->getCurrentState('complete', 'admin') . '_tooltip']] )?>
                            <? endif ?>
                        <? elseif ($timesheet_next_month): ?>
                            <?= Icon::create($status_infos['finished']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['finished'][$timesheet_next_month->getCurrentState('finished', 'admin') . '_tooltip']] )?>      
                            <?= Icon::create($status_infos['approved']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['approved'][$timesheet_next_month->getCurrentState('approved', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['received']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['received'][$timesheet_next_month->getCurrentState('received', 'admin') . '_tooltip']] )?> 
                            <?= Icon::create($status_infos['complete']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['complete'][$timesheet_next_month->getCurrentState('complete', 'admin') . '_tooltip']] )?>
                        
                        <? endif ?>
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
                    <td><?= ($timesheet->month_completed) ? $timesheet->timesheet_balance : 'ausstehend' ?></td>
                    <td>
                        <?= ($timesheet->finished) ?  
                             Icon::create($status_infos['finished']['icon'], $status_infos['true_icon_role'], ['title' =>  $status_infos['finished']['true_tooltip']] ) :
                             Icon::create($status_infos['finished']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['finished']['false_tooltip']] ) 
                        ?>
                        <?= ($timesheet->approved) ?  
                             Icon::create($status_infos['approved']['icon'], $status_infos['true_icon_role'], ['title' =>  $status_infos['approved']['true_tooltip']] ) :
                             Icon::create($status_infos['approved']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['approved']['false_tooltip']] ) 
                        ?>
                        <?= ($timesheet->received) ?  
                             Icon::create($status_infos['received']['icon'], $status_infos['true_icon_role'], ['title' =>  $status_infos['received']['true_tooltip']] ) :
                             Icon::create($status_infos['received']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['received']['false_tooltip']] ) 
                        ?>
                        <?= ($timesheet->complete) ?  
                             Icon::create($status_infos['complete']['icon'], $status_infos['true_icon_role'], ['title' =>  $status_infos['complete']['true_tooltip']] ) :
                             Icon::create($status_infos['complete']['icon'], $status_infos['false_icon_role'], ['title' =>  $status_infos['complete']['false_tooltip']] ) 
                        ?>
                    </td>
                </tr>
                <?php endforeach ?>
            <? endif ?>
        </tbody>
    </table>

<?php endif ?>    
    
</div>

</body>

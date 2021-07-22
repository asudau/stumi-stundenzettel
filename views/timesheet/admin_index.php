<div>

<? if ($adminrole || $supervisorrole) : ?>    
    <h2>Status Stundenzettel <?= strftime("%B", mktime(0, 0, 0, $month, 10)) ?> <?= $year ?>  </h2>
    <p>Monat/Jahr: 
        <form name="month_select" method="post"  action="<?= $controller->link_for('timesheet/admin_index/') ?>">
            <select name ='month' onchange="this.form.submit()">
                <? foreach ($plugin->getMonths() as $entry_value): ?>
                    <option <?= ($month == $entry_value) ? 'selected' : '' ?> value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
                <? endforeach ?>
            </select>
            <select  name ='year' onchange="this.form.submit()">
                <? foreach ($plugin->getYears() as $entry_value): ?>
                    <option <?= ($year == $entry_value) ? 'selected' : '' ?> value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
                <? endforeach ?>
            </select>
        </form>
    </p>

    <table id='stumi-timesheet-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Nachname, Vorname</th>
                <th data-sort="false" style='width:10%'>Stundenkonto</th>
                <th data-sort="htmldata" style='width:10%'>Status</br>Stundenzettel</br><?= strftime("%B", mktime(0, 0, 0, $month, 10))?> </th>
                <th data-sort="false" style='width:10%'>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <? if ($contracts) : ?>
                <? foreach ($contracts as $contract): ?>
                <tr>  
                    <? $timesheet = $timesheets[$contract->id]; ?>
                    <td>
                        <a href='<?=$this->controller->link_for('timesheet/index/' . $contract->id) ?>' title='Stundenzettel einsehen'><?= htmlready($contract->stumi->nachname) ?>, <?= htmlready($contract->stumi->vorname) ?></a>
                    </td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getWorktimeBalance())) ?></td>
                    <td data-sort-value= <?= htmlready($timesheet->int_status) ?> >  
                        <? if ($timesheet) : ?>
                            <?= Icon::create($status_infos['finished']['icon'], $status_infos[$timesheet->getCurrentState('finished', 'admin') . '_icon_role'], ['title' =>  $status_infos['finished'][$timesheet->getCurrentState('finished', 'admin') . '_tooltip']] )?>
                            <?= Icon::create($status_infos['approved']['icon'], $status_infos[$timesheet->getCurrentState('approved', 'admin') . '_icon_role'], ['title' =>  $status_infos['approved'][$timesheet->getCurrentState('approved', 'admin') . '_tooltip']] )?>
                            <?= Icon::create($status_infos['received']['icon'], $status_infos[$timesheet->getCurrentState('received', 'admin') . '_icon_role'], ['title' =>  $status_infos['received'][$timesheet->getCurrentState('received', 'admin') . '_tooltip']] )?>
                            <?= Icon::create($status_infos['complete']['icon'], $status_infos[$timesheet->getCurrentState('complete', 'admin') . '_icon_role'], ['title' =>  $status_infos['complete'][$timesheet->getCurrentState('complete', 'admin') . '_tooltip']] )?>
                        <? else : ?>
                            <?= Icon::create($status_infos['finished']['icon'], Icon::ROLE_INACTIVE, ['title' => 'Stundenzettel noch nicht angelegt'])?>
                            <?= Icon::create($status_infos['approved']['icon'], Icon::ROLE_INACTIVE, ['title' =>  'Stundenzettel noch nicht angelegt'] )?>
                                <?= Icon::create($status_infos['received']['icon'], Icon::ROLE_INACTIVE, ['title' =>  'Stundenzettel noch nicht angelegt'] )?>
                                <?= Icon::create($status_infos['complete']['icon'], Icon::ROLE_INACTIVE, ['title' =>  'Stundenzettel noch nicht angelegt'] )?>
                        <? endif ?>
                    </td>
                    <td>
                        <? if ($adminrole && $timesheet) : ?>
                            <? if ($timesheet->finished) : ?>
                                <a href="<?= PluginEngine::getLink($plugin, [], 'timesheet/unlock/' . $timesheet->id ) ?>" data-confirm="Einreichen Rückgängig machen? Der Stumi kann diesen Stundnezettel dann wieder bearbeiten und Sie haben erst wieder Zugriff wenn dieser erneut eingereicht wurde.">
                                    <?= Icon::create('rotate-left', ['title' =>  'Einreichen rückgängig machen'] )?>
                                </a>
                            <? endif ?>
                            <a href='<?=$this->controller->link_for('timesheet/received/' . $timesheet->id) ?>' 
                                title='<?= ($timesheet->getCurrentState('received', 'admin') == 'true') ? "Bestätigen für Vorliegen zurückziehen" : "Vorliegen bestätigen" ?>' >
                                <?= Icon::create($status_infos['received']['icon']) ?>
                            </a>
                            <a href='<?=$this->controller->link_for('timesheet/complete/' . $timesheet->id) ?>' 
                                title='<?= ($timesheet->getCurrentState('complete', 'admin') == 'true') ? 'Vorgang wieder öffnen' : 'Vorgang abschließen' ?>' >
                                <?= Icon::create($status_infos['complete']['icon']) ?>
                            </a>
                        <? elseif ($supervisorrole && $timesheet) : ?>
                            <? if ($timesheet->finished) : ?>
                                <a href="<?= PluginEngine::getLink($plugin, [], 'timesheet/unlock/' . $timesheet->id ) ?>" data-confirm="Einreichen Rückgängig machen? Der Stumi kann diesen Stundnezettel dann wieder bearbeiten und Sie haben erst wieder Zugriff wenn dieser erneut eingereicht wurde.">
                                    <?= Icon::create('rotate-left', ['title' =>  'Einreichen rückgängig machen'] )?>
                                </a>
                            <? endif ?>
                            <a href='<?=$this->controller->link_for('timesheet/approved/' . $timesheet->id) ?>' 
                                title='<?= ($timesheet->getCurrentState('approved', 'supervisor') == 'true') ? "Korrektheit der Angaben bestätigen" : "Bestätigung der Korrektheit zurückziehen" ?>' >
                                <?= Icon::create($status_infos['approved']['icon']) ?>
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


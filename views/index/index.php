<div>
    
<? if ($adminrole) : ?>    
    <? foreach ($inst_ids as $inst_id) : ?>
        <? if ($adminrole) : ?>
           <h2> 
               <a  href='<?=$this->controller->link_for('index/edit_institute_settings/' . $inst_id) ?>' title='Konfiguration bearbeiten' data-dialog='size=auto'>
                   <?=Icon::create('edit')?>
               </a>              
               <?= htmlready(Institute::find($inst_id)->name) ?> </h2>
       <? else : ?>
           <h2> Studentische MitarbeiterInnen </h2>
       <? endif ?>
        <? foreach ($groups[$inst_id] as $group) : ?>
        
            <h3><?= $group->name ?></h3>
                    
            <table id='stumi-contract-entries' class="sortable-table default">
                <thead>
                    <tr>
                        <th data-sort="text" style='width:10%'>Nachname, Vorname</th>
                        <th data-sort="htmldata" style='width:10%'>Vertrags- <br>beginn</th>
                        <th data-sort="htmldata" style='width:10%'>Vertrags- <br>ende</th>
                        <th data-sort="digit" style='width:7%'>Monats- </br>stunden/ </br> Tagessatz</th>
                        <th data-sort="digit" style='width:7%'>Stundenkonto</br>(exkl. <?= strftime('%B') ?>)</th>
                        <th data-sort="false" style='width:10%'>Urlaub in Anspruch genommen <?= date('Y') ?></th>
                        <th data-sort="digit" style='width:7%'>Resturlaub</br><?= date('Y') ?></th>
                        <th data-sort="false" style='width:10%'>Urlaubsanspruch</br><?= date('Y') ?></th>
                        <th data-sort="digit" style='width:10%'>Resturlaub</br><?= date('Y')-1 ?></br>zu Jahresbeginn</th>
                        <th data-sort="false" style='width:10%'>Verantwortliche/r MA</th>
                        <th style='width:10%'>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                <? foreach ($inst_data[$inst_id][$group->id]->stumis as $stumi): ?>
                    <? if ($inst_data[$inst_id][$group->id]->stumi_contracts[$stumi->user_id]) : ?>
                        <? foreach ($inst_data[$inst_id][$group->id]->stumi_contracts[$stumi->user_id] as $contract): ?>
                        <tr>  
                            <td title='Kennung: <?= $stumi->username ?>'><a href='<?=$this->controller->link_for('timesheet/index/' . $contract->id) ?>' ><?= htmlready($stumi->nachname) ?>, <?= htmlready($stumi->vorname) ?></a>
                            </td>
                            <td data-sort-value="<?= $contract->contract_begin ?>"><?= date('d.m.Y', $contract->contract_begin) ?>
                                <? if ($contract->begin_digital_recording_month && $contract->begin_digital_recording_year) : ?>
                                    <?= Icon::create('info-circle', Icon::ROLE_CLICKABLE,  
                                            ['title' => 'Beginn der elektronischen Erfassung: ' . $contract->begin_digital_recording_month . '/' . $contract->begin_digital_recording_year ]);?>
                                <? endif ?>
                            </td>
                            <td data-sort-value="<?= $contract->contract_end ?>"><?= date('d.m.Y', $contract->contract_end) ?></td>
                            <td><?= htmlready($contract->contract_hours) ?> / <?= htmlready($contract->default_workday_time) ?></td>
                            <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getWorktimeBalance())) ?></td>
                            <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getClaimedVacation(date('Y'))))?></td>
                            <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getRemainingVacation(date('Y'))))?></td>
                            <td><?= htmlready($contract->getVacationEntitlement(date('Y')))?></td>
                            <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getRemainingVacationAtEndOfYear(date('Y')-1)))?></td>
                            <td title='<?= User::find($contract->supervisor)->username ?>'><?= sprintf('%s %s', User::find($contract->supervisor)->vorname, User::find($contract->supervisor)->nachname) ?></td>
                            <td>
                                <? if ($adminrole) : ?>
                                    <a data-confirm='Eintrag löschen?' href='<?=$this->controller->link_for('index/delete/' . $contract->id) ?>' title='Eintrag löschen' ><?=Icon::create('trash')?></a>
                                    <a  href='<?=$this->controller->link_for('index/edit/' . $contract->id) ?>' title='Vertragsdaten bearbeiten' data-dialog='size=auto'><?=Icon::create('edit')?></a>
                                    <a  href='<?=$this->controller->link_for('index/add_contract_begin_data/' . $contract->id) ?>' title='Zeitpunkt für Beginn digitaler Erfassung defnieren' data-dialog='size=auto'><?=Icon::create('date')?></a>
                                    <a  href='<?=$this->controller->link_for('index/edit/'. $contract->id . '/1') ?>' title='Folgevertrag anlegen' data-dialog='size=auto'><?=Icon::create('add')?></a>
                                    <? endif ?>
                            </td>

                        </tr>
                        <? endforeach ?>

                   <? elseif ($adminrole) : ?>
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
                               <a  href='<?=$this->controller->link_for('index/new/'. $inst_id . '/' . $stumi->user_id) ?>' title='Vertrag hinzufügen' data-dialog='size=auto'><?=Icon::create('add')?></a>
                            </td>
                            </tr>

                    <? endif ?>
                <? endforeach ?>

                </tbody>
            </table>
        <? endforeach ?>
    <? endforeach ?>
    
<? elseif ( $supervisorrole ) : ?>
    <h2> Vertragsdaten Hilfskräfte </h2>
        <table id='stumi-contract-entries' class="sortable-table default">
            <thead>
                <tr>
                    <th data-sort="text" style='width:10%'>Nachname, Vorname</th>
                    <th data-sort="htmldata" style='width:10%'>Vertrags- <br>beginn</th>
                    <th data-sort="htmldata" style='width:10%'>Vertrags- <br>ende</th>
                    <th data-sort="digit" style='width:7%'>Monats- </br>stunden/ </br>Tagessatz</th>
                    <th data-sort="digit" style='width:7%'>Stundenkonto</br>(exkl. <?= strftime('%B') ?>)</th>
                    <th data-sort="false" style='width:10%'>Urlaub in Anspruch genommen <?= date('Y') ?></th>
                    <th data-sort="digit" style='width:7%'>Resturlaub</br><?= date('Y') ?></th>
                    <th data-sort="false" style='width:10%'>Urlaubsanspruch</br><?= date('Y') ?></th>
                    <th data-sort="digit" style='width:10%'>Resturlaub</br><?= date('Y')-1 ?></br>zu Jahresbeginn</th>
                </tr>
            </thead>
            <tbody>
            <? foreach ($stumis as $stumi): ?>
                <? foreach ($stumi_contracts[$stumi->user_id] as $contract): ?>
                <tr>  
                    <td><a href='<?=$this->controller->link_for('timesheet/index/' . $contract->id) ?>' title='Stundenzettel einsehen'><?= htmlready($stumi->nachname) ?>, <?= htmlready($stumi->vorname) ?></a>
                    </td>
                    <td data-sort-value="<?= $contract->contract_begin ?>"><?= date('d.m.Y', $contract->contract_begin) ?>
                        <? if ($contract->begin_digital_recording_month && $contract->begin_digital_recording_year) : ?>
                            <?= Icon::create('info-circle', Icon::ROLE_CLICKABLE,  
                                    ['title' => 'Beginn der elektronischen Erfassung: ' . $contract->begin_digital_recording_month . '/' . $contract->begin_digital_recording_year ]);?>
                        <? endif ?>
                    </td>
                    <td data-sort-value="<?= $contract->contract_end ?>"><?= date('d.m.Y', $contract->contract_end) ?></td>
                    <td><?= htmlready($contract->contract_hours) ?> / <?= htmlready($contract->default_workday_time) ?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getWorktimeBalance())) ?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getClaimedVacation(date('Y'))))?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getRemainingVacation(date('Y'))))?></td>
                    <td><?= htmlready($contract->getVacationEntitlement(date('Y')))?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getRemainingVacationAtEndOfYear(date('Y')-1)))?></td>

                </tr>
                <? endforeach ?>
            <? endforeach ?>
            </tbody>
        </table>
                    

<? elseif ($stumirole) : ?>
    <h2> Meine Verträge </h2>
    <table id='stumi-contract-entries' class="sortable-table default">
        <thead>
            <tr>
                <th data-sort="text" style='width:10%'>Institut/Organisationseinheit</th>
                <th data-sort="false" style='width:10%'>Vertragsbeginn</th>
                <th data-sort="false" style='width:10%'>Vertragsende</th>
                <th data-sort="false" style='width:10%'>Stunden lt. Vertrag</th>
                <th data-sort="false" style='width:10%'>Stundenkonto (exkl. <?= strftime('%B') ?>)</th>
<!--                <th data-sort="false" style='width:10%'>Resturlaub/Urlaubsanspruch <?= date('Y') ?></th>                -->
                <th data-sort="false" style='width:10%'>Verantwortliche/r MA</th>
                <th data-sort="false" style='width:10%'>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($stumi_contracts as $contract): ?>
                <tr>
                    <td><?= htmlready(Institute::find($contract->inst_id)->name) ?></td>
                    <td><?= date('d.m.Y', $contract->contract_begin) ?>
                        <? if ($contract->begin_digital_recording_month && $contract->begin_digital_recording_year) : ?>
                                <?= Icon::create('info-circle', Icon::ROLE_CLICKABLE,  
                                        ['title' => 'Beginn der elektronischen Erfassung: ' . $contract->begin_digital_recording_month . '/' . $contract->begin_digital_recording_year ]);?>
                        <? endif ?>
                    </td>
                    <td><?= date('d.m.Y', $contract->contract_end) ?></td>
                    <td><?= htmlready($contract->contract_hours) ?></td>
                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getWorktimeBalance())) ?></td>
<!--                    <td><?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($contract->getRemainingVacation(date('Y')))) ?>/<?= htmlready($contract->getVacationEntitlement(date('Y'))) ?></td>-->
                    <td title='<?= User::find($contract->supervisor)->username ?>'><?= sprintf('%s %s', User::find($contract->supervisor)->vorname, User::find($contract->supervisor)->nachname) ?></td>
                    <td>
                        <a data-dialog="title='eMail an verantwortliche/n MA';size=1000x800;" 
                            href="<?=$this->controller->link_for('index/mail/' . $contract->supervisor)?>" 
                            title="eMail an verantwortliche/n MA">
                            <?= Icon::create('mail') ?>
                        </a>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>

<? endif ?>    
    
</div>

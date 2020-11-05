<html>

<body>
    <div>
<h> <?= Institute::find($inst_id)->name ?>: <?= sizeof($contracts) ?> Stumis </h1>

<table id='stumi-contract-entries' class="sortable-table default">
    <thead>
        <tr>
            <th data-sort="text" style='width:10%'>Nachame</th>
            <th data-sort="false" style='width:10%'>Vorname</th>
            <th data-sort="false" style='width:10%'>Vertragsbeginn</th>
            <th data-sort="false" style='width:10%'>Vertragsende</th>
            <th data-sort="false" style='width:10%'>Stunden</th>
            <th data-sort="false" style='width:10%'>Verantwortlicher/r MA</th>
            <th>Aktionen</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($contracts as $contract): ?>
    <tr>

        <td><a href='<?=$this->controller->url_for('index/edit/' . $contract['id']) ?>' title='Eintrag editieren' data-dialog="size=big"><?= User::findOneByUser_Id($contract->stumi_id)->username ?></a>

        <br/></td>
        <td></td>
        <td><?= date('d.m.Y',$contract->contract_begin) ?></td>
        <td><?= date('d.m.Y', $contract->contract_end) ?></td>
        <td><?= $contract->contract_hours ?></td>
        <td><?= User::findOneByUser_Id($contract->supervisor)->username ?></td>
        <td>
           <a onclick="return confirm('Eintrag löschen?')" href='<?=$this->controller->url_for('index/delete/' . $entry['id']) ?>' title='Eintrag löschen' ><?=Icon::create('trash')?></a>
        </td>

    </tr>
    <?php endforeach ?>
    </tbody>
</table>
</div>


</body>

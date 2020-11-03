<html>

<body>
    <div>
<h> <?= Institute::find($inst_id)->name ?>: <?= sizeof($entries) ?> Stumis </h1>

<table id='stumi-contract-entries' class="sortable-table default">
    <thead>
        <tr>
            <th data-sort="false" style='width:5%'>Name</th>
            <th data-sort="text" style='width:10%'>Status</th>
            <th data-sort="false" style='width:5%'></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($entries as $entry): ?>
    <tr>

        <td><a href='<?=$this->controller->url_for('index/edit/' . $entry['id']) ?>' title='Eintrag editieren' data-dialog="size=big"><?= User::findOneByUser_Id($entry->stumi_id)->username ?></a>

        <br/></td>
        
        <td></td>
        <td>
           <a onclick="return confirm('Eintrag löschen?')" href='<?=$this->controller->url_for('index/delete/' . $entry['id']) ?>' title='Eintrag löschen' ><?=Icon::create('trash')?></a>
        </td>

    </tr>
    <?php endforeach ?>
    </tbody>
</table>
</div>


</body>

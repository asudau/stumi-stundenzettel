
<?
use Studip\Button, Studip\LinkButton;
?>

<? if (!$timesheet) : ?>
<? else : ?>

    <h2>Name, Vorname der Hilfskraft: <?= User::findOneByUser_Id($stumi_id)->username ?></h2>
    <h2>Fachbereich/Organisationseinheit: <?= Institute::find($inst_id)->name ?></h2>
    <h2>Monat/Jahr: 
        <form name="month_select" method="post"  action="<?= $controller->url_for('timesheet/select/' . $timesheet->contract_id) ?>">
            <select name ='month' onchange="this.form.submit()">
                <?php foreach ($plugin->getMonths() as $entry_value): ?>
                    <option <?= ($timesheet->month == $entry_value) ? 'selected' : '' ?> value="<?=$entry_value?>"><?= $entry_value ?></option>
                <?php endforeach ?>
            </select>
            <select  name ='year' onchange="this.form.submit()">
                <?php foreach ($plugin->getYears() as $entry_value): ?>
                    <option <?= ($timesheet->year == $entry_value) ? 'selected' : '' ?> value="<?=$entry_value?>"><?= $entry_value ?></option>
                <?php endforeach ?>
            </select>
        </form>
        
    </h2>

<form name="entry" method="post" onsubmit="return validateForm()" action="<?= $controller->url_for('timesheet/save_timesheet', $timesheet->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    
        <table class='sortable-table default'>
            <tr>
                <th style='width:10px'>Tag</th>
                <th style='width:110px'>Beginn</th>
                <th style='width:110px'>Pause</th>
                <th style='width:110px'>Ende</th>
                <th style='width:110px'>Dauer</th>
                <th style='width:110px'>Aufgezeichnet am</th>
                <th style='width:110px'>Bemerkung</th>
                <th style='width:200px'>sonstige Bemerkung</th>
                
            </tr>

            <?php $j = 0; ?>
         
            <?php for ($i = 1; $i <= $days_per_month; $i++) : ?>
                <?php if ($records[$j]['day'] == $i ) : ?>
                    <? $holiday = $records[$j]->isHoliday(); ?>
                    <? $weekend = $records[$j]->isWeekend(); ?>
                    <? $date = $records[$j]->getDate(); ?>
                    <? $is_editable = StundenzettelRecord::isEditable($date); ?>
                    <tr id ='entry[<?= $i ?>]' class='<?= ($weekend)? 'weekend' : ''?> <?= ($holiday)? 'holiday' : ''?> <?= $records[$j]['defined_comment'] ?>' >
                        <input type='hidden' name ='record_id[<?= $i ?>]' value='<?= $records[$j]['id'] ?>' >
                        <td>
                           <?= $i ?>
                        </td>
                        <td >
                            <input type='text' <?= (!$is_editable)? 'readonly' : ''?> class='begin size-s studip-timepicker' id ='' name ='begin[<?= $i ?>]' value='<?= $records[$j]['begin'] ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input type='text' <?= (!$is_editable)? 'readonly' : ''?> class='break' name ='break[<?= $i ?>]' value ='<?= $records[$j]['break'] ?>' placeholder="hh:mm">
                        </td>
                        <td>
                            <input type='text' <?= (!$is_editable)? 'readonly' : ''?> class='end size-s studip-timepicker' id ='' name ='end[<?= $i ?>]' value='<?= $records[$j]['end'] ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                           <input type='text' readonly class='sum' name ='sum[<?= $i ?>]' value ='<?= $records[$j]['sum'] ?>' >
                        </td>
                        <td>
                           <input type='date' <?= (!$is_editable)? 'readonly' : ''?> class='entry_mktime' name ='entry_mktime[<?= $i ?>]' value='<?= $records[$j]['entry_mktime'] ?>' >
                        </td>
                        <td>
                           <select <?= (!$is_editable)? 'disabled' : ''?> class='defined_comment' name ='defined_comment[<?= $i ?>]'>
                                <option value=""> -- </option>
                                <?php foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option 
                                    <?= ($records[$j]['defined_comment'] == $entry_value) ? 'selected' : ''?> 
                                    <?= ($entry_value == 'Feiertag') ? 'disabled' : '' ?> 
                                        value="<?=$entry_value?>"><?= $entry_value ?></option>
                                <?php endforeach ?>
                            </select>
                        </td>
                        <td>
                           <input type='text' <?= (!$is_editable)? 'readonly' : ''?> id ='' name ='comment[<?= $i ?>]' value ='<?= $records[$j]['comment'] ?>' >
                        </td>
                    </tr>
                    <?php $j++; ?>
                <?php else: ?>
                    <? $date = $i . '.' . $timesheet->month . '.'  . $timesheet->year; ?>
                    <? $weekend = StundenzettelRecord::isDateWeekend($date); ?>
                    <? $holiday = StundenzettelRecord::isDateHoliday($date); ?>
                    <? $is_editable = StundenzettelRecord::isEditable($date); ?>
                    <tr id ='entry[<?= $i ?>]' class='<?= ($weekend)? 'weekend' : ''?> <?= ($holiday)? 'holiday' : ''?>' >
                        <input type='hidden' name ='record_id[<?= $i ?>]' value='' >
                        <td>
                           <?= $i ?>
                        </td>
                        <td >
                            <input type='text' <?= (!$is_editable)? 'readonly' : ''?> class='begin' id ='' name ='begin[<?= $i ?>]' value='' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input type='text' <?= (!$is_editable)? 'readonly' : ''?> class ='break' name ='break[<?= $i ?>]' value ='' placeholder="hh:mm">
                        </td>
                        <td>
                            <input type='text' <?= (!$is_editable)? 'readonly' : ''?> class='end' name ='end[<?= $i ?>]' value='' placeholder="hh:mm" >
                        </td>
                        <td>
                           <input type='text' readonly class ='sum' name ='sum[<?= $i ?>]' value ='<?= (!$weekend && $holiday) ? $timesheet->contract->default_workday_time : ''?>' >
                        </td>
                        <td>
                           <input type='date' <?= (!$is_editable)? 'readonly' : ''?> class ='entry_mktime' name ='entry_mktime[<?= $i ?>]' value='' >
                        </td>
                        <td>
                           <select <?= (!$is_editable)? 'disabled' : ''?> class='defined_comment' name ='defined_comment[<?= $i ?>]'>
                                <option <?= ($holiday) ? '' : 'selected' ?> value=""> -- </option>
                                <?php foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option 
                                        <?= ($holiday && ($entry_value == 'Feiertag')) ? 'selected' : ''?> 
                                        <?= ($entry_value == 'Feiertag') ? 'disabled' : '' ?> value="<?=$entry_value?>"><?= $entry_value ?></option>
                                <?php endforeach ?>
                            </select>
                        </td>
                        <td>
                           <input type='text' <?= (!$is_editable)? 'readonly' : ''?> class ='comment' name ='comment[<?= $i ?>]' value ='' >
                        </td>
                    </tr>
                <?php endif ?>
            <?php endfor ?>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                       <input type='text' class ='comment' name ='' value ='<?= $timesheet->sum ?>' >
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
        </table>


    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>
    
<? endif ?>    
    

<style>
    tr.weekend {
        background-color: #ccc;
    }
    tr.holiday {
        background-color: #6da6b3;
    }
    tr.Urlaub {
        background-color: #6db370;
    }
    tr.Krank {
        background-color: #f4d33a;
    }  
    
</style>

<script>
    
    $(function() {
        inputs = document.getElementsByClassName('Krank');
        for (index = 0; index < inputs.length; ++index) {
            var name = inputs[index].getAttribute("id");
            var rec_index = name.substring(5, 10);
            disable_timetracking(rec_index);
        }
        inputs = document.getElementsByClassName('Urlaub');
        for (index = 0; index < inputs.length; ++index) {
            var name = inputs[index].getAttribute("id");
            var rec_index = name.substring(5, 10);
            disable_timetracking(rec_index);
        }
        inputs = document.getElementsByClassName('holiday');
        for (index = 0; index < inputs.length; ++index) {
            var name = inputs[index].getAttribute("id");
            var rec_index = name.substring(5, 10);
            disable_timetracking(rec_index);
        }
    });
    
    function calculate_sum(begin, end, brk){
        
        if (begin && end){
            if (!brk) {
              brk = '00:00';  
            }
            var begin_array = begin.split(':');
            var end_array = end.split(':');
            var break_array = brk.split(':');

            var begin_minutes = begin_array[1];
            var begin_hours = begin_array[0];

            var end_minutes = end_array[1];
            var end_hours = end_array[0];

            var break_minutes = break_array[1];
            var break_hours = break_array[0];

            var minutes_total = 0;
            var hours_total = 0;

            if ((+begin_minutes + +break_minutes) >= 60) {
                begin_hours = +begin_hours + 1;
                begin_minutes = (+begin_minutes + +break_minutes) - 60;
            } else {
                begin_minutes = +begin_minutes + +break_minutes;
            }
            begin_hours = +begin_hours + +break_hours;

            if ((+end_minutes + (60 - +begin_minutes)) >= 60) {
                minutes_total = (+end_minutes + (60 - +begin_minutes)) - 60;
            } else {
                end_hours -= 1;
                minutes_total = +end_minutes + (60 - +begin_minutes);
            }

            hours_total = +end_hours - +begin_hours;
            return (hours_total + ':' + minutes_total);
        }
   
    }
    
    function autofill_row(row_index){
        var begin = document.getElementsByName('begin' + row_index)[0].value;
        var end = document.getElementsByName('end' + row_index)[0].value;
        var brk = document.getElementsByName('break' + row_index)[0].value;
        var res = calculate_sum(begin, end, brk);
        document.getElementsByName('sum' + row_index)[0].value = res;
    }
    
    function set_mktime(row_index){
        var today = new Date().toISOString().slice(0, 10);
        document.getElementsByName('entry_mktime' + row_index)[0].value = today;
    }
    
    function getDateofRow(row_index){
        var month = <?= $timesheet->month ?>;
        var year = <?= $timesheet->year ?>;
        var day = row_index;
        var row_date = new Date(year + '-' + month + '-' + day);
        return row_date;
        
    }
    
    function disable_timetracking(row_index){
        document.getElementsByName('begin' + row_index)[0].value = '';
        document.getElementsByName('begin' + row_index)[0].style.display = 'none';
        document.getElementsByName('end' + row_index)[0].value = '';
        document.getElementsByName('end' + row_index)[0].style.display = 'none';
        document.getElementsByName('break' + row_index)[0].value = '';
        document.getElementsByName('break' + row_index)[0].style.display = 'none';
    }
    
    function enable_timetracking(row_index){
        document.getElementsByName('begin' + row_index)[0].style.removeProperty("display");
        document.getElementsByName('end' + row_index)[0].style.removeProperty("display");
        document.getElementsByName('break' + row_index)[0].style.removeProperty("display");
    }
    
    function autocalc_sum(row_index){
        var default_workday_time = '<?= $timesheet->contract->default_workday_time ?>';
        document.getElementsByName('sum' + row_index)[0].value = default_workday_time;
    }
    
    
    var inputs, index;
   
    inputs = document.getElementsByClassName('begin');
    for (index = 0; index < inputs.length; ++index) {
        inputs[index].onchange = function () {
            var name = this.getAttribute("name");
            var rec_index = name.substring(5, 9);
            autofill_row(rec_index);
            set_mktime(rec_index);
        };
    }
    
    inputs = document.getElementsByClassName('end');
    for (index = 0; index < inputs.length; ++index) {
        inputs[index].onchange = function () {
            var name = this.getAttribute("name");
            var rec_index = name.substring(3, 7);
            autofill_row(rec_index);
            set_mktime(rec_index);
        };
    }
    
    inputs = document.getElementsByClassName('break');
    for (index = 0; index < inputs.length; ++index) {
        inputs[index].onchange = function () {
            var name = this.getAttribute("name");
            var rec_index = name.substring(5, 9);
            autofill_row(rec_index);
            set_mktime(rec_index);
        };
    }
     
    //handling of Urlaub and Krank
    inputs = document.getElementsByClassName('defined_comment');
    for (index = 0; index < inputs.length; ++index) {
        inputs[index].onchange = function () {
            var name = this.getAttribute("name");
            var rec_index = name.substring(15, 19);
            if(this.value == '')  {
                enable_timetracking(rec_index);
                document.getElementsByName('sum' + rec_index)[0].value = '';
                document.getElementsByName('entry_mktime' + rec_index)[0].value = '';
            } else {
                disable_timetracking(rec_index);
                autocalc_sum(rec_index);
            }
            document.getElementById('entry' + rec_index).removeAttribute("class");
            document.getElementById('entry' + rec_index).classList.add(this.value);
            set_mktime(rec_index);
        };
    }

</script>
        
        
<script>

    function validateForm() {
    var value = e.options[e.selectedIndex].value;
        if (value == "NULL") {
        alert("Promotionsfach muss angegeben werden!");
        return false;
    }
}

    inputs = document.getElementsByTagName('begin');
    for (index = 0; index < inputs.length; ++index) {
        // deal with inputs[index] element.
        inputs[index].onchange = function () {
            alert('test');
            var begin = document.getElementsByName('begin[1]')[0].value;
            var end = document.getElementsByName('end[1]')[0].value;
            document.getElementsByName('sum[1]')[0].value = begin + end;
            document.getElementsByName('sum[1]')[0].removeAttribute("disabled");
            if (this.value != ''){
                document.getElementsByName(this.getAttribute("name"))[0].classList.remove("needs_fill");
            }
        };
    }


    //Ersteinschreibung Auslandshochschulen, dann Staat Pflichtfeld
    document.getElementsByName("hochschule_erst")[1].onchange = function () {
        if (this.value == '2'){
            document.getElementsByName("staat_hochschule_erst")[1].removeAttribute("disabled");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "";
        } else {
            //document.getElementsByName("staat_hochschule_erst")[1].value = "-- nicht erforderlich --";
            document.getElementsByName("staat_hochschule_erst")[1].setAttribute("disabled", true);
            //$("tr[staat_hochschule_erst]").hide();//.removeClass( "required");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "none";//.removeAttribute("class");
        }
        if (this.value != ''){
            document.getElementsByName(this.getAttribute("name"))[0].classList.remove("needs_fill");
        }
    };

    document.getElementsByName("art_reg_prom")[1].onchange = function () {
        if (this.value == '3' || this.value == '2' ){
            document.getElementsByName("promotionsende_monat")[1].removeAttribute("disabled");
            document.getElementsByName("promotionsende_monat")[0].classList.add("needs_fill");
            document.getElementsByName("promotionsende_jahr")[1].removeAttribute("disabled");
            document.getElementsByName("promotionsende_jahr")[0].classList.add("needs_fill");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "";
        } else {
            //document.getElementsByName("promotionsende_monat")[1].value = "-- nicht erforderlich --";
            document.getElementsByName("promotionsende_monat")[1].childNodes[1].value = 'NULL';
            document.getElementsByName("promotionsende_monat")[1].setAttribute("disabled", true);
            document.getElementsByName("promotionsende_monat")[0].classList.remove("needs_fill");
            document.getElementsByName("promotionsende_jahr")[1].value = '';
            document.getElementsByName("promotionsende_jahr")[1].setAttribute("disabled", true);
            document.getElementsByName("promotionsende_jahr")[0].classList.remove("needs_fill");
            //$("tr[staat_hochschule_erst]").hide();//.removeClass( "required");
            //document.getElementsByName("staat_hochschule_erst")[0].style.display = "none";//.removeAttribute("class");
        }
        if (this.value != ''){
            document.getElementsByName(this.getAttribute("name"))[0].classList.remove("needs_fill");
        }
    };

</script>

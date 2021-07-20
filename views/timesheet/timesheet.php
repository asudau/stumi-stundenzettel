<?
use Studip\Button, Studip\LinkButton;
?>

<div id='form_error' class="messagebox messagebox_error " style='display:none'>
    Speichern wegen fehlerhafter Werte nicht möglich. Bitte überprüfe deine Eingaben.
</div>

<? if ($no_timesheet) : ?>
    <p>Name, Vorname: <b><?= htmlready(User::find($contract->user_id)->nachname) ?>, <?= htmlready(User::find($contract->user_id)->vorname) ?></b></p>
    <p>Fachbereich/Organisationseinheit: <b><?= htmlready(Institute::find($contract->inst_id)->name) ?></b></p>

    <p>Monat/Jahr: 
        <form name="month_select" method="post"  action="<?= $controller->link_for('timesheet/select/' . $contract->id) ?>">
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
    
<? elseif ($timesheet->can_read(User::findCurrent()) || $adminrole): ?>
    <h2><?= strftime("%B", mktime(0, 0, 0, $timesheet->month, 10)) . ' ' . $timesheet->year;  ?> </h2>
    <p>Name, Vorname: <b><?= htmlready(User::find($timesheet->contract->user_id)->nachname) ?>, <?= htmlready(User::find($timesheet->contract->user_id)->vorname) ?></b></p>
    <p>Fachbereich/Organisationseinheit: <b><?= htmlready(Institute::find($inst_id)->name) ?></b></p>
    <p>Monatsarbeitszeit laut Arbeitsvertrag: <b><?= htmlready($timesheet->contract->contract_hours) ?> Stunden </b></p>
    <p>Diesen Monat erfasst: <b> <?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->sum)) ?> Stunden </b></p>
    <p>Monat/Jahr: 
        <form name="month_select" method="post"  action="<?= $controller->link_for('timesheet/select/' . $timesheet->contract_id) ?>">
            <select name ='month' onchange="this.form.submit()">
                <? foreach ($plugin->getMonths() as $entry_value): ?>
                    <option <?= ($timesheet->month == $entry_value) ? 'selected' : '' ?> value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
                <? endforeach ?>
            </select>
            <select  name ='year' onchange="this.form.submit()">
                <? foreach ($plugin->getYears() as $entry_value): ?>
                    <option <?= ($timesheet->year == $entry_value) ? 'selected' : '' ?> value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
                <? endforeach ?>
            </select>
        </form>
    </p> 

<!-- langfristig Formular für admins sperren (class  admin und locked-abfrage) -->
<form id="timesheet_form" method="post" class='<?= $adminrole ? '' : '' ?> <?= ($timesheet->locked && !$adminrole) ? 'locked' : '' ?>' action="<?= $controller->link_for('timesheet/save_timesheet', $timesheet->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <div style="overflow:scroll;">
        <table class='sortable-table default' style='width: auto;'>
            <tr>
                <th style='width:15px'>Tag</th>
                <th style='width:15px'></th>
                <th style='width:110px'>Beginn</th>
                <th style='width:110px'>Pause</th>
                <th style='width:110px'>Ende</th>
                <th style='width:110px'>Dauer</th>
                <th style='width:110px'>Aufgezeichnet am</th>
                <th style='width:110px'>Bemerkung</th>
                <th style='width:210px'>sonstige Bemerkung</th>
                <th style='width:100px'></th>
                
            </tr>

            <? $j = 0; ?>
         
            <? for ($i = 1; $i <= $days_per_month; $i++) : ?>
            
<!--                für diesen Tag liegt bereits ein Eintrag vor       -->
                <? if ($records[$j]['day'] == $i ) : ?>
                    <? $holiday = $records[$j]->isHoliday(); ?>
                    <? $weekend = $records[$j]->isWeekend(); ?>
                    <? $date = $records[$j]->getDate(); ?>
                    <? $uni_closed = StundenzettelRecord::isUniClosedOnDate($date); ?>
                    <? $is_editable = StundenzettelRecord::isEditable($date); ?>
                    <tr id ='entry[<?= $i ?>]' class='<?= ($weekend)? 'weekend' : ''?> <?= ($holiday)? 'holiday' : ''?> <?= htmlready($records[$j]['defined_comment']) ?>' >
                        <input type='hidden' name ='record_id[<?= $i ?>]' value='<?= htmlready($records[$j]['id']) ?>' >
                        <td>
                            <?= $i ?>
                        </td>
                        <td>
                            <?= strftime("%A", strtotime($date)) ?>
                        </td>
                        <td >
                            <input style='width: 80px;' type='text' pattern="<?= $time_begin_pattern ?>" <?= (!$is_editable)? 'readonly' : ''?> class='begin size-s studip-timepicker' id ='' name ='begin[<?= $i ?>]' value='<?= ($records[$j]['begin']) ? htmlready(StundenzettelTimesheet::stundenzettel_strftime('%H:%M', $records[$j]['begin'])) : '' ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input style='width: 80px;' type='text' pattern="<?= $break_pattern ?>" <?= (!$is_editable)? 'readonly' : ''?> class='break' name ='break[<?= $i ?>]' value ='<?= ($records[$j]['break']) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($records[$j]['break'])) : '' ?>' placeholder="hh:mm">
                        </td>
                        <td>
                            <input style='width: 80px;' type='text' pattern="<?= $time_end_pattern ?>" <?= (!$is_editable)? 'readonly' : ''?> class='end size-s studip-timepicker' id ='' name ='end[<?= $i ?>]' value='<?= ($records[$j]['end']) ? htmlready(StundenzettelTimesheet::stundenzettel_strftime('%H:%M', $records[$j]['end'])) : '' ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input style='width: 80px;' type='text' <?= ($records[$j]['defined_comment'] == 'Urlaub')? '' : 'readonly' ?> class='sum' name ='sum[<?= $i ?>]' value ='<?= ($records[$j]['sum']) ? htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($records[$j]['sum'])) : '' ?>' >
                        </td>
                        <td>
                           <input type='date'
                                  min="<?= date('Y-m-d', strtotime($date)) ?>" max="<?= date('Y-m-d', strtotime('+1 week', strtotime($date))) ?>" 
                                  <?= (!$is_editable)? 'readonly' : ''?> 
                                  class='entry_mktime <?= ($records[$j]['entry_mktime'] == '00-00-000') ? 'empty_date' :'' ?>' 
                                  name ='entry_mktime[<?= $i ?>]' value='<?= htmlready($records[$j]['entry_mktime']) ?>' >
                        </td>
                        <td>
                           <? if (!$uni_closed || $holiday || $weekend) : ?>
                           <select <?= ($holiday || $weekend)? 'disabled' : ''?> class='defined_comment' name ='defined_comment[<?= $i ?>]'>
                                <option value=""> -- </option>
                                <? foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option 
                                    <?= ($records[$j]['defined_comment'] == $entry_value) ? 'selected' : ''?> 
                                    <?= ($entry_value == 'Feiertag') ? 'disabled' : '' ?> 
                                    <?= (!$is_editable && $entry_value == 'Krank') ? 'disabled' : '' ?>  
                                        value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
                                <? endforeach ?>
                            </select>
                           <? else : ?>
                            <select class='defined_comment' name ='defined_comment[<?= $i ?>]'>
                                <option value=""> -- </option>
                                <? foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option 
                                        <?= ($records[$j]['defined_comment'] == $entry_value) ? 'selected' : ''?> 
                                        <?= ($entry_value == 'Urlaub' || $entry_value == 'Krank') ? '' : 'disabled' ?> value="<?=htmlready($entry_value)?>"><?= htmlready($entry_value) ?></option>
                                <? endforeach ?>
                            </select>
                            <? endif ?>
                        </td>
                        <td>
                           <input style='width:210px' type='text' <?= (!$is_editable)? 'readonly' : ''?> id ='' name ='comment[<?= $i ?>]' value ='<?= ($uni_closed)? 'Arbeitszeiterfassung nicht zulässig' : htmlready($records[$j]['comment']) ?>' >
                        </td>
                        <td>
                            <? if ($is_editable && !$timesheet->locked) : ?>
                             <a href='' class='edit_action' onclick='return clearLine(event, "[<?= $i ?>]"); return false;'> <?= Icon::create('decline') ?> </a>
                            <? endif ?>
                        </td>
                    </tr>
                    <? $j++; ?>
                    
<!--                für diesen Tag liegt noch kein Eintrag vor       -->
                <? else: ?>
                    <? $date = $i . '.' . $timesheet->month . '.'  . $timesheet->year; ?>
                    <? $weekend = StundenzettelRecord::isDateWeekend($date); ?>
                    <? $holiday = StundenzettelRecord::isDateHoliday($date); ?>
                    <? $uni_closed = StundenzettelRecord::isUniClosedOnDate($date); ?>
                    <? $is_editable = StundenzettelRecord::isEditable($date); ?>
                    <tr id ='entry[<?= $i ?>]' class='<?= ($weekend)? 'weekend' : ''?> <?= ($holiday)? 'holiday' : ''?>' >
                        <input type='hidden' name ='record_id[<?= $i ?>]' value='' >
                        <td>
                            <?= $i ?>
                        </td>
                        <td>
                            <?= strftime("%A", strtotime($date)) ?>
                        </td>
                        <td >
                            <input style='width: 80px;' type='text' pattern="<?= $time_begin_pattern ?>" <?= (!$is_editable)? 'readonly' : ''?> class='begin' id ='' name ='begin[<?= $i ?>]' value='' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input style='width: 80px;' type='text' pattern="<?= $break_pattern ?>" <?= (!$is_editable)? 'readonly' : ''?> class ='break' name ='break[<?= $i ?>]' value ='' placeholder="hh:mm">
                        </td>
                        <td>
                            <input style='width: 80px;' type='text' pattern="<?= $time_end_pattern ?>" <?= (!$is_editable)? 'readonly' : ''?> class='end' name ='end[<?= $i ?>]' value='' placeholder="hh:mm" >
                        </td>
                        <td>
                           <input style='width: 80px;' type='text' readonly class ='sum' name ='sum[<?= $i ?>]' value ='<?= (!$weekend && $holiday) ? htmlready($timesheet->contract->default_workday_time) : ''?>' >
                        </td>
                        <td>
                            <!-- data-datepicker='{">":<?= $date ?>}' -->
                           <input type='date'
                                  min="<?= date('Y-m-d', strtotime($date)) ?>" max="<?= date('Y-m-d', strtotime('+1 week', strtotime($date))) ?>" 
                                  <?= (!$is_editable)? 'readonly' : ''?> 
                                  class ='entry_mktime empty_date' 
                                  name ='entry_mktime[<?= $i ?>]' value='<?= ($holiday) ? date('Y-m-d', strtotime($date)) : '' ?>' >
                        </td>
                        <td>
                            
<!--                        für Feiertage und Wochenenden ggf. Bearbeitung sperren und passenden Wert setzen          -->
                            <? if (!$uni_closed || $holiday || $weekend) : ?>
                           <select <?= ($holiday || $weekend)? 'disabled' : ''?> class='defined_comment' name ='defined_comment[<?= $i ?>]'>
                                <option <?= ($holiday) ? '' : 'selected' ?> value=""> -- </option>
                                <? foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option 
                                        <?= ($holiday && ($entry_value == 'Feiertag')) ? 'selected' : ''?> 
                                        <?= ($entry_value == 'Feiertag') ? 'disabled' : '' ?> 
                                        <?= (!$is_editable && $entry_value == 'Krank') ? 'disabled' : '' ?> 
                                        value="<?= htmlready($entry_value)?>"><?= htmlready($entry_value) ?>
                                    </option>
                                <? endforeach ?>
                            </select>
<!--                        falls die Uni geschlossen ist          -->
                            <? else : ?>
                            <select class='defined_comment' name ='defined_comment[<?= $i ?>]'>
                                <option selected value=""> -- </option>
                                <? foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option 
                                        <?= ($entry_value == 'Urlaub') ? '' : 'disabled' ?> value="<?= htmlready($entry_value) ?>"><?= htmlready($entry_value) ?></option>
                                <? endforeach ?>
                            </select>
                            <? endif ?>
                        </td>
                        <td>
                           <input style='width:210px' type='text' <?= (!$is_editable)? 'readonly' : ''?> class ='comment' name ='comment[<?= $i ?>]' value ='<?= ($uni_closed)? 'Arbeitszeiterfassung nicht zulässig' :''?>' >
                        </td>
                         <td>
                        </td>
                    </tr>
                <? endif ?>
            <? endfor ?>
                <tr>
                    <td></td>
                    <td>Summe:</td>
                    <td>(wird beim Speichern berechnet)</td>
                    <td></td>
                    <td></td>
                    <td>
                        <input style='width:80px' type='text' readonly class ='comment' name ='' value ='<?= htmlready(StundenzettelTimesheet::stundenzettel_strftimespan($timesheet->sum)) ?>' >
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
        </table>
    </div>
    <? if ($stumirole || $adminrole) : ?>
        <footer id='timesheet_submit' data-dialog-button>
            <? if ($stumirole) : ?>
                <?= Studip\Button::create(_('Übernehmen')) ?>
            <? else: ?>
                <?= Studip\Button::create(_('Übernehmen'), 'save', [
                    'data-confirm' => _('Sind Sie sicher, dass Sie die Eingaben Überschreiben wollen!?'),
                ]) ?>
            <? endif ?>
        </footer>
    <? endif ?>  
</form>

    
<? if ($supervisorrole && $timesheet->finished) : ?>
    <?= LinkButton::create(_('Korrektheit bestätigen'), $controller->link_for('timesheet/approve/' . $timesheet->id) ) ?>
<? elseif ($adminrole && $timesheet->finished) : ?>
    <?= LinkButton::create(_('Eingang bestätigen'), $controller->link_for('timesheet/received/' . $timesheet->id) ) ?>
<? endif ?>    

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
    input[class*="empty_date"]::-webkit-datetime-edit {
        color: transparent; 
    }
    :invalid {
        box-shadow: none; /* FF */
        outline: 0;       /* IE */
        border-color: red;
    }
    
</style>

<script>
    
    var form = document.getElementById('timesheet_form');
    form.addEventListener('invalid', function(e) {
        document.getElementById('form_error').style.removeProperty("display");
        //alert('Speichern wegen fehlerhafter Werte nicht möglich. Bitte überprüfe deine Eingaben.');  
    }, true);
    
    form.addEventListener('valid', function(e) {
        document.getElementById('form_error').style.display = 'none';
        //alert('Speichern wegen fehlerhafter Werte nicht möglich. Bitte überprüfe deine Eingaben.');  
    }, true);
    
    
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
        
        var form = document.getElementById('timesheet_form');
        if (form.classList.contains('admin') || form.classList.contains('locked') ){
            var inputs = form.querySelectorAll("input, select");
            for(var i = 0; i < inputs.length; i++){
                inputs[i].setAttribute("disabled", true);;
            }
            var submit_button = document.getElementById('timesheet_submit');
            submit_button.style.display = 'none';
        }
    });
    
    function validateFormSaved() {
        alert("Ungespeicherte Änderungen!");
        return false;
    }
    
    function clearLine(event, row_index) {
        event.preventDefault();
        document.getElementsByName('begin' + row_index)[0].value = '';
        document.getElementsByName('begin' + row_index)[0].removeAttribute('required');
        document.getElementsByName('begin' + row_index)[0].removeAttribute('display');
        document.getElementsByName('end' + row_index)[0].value = '';
        document.getElementsByName('end' + row_index)[0].removeAttribute('required');
        document.getElementsByName('end' + row_index)[0].removeAttribute('display');
        document.getElementsByName('break' + row_index)[0].value = '';
        document.getElementsByName('break' + row_index)[0].removeAttribute('required');
        document.getElementsByName('break' + row_index)[0].removeAttribute('display');
        document.getElementsByName('sum' + row_index)[0].value = '';
        document.getElementsByName('comment' + row_index)[0].value = '';
        document.getElementsByName('defined_comment' + row_index)[0].value = '';
        document.getElementsByName('entry_mktime' + row_index)[0].value = '';
    }
    
    function calculate_sum(begin, end, brk){
        
        if (begin && end){
            if (!brk || brk == '0') {
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
            return (("0" + hours_total).slice (-2) + ':' + ("0" + minutes_total).slice (-2));
        } else return '';
   
    }
    
    function autofill_row(row_index){
        var begin = document.getElementsByName('begin' + row_index)[0].value;
        var end = document.getElementsByName('end' + row_index)[0].value;
        var brk = document.getElementsByName('break' + row_index)[0].value;
        if ((begin && end) && (brk == '0')) {
              document.getElementsByName('break' + row_index)[0].value = '00:00';
            }
        var res = calculate_sum(begin, end, brk);
        if (res.startsWith("-")) {
            alert("Gesamtsumme der Arbeitszeit pro Tag muss positiv sein.");
            document.getElementsByName('sum' + row_index)[0].value = '';
        } else {
            document.getElementsByName('sum' + row_index)[0].value = res;
        }
    }
    
    function set_mktime(row_index){
        var today = new Date().toISOString().slice(0, 10);
        document.getElementsByName('entry_mktime' + row_index)[0].value = today;
    }
    
    function getDateofRow(row_index){
        var month = <?= htmlready($timesheet->month) ?>;
        var year = <?= htmlready($timesheet->year) ?>;
        var day = row_index;
        var row_date = new Date(year + '-' + month + '-' + day);
        return row_date;
    }
    
    function set_time_inputs_in_row_required(row_index){
        document.getElementsByName('begin' + row_index)[0].setAttribute("required", "");
        document.getElementsByName('end' + row_index)[0].setAttribute("required", "");
        document.getElementsByName('entry_mktime' + row_index)[0].setAttribute("required", "");
    }
    
    function disable_timetracking(row_index){
        document.getElementsByName('begin' + row_index)[0].value = '';
        document.getElementsByName('begin' + row_index)[0].style.display = 'none';
        document.getElementsByName('begin' + row_index)[0].removeAttribute('required');
        document.getElementsByName('end' + row_index)[0].value = '';
        document.getElementsByName('end' + row_index)[0].style.display = 'none';
        document.getElementsByName('end' + row_index)[0].removeAttribute('required');
        document.getElementsByName('break' + row_index)[0].value = '';
        document.getElementsByName('break' + row_index)[0].style.display = 'none';
    }
    
    function enable_timetracking(row_index){
        document.getElementsByName('begin' + row_index)[0].style.removeProperty("display");
        document.getElementsByName('end' + row_index)[0].style.removeProperty("display");
        document.getElementsByName('break' + row_index)[0].style.removeProperty("display");
    }
    
    function autocalc_sum(row_index){
        var default_workday_time = '<?= htmlready($timesheet->contract->default_workday_time) ?>';
        document.getElementsByName('sum' + row_index)[0].value = default_workday_time;
    }
   
    $('.begin').change(function() {
        var name = this.getAttribute("name");
        var rec_index = name.substring(5, 9);
        autofill_row(rec_index);
        set_mktime(rec_index);
        set_time_inputs_in_row_required(rec_index);
    });
    
    $('.end').change(function() {
        var name = this.getAttribute("name");
        var rec_index = name.substring(3, 7);
        autofill_row(rec_index);
        set_mktime(rec_index);
        set_time_inputs_in_row_required(rec_index);
    });
    
    $('.break').change(function() {
        var name = this.getAttribute("name");
        var rec_index = name.substring(5, 9);
        autofill_row(rec_index);
        set_mktime(rec_index);
        set_time_inputs_in_row_required(rec_index);
    });
     
    //handling of Urlaub and Krank
    $('.defined_comment').change(function() {
        var name = this.getAttribute("name");
        var rec_index = name.substring(15, 19);
        if(this.value == '')  { //keine Auswahl
            enable_timetracking(rec_index);
            document.getElementsByName('sum' + rec_index)[0].readOnly = true;
            document.getElementsByName('sum' + rec_index)[0].value = '';
            document.getElementsByName('entry_mktime' + rec_index)[0].value = '';
            document.getElementsByName('sum' + rec_index)[0].removeAttribute("required");
        } else if(this.value == 'Urlaub') {
            disable_timetracking(rec_index);
            document.getElementsByName('sum' + rec_index)[0].readOnly = false;
            document.getElementsByName('sum' + rec_index)[0].value = '';
            document.getElementsByName('sum' + rec_index)[0].setAttribute("required", "");
        } else { //Feiertag und Krankheit
            document.getElementsByName('sum' + rec_index)[0].readOnly = true;
            disable_timetracking(rec_index);
            autocalc_sum(rec_index);
        }
        document.getElementById('entry' + rec_index).removeAttribute("class");
        document.getElementById('entry' + rec_index).classList.add(this.value);
        set_mktime(rec_index);
    });

</script>
        
<!-- remove         -->
<script>

    function validateForm() {
        alert("Ungespeicherte Änderungen!");
        return false;
//        var value = e.options[e.selectedIndex].value;
//        if (value == "NULL") {
//        alert("Promotionsfach muss angegeben werden!");
//        return false;
//        }
}
    

</script>

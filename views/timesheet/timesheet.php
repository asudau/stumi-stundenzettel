
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
            
            <?php $days_per_month = 31; ?>
            <?php $j = 0; ?>
         
            <?php for ($i = 1; $i <= $days_per_month; $i++) : ?>
                <?php if ($records[$j]['day'] == $i ) : ?>
                    <? $holiday = $records[$j]->isHoliday(); ?>
                    <? $weekend = $records[$j]->isWeekend(); ?>
                    <tr id ='entry[<?= $i ?>]' class='<?= ($weekend)? 'weekend' : ''?> <?= ($holiday)? 'holiday' : ''?>' >
                        <input type='hidden' name ='record_id[<?= $i ?>]' value='<?= $records[$j]['id'] ?>' >
                        <td>
                           <?= $i ?>
                        </td>
                        <td >
                            <input type='text' <?= ($weekend || $holiday)? 'readonly' : ''?> class='begin size-s studip-timepicker' id ='' name ='begin[<?= $i ?>]' value='<?= $records[$j]['begin'] ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input type='text' <?= ($weekend || $holiday)? 'readonly' : ''?> class='break' name ='break[<?= $i ?>]' value ='<?= $records[$j]['break'] ?>' placeholder="hh:mm">
                        </td>
                        <td>
                            <input type='text' <?= ($weekend || $holiday)? 'readonly' : ''?> class='end size-s studip-timepicker' id ='' name ='end[<?= $i ?>]' value='<?= $records[$j]['end'] ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                           <input type='text' readonly class='sum' name ='sum[<?= $i ?>]' value ='<?= $records[$j]['sum'] ?>' >
                        </td>
                        <td>
                           <input type='date' <?= ($weekend || $holiday)? 'readonly' : ''?> class='entry_mktime' name ='entry_mktime[<?= $i ?>]' value='<?= $records[$j]['entry_mktime'] ?>' >
                        </td>
                        <td>
                           <select <?= ($weekend || $holiday)? 'readonly' : ''?> name ='defined_comment[<?= $i ?>]'>
                                <option value=""> -- </option>
                                <?php foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option <?= ($records[$j]['defined_comment'] == $entry_value) ? 'selected' : ''?> value="<?=$entry_value?>"><?= $entry_value ?></option>
                                <?php endforeach ?>
                            </select>
                        </td>
                        <td>
                           <input type='text' <?= ($records[$j]->isWeekend())? 'readonly' : ''?> id ='' name ='comment[<?= $i ?>]' value ='<?= $records[$j]['comment'] ?>' >
                        </td>
                    </tr>
                    <?php $j++; ?>
                <?php else: ?>
                    <? $date = $i . '.' . $timesheet->month . '.'  . $timesheet->year; ?>
                    <? $weekend = StundenzettelRecord::isDateWeekend($date); ?>
                    <? $holiday = StundenzettelRecord::isDateHoliday($date); ?>
                    <tr id ='entry[<?= $i ?>]' class='<?= ($weekend)? 'weekend' : ''?> <?= ($holiday)? 'holiday' : ''?>' >
                        <input type='hidden' name ='record_id[<?= $i ?>]' value='' >
                        <td>
                           <?= $i ?>
                        </td>
                        <td >
                            <input type='text' <?= ($weekend || $holiday)? 'readonly' : ''?> class='begin' id ='' name ='begin[<?= $i ?>]' value='' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input type='text' <?= ($weekend || $holiday)? 'readonly' : ''?> class ='break' name ='break[<?= $i ?>]' value ='' placeholder="hh:mm">
                        </td>
                        <td>
                            <input type='text' <?= ($weekend || $holiday)? 'readonly' : ''?> class='end' name ='end[<?= $i ?>]' value='' placeholder="hh:mm" >
                        </td>
                        <td>
                           <input type='text' readonly class ='sum' name ='sum[<?= $i ?>]' value ='' >
                        </td>
                        <td>
                           <input type='date' <?= ($weekend || $holiday)? 'readonly' : ''?> class ='entry_mktime' name ='entry_mktime[<?= $i ?>]' value='' >
                        </td>
                        <td>
                           <select <?= ($weekend)? 'readonly' : ''?>  name ='defined_comment[<?= $i ?>]'>
                                <option selected value=""> -- </option>
                                <?php foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option value="<?=$entry_value?>"><?= $entry_value ?></option>
                                <?php endforeach ?>
                            </select>
                        </td>
                        <td>
                           <input type='text' <?= ($weekend || $holiday)? 'readonly' : ''?> class ='comment' name ='comment[<?= $i ?>]' value ='' >
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
        background-color: #6db370;
    }
</style>

<script>
    

    
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
        let today = new Date().toISOString().slice(0, 10);
        document.getElementsByName('entry_mktime' + row_index)[0].value = today;
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

</script>
        
        
<script>

    function validateForm() {
    var value = e.options[e.selectedIndex].value;
        if (value == "NULL") {
        alert("Promotionsfach muss angegeben werden!");
        return false;
    }
}

    

    inputs = document.getElementsByTagName('select');
    for (index = 0; index < inputs.length; ++index) {
        // deal with inputs[index] element.
        inputs[index].onchange = function () {
            if (this.value != ''){
                document.getElementsByName(this.getAttribute("name"))[0].classList.remove("needs_fill");
            }
        };
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

    //Abschluss(HZB) im Ausland: Staat Pflichtfeld
    //Abschluss(HZB) im Inland: Staat ausgegraut, Bundesland und Kreis Pflichtfeld
    //TODO, astatwerte aus tabelle kramen
    var hzb_art_ids = new Array("41", "52", "15", "56", "25", "58");
    document.getElementsByName("hzb_art")[1].onchange = function () {
        if (hzb_art_ids.indexOf(this.value) != -1) {
            document.getElementsByName("hzb_staat")[1].removeAttribute("disabled");
            document.getElementsByName("hzb_staat")[0].classList.add("needs_fill");
            document.getElementsByName("hzb_kreis")[1].setAttribute("disabled", true);
            document.getElementsByName("hzb_kreis")[0].classList.remove("needs_fill");
        } else {
            document.getElementsByName("hzb_staat")[1].setAttribute("disabled", true);
            document.getElementsByName("hzb_staat")[0].classList.remove("needs_fill");
            document.getElementsByName("hzb_kreis")[1].removeAttribute("disabled");
            document.getElementsByName("hzb_kreis")[0].classList.add("needs_fill");
        }
        if (this.value != ''){
            document.getElementsByName(this.getAttribute("name"))[0].classList.remove("needs_fill");
        }
    };

    //Abschlusshochschule Auslandshochschulen, dann Staat Pflichtfeld
    document.getElementsByName("hochschule_abschlusspruefung")[1].onchange = function () {
        if (this.value == '2'){
            document.getElementsByName("staat_abschlusspruefung")[1].removeAttribute("disabled");
        } else {
            document.getElementsByName("staat_abschlusspruefung")[1].setAttribute("disabled", true);
            document.getElementsByName("hzb_staat")[0].classList.remove("needs_fill");
        }
        if (this.value != ''){
            document.getElementsByName(this.getAttribute("name"))[0].classList.remove("needs_fill");
        }
    };

    


</script>
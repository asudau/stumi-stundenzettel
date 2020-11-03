
<?
use Studip\Button, Studip\LinkButton;
?>


    <h2>Name, Vorname der Hilfskraft: <?= User::findOneByUser_Id($stumi_id)->username ?></h2>
    <h2>Fachbereich/Organisationseinheit: <?= Institute::find($inst_id)->name ?></h2>
    <h2>Monat/Jahr: <?= $timesheet->month ?>/<?= $timesheet->year ?> </h2>

<form name="entry" method="post" onsubmit="return validateForm()" action="<?= $controller->url_for('index/save', $entry->id) ?>" <?= $dialog_attr ?> class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    
        <table class='sortable-table default'>
            <tr>
                <th style='width:10px'>Tag</th>
                <th style='width:200px'>Beginn</th>
                <th style='width:200px'>Pause</th>
                <th style='width:200px'>Ende</th>
                <th style='width:200px'>Dauer</th>
                <th style='width:200px'>Aufgezeichnet am</th>
                <th style='width:150px'>Bemerkung</th>
                <th style='width:200px'>sonstige Bemerkung</th>
                
            </tr>
            
            <?php $days_per_month = 31; ?>
            <?php $j = 0; ?>
                  
         
            <?php for ($i = 1; $i <= $days_per_month; $i++) : ?>
                <?php if ($records[$j]['day'] == $i ) : ?>
                    <tr name ='id' >
                        <td>
                           <?= $i ?>
                        </td>
                        <td >
                            <input type='text' class='size-s studip-timepicker' id ='' name ='' value='<?= $records[$j]['begin'] ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                            <input type='text' id ='' name ='?>' value ='<?= $records[$j]['break'] ?>' >
                        </td>
                        <td>
                            <input type='text' class='size-s studip-timepicker' id ='' name ='' value='<?= $records[$j]['end'] ?>' placeholder="hh:mm" >
                        </td>
                        <td>
                           <input type='text' id ='' name ='' value ='' >
                        </td>
                        <td>
                           <input type='date' id ='' name ='' value='' >
                        </td>
                        <td>
                           <select  name =''>
                                <option selected value=""> -- </option>
                                <?php foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                    <option value="<?=$entry_value?>"><?= $entry_value ?></option>
                                <?php endforeach ?>
                            </select>
                        </td>
                        <td>
                           <input type='text' id ='' name ='' value ='' >
                        </td>
                    </tr>
                    <?php $j++; ?>
                <?php else: ?>
                <tr name ='id' >
                    <td>
                       <?= $i ?>
                    </td>
                    <td >
                        <input type='text' class='size-s studip-timepicker' id ='' name ='' value='' placeholder="hh:mm" >
                    </td>
                    <td>
                        <input type='text' id ='' name ='?>' value ='' >
                    </td>
                    <td>
                        <input type='text' class='size-s studip-timepicker' id ='' name ='' value='' placeholder="hh:mm" >
                    </td>
                    <td>
                       <input type='text' id ='' name ='' value ='' >
                    </td>
                    <td>
                       <input type='date' id ='' name ='' value='' >
                    </td>
                    <td>
                       <select  name =''>
                            <option selected value=""> -- </option>
                            <?php foreach ($plugin->getCommentOptions() as $entry_value): ?>
                                <option value="<?=$entry_value?>"><?= $entry_value ?></option>
                            <?php endforeach ?>
                        </select>
                    </td>
                    <td>
                       <input type='text' id ='' name ='' value ='' >
                    </td>
                </tr>
                <?php endif ?>
            <?php endfor ?>
        </table>


    <footer data-dialog-button>
        <?= Button::create(_('Übernehmen')) ?>
    </footer>
</form>


<script>

    function validateForm() {
    var e = document.getElementsByName("promotionsfach")[1];
    var value = e.options[e.selectedIndex].value;
        if (value == "NULL") {
        alert("Promotionsfach muss angegeben werden!");
        return false;
    }
}

    var inputs, index;

    inputs = document.getElementsByTagName('select');
    for (index = 0; index < inputs.length; ++index) {
        // deal with inputs[index] element.
        inputs[index].onchange = function () {
            if (this.value != ''){
                document.getElementsByName(this.getAttribute("name"))[0].classList.remove("needs_fill");
            }
        };
    }

    inputs = document.getElementsByTagName('input');
    for (index = 0; index < inputs.length; ++index) {
        // deal with inputs[index] element.
        inputs[index].onkeyup = function () {
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

    $(function() {
            $('.mydate-picker').datepicker( {
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'MM yy',
            onClose: function(dateText, inst) {
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
            });
        });


</script>

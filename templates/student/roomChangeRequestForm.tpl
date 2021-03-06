<h1>Room Change Request</h1>


<h2>A few important notes first:</h2>
<ul>
    <li>Your request must be approved by the Residence Director (RD) for your hall, the Residence Director of hall you are moving to, and by the University Housing Assignments Office.</li>
    <li>If you change rooms without approval you will  be required to return to your assigned space, may be denied the opportunity to participate in any other room changes for the academic year, and will be assessed a $35 administrative charge.</li>
    <li><strong>Requests are granted based on available space and may be denied for any reason.</strong> We do not guarantee there any vacancies. Your RD will work with you to select a space from available vacancies.</li>
    <li><strong>The room fee may increase/decrease depending upon your selection of residence hall. Your student account will be billed accordingly.</strong></li>
    <li>It may take several bussiness days to process your request. Requests submitted on Fridays may not be processed until the following business day.</li>
    <li>You will be notified via your ASU email address when your request is approved or denied. <strong>Do not</strong> begin moving your belongings until you receive the final approval from University Housing.</li>
    <li>Once you receive your room change confirmation from the Housing Assignments Office, you must complete your move and be checked into your new assignment within 48 hours.</li>
</ul>

{START_FORM}
<h2>Contact Info</h2>
<p>Your RD and the Assignments Office will use this extra contact information (in addition to your ASU email address) to reach you in case there is a question regarding your request.</p>
<p>
{CELL_NUM_LABEL}: {CELL_NUM} {CELL_OPT_OUT} <i style="color: #696969;">(or check the box to opt out)</i>
</p>

<h2>Where to?</h2>

<h3>{TYPE_1}{TYPE_1_LABEL}</h3>
<div id="roomSwitch" style="margin-left:30px;">
    <h3>Hall Preferences</h3>
    <table>
        <tr>
            <td>{FIRST_CHOICE_LABEL}: </td>
            <td>{FIRST_CHOICE}</td>
        </tr>
        <tr>
            <td>{SECOND_CHOICE_LABEL}:</td>
            <td>{SECOND_CHOICE}</td>
        </tr>
    </table>
</div>

<h3>{TYPE_2}{TYPE_2_LABEL}</h3>
<div id="roomSwap" style="margin-left:30px;">
    <p>Enter the ASU user name (first part of email address) of the student you would like to switch rooms with. <strong>The person you want to switch will be sent an email asking them to sign in and confirm your request.</strong></p>
    <p>
    {SWAP_WITH_LABEL}:
    {SWAP_WITH}<strong>@appstate.edu</strong>
    </p>
</div>

<h2>Reason</h2>
<p>In the box below, please provide a <strong>short</strong> explanation of why you would like to move to a different room. A few sentences are sufficient. You should also indicate any special circumstances (i.e. you want to switch rooms with a friend on your floor).</p>
{REASON}

<p>{SUBMIT}</p>
{END_FORM}

<script type="text/javascript">
$(document).ready(function(){
    $("#room_change_request_cell_opt_out").change(function(){
        if($(this).is(":checked")){
            $("#room_change_request_cell_num").attr('disabled', true);
        } else {
            $("#room_change_request_cell_num").attr('disabled', false);
        }
     });

    $("#roomSwap").hide();
    $("#roomSwitch").hide();

    $("#room_change_request_type_switch").change(function(){
        if($(this).attr('checked', 'checked')){
            //$("#roomSwap").hide();
            //$("#roomSwitch").show();
            $("#roomSwap").slideUp();
            $("#roomSwitch").slideDown();
        }
    });

    $("#room_change_request_type_swap").change(function(){
        if($(this).attr('checked', 'checked')){
            //$("#roomSwitch").hide();
            //$("#roomSwap").show();
        	  $("#roomSwitch").slideUp();
            $("#roomSwap").slideDown();
        }
    });
});
</script>

<div class="hms">
  <div class="box">
    <div class="{TITLE_CLASS}"> <h1>{TITLE}</h1> </div>
    <div class="box-content">

        {MENU_LINK}<br/>

        <!-- BEGIN error_msg -->
        <span class="error">{ERROR_MSG}<br /></span>
        <!-- END error_msg -->
        
        <!-- BEGIN success_msg -->
        <span class="success">{SUCCESS_MSG}<br /></span>
        <!-- END success_msg -->
        <h2>Create Roommate Group - {TERM}</h2>
        {START_FORM}
        <table>
            <tr>
                <th>First roommate:</th><td align="left">{ROOMMATE_1}</td>
            </tr>
            <tr>
                <th>Second roommate</th><td align="left">{ROOMMATE_2}</td>
            </tr>
        </table>
        <br />
        {SUBMIT}
        {END_FORM}
    </div>
  </div>
</div>

<?php

class LotteryAdminEntryView extends hms\View {

    public function __construct()
    {

    }

    public function show()
    {
        $submitCmd = CommandFactory::getCommand('LotteryAdminCreateApp');

        $form = new PHPWS_Form('admin_entry');
        $submitCmd->initForm($form);

        $form->addText('asu_username');
        $form->setLabel('asu_username', 'ASU Username');
        $form->setClass('asu_username', 'form-control');
        $form->setExtra('asu_username', 'autofocus');

        $form->addCheck('physical_disability');
        $form->setLabel('physical_disability', 'Physical Disability');

        $form->addCheck('psych_disability');
        $form->setLabel('psych_disability', 'Psychological Disability');

        $form->addCheck('medical_need');
        $form->setLabel('medical_need', 'Medical Need');

        $form->addCheck('gender_need');
        $form->setLabel('gender_need', 'Gender Need');

//        $form->addSelect('special_interest', HMS_Lottery::get_special_interest_groups());
//        $form->setLabel('special_interest', 'Special Interest Group');

        $form->addSubmit('enter_into_lottery', 'Add to lottery');

        Layout::addPageTitle("Lottery Entry");

        return PHPWS_Template::process($form->getTemplate(), 'hms', 'admin/add_to_lottery.tpl');
    }
}

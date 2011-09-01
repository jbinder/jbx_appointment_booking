= Readme =

jbx_appointment_booking is a TYPO3 extension which allows basic appointment booking using Google Calendar.
User authentication is done using TYPO3's frontend users and Smarty is used as template language.

The user is guided through following steps:
* select type
* select day
* select time
* log in or register (optional if not already logged in)
* confirmation

The user receives an e-mail containing the appointment data and a link to cancel the appointment.
The appointment is added to a Google Calendar.
The admin is notified about new and cancelled appointments per e-mail.

== Requirements ==

* TYPO3 4.5
* smarty extension for TYPO3
* Google Calendar account

== Install and Configuration ==

* Install the Appointment Booking plugin.

* Define your time slots.

In the list view add at least one Appointment Booking - Season record and as much Appointment Booking - Slot Range
records as you need.

* Configure the extension.

Add the TypoScript configuration to the extension template where you installed the plugin.

Required parameters are:
gcal_username // Google Calendar username
gcal_password // Google Calendar password
userGroup // the group which newly registered user will be assigned to
adminEmail // the e-mail address which will receive notifications about new and cancelled appointments

Other important parameters are:
slotLength // appointment slot length in minutes
types // the appointment types which can be selected by the user
userPID // page id where newly registered users are stored

Following is the default TypoScript configuration:

plugin.tx_jbxappointmentbooking_pi1 {
    templateFileStep1 = EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_type.html
    templateFileStep2 = EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_month.html
    templateFileStep3 = EXT:jbx_appointment_booking/tpl/jbx_appointment_booking_slot.html
    templateFileStep4 = EXT:jbx_appointment_booking/tpl/jbx_appointment_user_login.html
    templateFileStep5 = EXT:jbx_appointment_booking/tpl/jbx_appointment_done.html
    templateFileStepCancel = EXT:jbx_appointment_booking/tpl/jbx_appointment_cancel.html
    templateEmailSubscribeUser = EXT:jbx_appointment_booking/tpl/jbx_appointment_email_subscribe_user.txt
    templateEmailSubscribeAdmin = EXT:jbx_appointment_booking/tpl/jbx_appointment_email_subscribe_admin.txt
    templateEmailCancelUser = EXT:jbx_appointment_booking/tpl/jbx_appointment_email_cancel_user.txt
    templateEmailCancelAdmin = EXT:jbx_appointment_booking/tpl/jbx_appointment_email_cancel_admin.txt
    calendarWeekdayNames = Sun,Mon,Tue,Wed,Thu,Fri,Sat,
    slotLength = 75
    gcal_username = 
    gcal_password = 
    eventTitle = [appointment] 
    userPID = 0
    userGroup = 1
    mailFromEmail = test@test.test
    mailFromName = test
    mailSubjectSubscribeUser = Your appointment
    mailSubjectSubscribeAdmin = New appointment
    mailSubjectCancelUser = Your appointment
    mailSubjectCancelAdmin = Appointment cancelled
    adminEmail = test@test.test
    types = type1, type2, type3
}

== Acknowledgement ==

This software contains:

 * Zend Gdata 1.11.10 (http://framework.zend.com/download/webservices), Zend Framework license
 * PHPMailer-Lite 5.1, (http://phpmailer.codeworxtech.com/), GNU Lesser GPL, version 2.1
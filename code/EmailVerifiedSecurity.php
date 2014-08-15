<?php

/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class EmailVerifiedSecurity extends Extension {
    
    private static $allowed_actions = array(
        'emailsent',
        'verifyemail',
        'VerifyEmailForm',
        'validate'
    );
    
    private static $default_verified_dest = false; // Default Destination redirect after verification Link clicked
    
    public static function set_default_verified_dest($dest){
        self::$default_verified_dest = $dest;
    }
    
    public static function get_default_verified_dest(){
        return self::$default_verified_dest;
    }
    
    /**
     * Show the "password sent" page, after a user has requested
     * to reset their password.
     *
     * @param SS_HTTPRequest $request The SS_HTTPRequest for this action. 
     * @return string Returns the "password sent" page as HTML code.
     */
    public function emailsent($request) {
        
        $email = Convert::raw2xml($request->param('ID') . '.' . $request->getExtension());

    	$tmpPage = new Page();
    	$tmpPage->Title = sprintf(_t('EmailVerifiedMember.EMAILSENTHEADER', "Verify Email link sent to '%s'"), $email);
    	$tmpPage->URLSegment = 'Security';
    	$tmpPage->ID = -1; // Set the page ID to -1 so we dont get the top level pages as its children
    	$controller = new Page_Controller($tmpPage);
    	$controller->init();
		
    	$customisedController = $controller->customise(array(
            'Content' =>
                "<p>" .
		sprintf(_t('EmailVerifiedMember.EMAILSENTTEXT', "Thank you! A verify email link has been sent to  '%s', provided an account exists for this email address."), $email) .
		"</p>",
            'Email' => $email
	));
	return $customisedController->renderWith(array('Security_emailsent', 'Security', $this->owner->stat('template_main'), 'BlankPage'));
    }
    
    /**
     * Show the "verify email" page
     *
     * @return string Returns the "verify email" page as HTML code.
     */
    public function verifyemail() {

	$tmpPage = new Page();
        $tmpPage->Title = _t('EmailVerifiedMember.VERIFYEMAILHEADER', 'Verify your email');
	$tmpPage->URLSegment = 'Security';
	$tmpPage->ID = -1; // Set the page ID to -1 so we dont get the top level pages as its children
	$controller = new Page_Controller($tmpPage);
	$controller->init();

	$customisedController = $controller->customise(array(
            'Content' =>
                '<p>' . _t('EmailVerifiedMember.VERIFYBEFORELOGON','You need to verify the link in the email we sent you before you can log on.') . '</p>' .
		'<p>' . _t('EmailVerifiedMember.USEFORMBELOW','Use the form below if you would like us to resend the link.') . '</p>',
            'Form' => $this->owner->VerifyEmailForm(),
	));
        
        //Controller::$currentController = $controller;
	return $customisedController->renderWith(array('Security_verifyemail', 'Security', $this->owner->stat('template_main'), 'BlankPage'));
    }
    
    /**
     * Factory method for the lost verify email form
     *
     * @return Form Returns the lost verify email form
     */
    public function VerifyEmailForm() {
        return new EmailVerifiedMemberLoginForm(
            $this->owner,
            'VerifyEmailForm',
            new FieldList(
                new EmailField('Email', _t('Member.EMAIL', 'Email'))
            ),
            new FieldList(
                new FormAction(
                    'resendVerificationEmail',
                    _t('EmailVerifiedMember.BUTTONSEND', 'Send me the verify email link again')
		)
            ),
            false
	);
    }
    
    /**
     * Validate the link clicked in email
     *
     * @param SS_HTTPRequest $request The SS_HTTPRequest for this action.
     * @return string Returns the "validated" page as HTML code.
     */
    public function validate($request) {
        
        $tmpPage = new Page();
	$tmpPage->Title = _t('EmailVerifiedMember.VERIFYEMAILHEADER', 'Verification link');
	$tmpPage->URLSegment = 'Security';
	$tmpPage->ID = -1; // Set the page ID to -1 so we dont get the top level pages as its children
	$controller = new Page_Controller($tmpPage);
	$controller->init();
        
        if($request && $member = DataObject::get_one('Member', "\"Email\" = '".Convert::raw2sql($request->param('ID'))."'")){
            if ($member->VerificationString == Convert::raw2sql($request->param('OtherID'))){
                
                $member->Verified = true;
                $member->write();
                
                if(EmailVerifiedMember::get_login_after_validation()) $member->logIn();

                // Redirect to custom destination after verification succeded
                if(EmailVerifiedSecurity::get_default_verified_dest()) return $this->owner->redirect(EmailVerifiedSecurity::get_default_verified_dest());
                $tmpPage->Title.= " - "._t('EmailVerifiedMember.ACCOUNTVERIFIEDTITLE', "Member account verified");
                $customisedController = $controller->customise(array(
                    'Content' =>
                        "<p>" .
                        sprintf(_t('EmailVerifiedMember.ACCOUNTVERIFIED', "Thank you %s! Your account has been verified, you can now login to the website."), $member->Name) .
                        "</p>"
                ));
                return $customisedController->renderWith(array('Security_validationsuccess', 'Security', $this->owner->stat('template_main'), 'BlankPage'));
            }
        }
        
        // Verification failed
        $tmpPage->Title.= " - "._t('EmailVerifiedMember.ACCOUNTVERIFIEDFAILTITLE', "Member email address verification failed");
        $customisedController = $controller->customise(array(
            'Content' =>
                "<p>" .
                _t('EmailVerifiedMember.ACCOUNTVERIFIEDFAIL', "Member email address verification failed, either unknown email address or invalid verification string. Please ensure you copy and pasted the entire link.") .
                "</p>"
        ));
        return $customisedController->renderWith(array('Security_validationfail', 'Security', $this->owner->stat('template_main'), 'BlankPage'));
    }
}